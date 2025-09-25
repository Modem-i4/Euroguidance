<?php
/**
 * Plugin Name: WP Contact API (Table + REST)
 * Description: Мінімальний контактний API: своя БД-таблиця, REST submit, список у адмінці, пошта, експорт CSV, сповіщення e-mail.
 * Version:     0.3.0
 * Author:      Everydev (Modemi4)
 */

if (!defined('ABSPATH')) exit;

class WP_Contact_API {
    const VERSION       = '0.3.0';
    const OPTION_NOTIFY = 'wp_contact_notify_emails';

    public function __construct() {
        register_activation_hook(__FILE__, [$this, 'activate']);

        add_action('admin_menu',    [$this, 'admin_menu']);
        add_action('rest_api_init', [$this, 'register_routes']);

        // ВАЖЛИВО: Ajax-експорт реєструємо тут, а не в rest_api_init
        add_action('wp_ajax_wp_contact_export_csv', [$this, 'export_csv']);

        // Стилі/скрипти для адмін-сторінки списку
        add_action('admin_enqueue_scripts', function($hook){
            if ($hook === 'toplevel_page_wp-contact-entries') {
                wp_enqueue_style('wp-components');
                wp_add_inline_style('wp-components', $this->admin_inline_css());
                wp_add_inline_script('jquery-core', $this->admin_inline_js());
            }
        });
    }

    private function table() {
        global $wpdb;
        return $wpdb->prefix . 'contact_entries';
    }

    public function activate() {
        global $wpdb;
        $table = $this->table();
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Актуальна схема БД
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            name VARCHAR(191) NOT NULL,
            email VARCHAR(191) NOT NULL,
            message LONGTEXT NULL,
            ip VARCHAR(45) DEFAULT NULL,
            user_agent TEXT NULL,
            referer TEXT NULL,
            meta JSON NULL,
            answered TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY created_at (created_at),
            KEY email (email),
            KEY answered (answered)
        ) $charset_collate;";
        dbDelta($sql);

        // Прибрати subject, якщо лишився
        $has_subject = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND COLUMN_NAME='subject'",
                DB_NAME, $table
            )
        );
        if ($has_subject) {
            $wpdb->query("ALTER TABLE {$table} DROP COLUMN subject");
        }

        // Додати answered, якщо відсутній
        $has_answered = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s AND COLUMN_NAME='answered'",
                DB_NAME, $table
            )
        );
        if (!$has_answered) {
            $wpdb->query("ALTER TABLE {$table} ADD COLUMN answered TINYINT(1) NOT NULL DEFAULT 0, ADD KEY answered (answered)");
        }
    }

    public function admin_menu() {
        add_menu_page(
            'Запити зв\'язку',
            'Запити зв\'язку',
            'manage_options',
            'wp-contact-entries',
            [$this, 'render_admin_page'],
            'dashicons-email-alt2',
            26
        );
    }

    private function admin_inline_css() {
        return <<<CSS
/* Статусні бейджі */
.wp-contact-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:2px 8px; border-radius:999px; font-size:12px; font-weight:600;
}
.wp-contact-badge--open  { background:#fff6e5; color:#8a5300; border:1px solid #ffd99e; }
.wp-contact-badge--done  { background:#e9fff0; color:#106f3a; border:1px solid #a6f0c0; }

/* Дії */
.wp-contact-actions { display:flex; gap:8px; align-items:center; }

/* Видалення — більш виразна кнопка */
.button-delete {
    background:#d63638; color:#fff; border-color:#b32d2e;
}
.button-delete:hover { background:#b32d2e; color:#fff; }

/* Кнопка "Показати повідомлення" */
.button-toggle-msg { white-space:nowrap; }

/* Рядок повідомлення (акордеон під записом) */
.wp-contact-msg-row { display:none; }
.wp-contact-msg-row.is-open { display:table-row; }
.wp-contact-msg-cell {
    background:#f6f7f7; padding:16px 20px; border-left:4px solid #2271b1;
}
.wp-contact-msg {
    white-space:pre-wrap; max-width:1200px; min-height:180px; line-height:1.5;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

/* Таблиця */
.wp-contact-table td, .wp-contact-table th { vertical-align:top; }
.dashicons, .dashicons-before:before { vertical-align:sub; }

/* Парами по два рядки: 2 білі, 2 сірі */
.wp-contact-table tr:nth-child(4n+1),
.wp-contact-table tr:nth-child(4n+2) { background-color:#fff; }
.wp-contact-table tr:nth-child(4n+3),
.wp-contact-table tr:nth-child(4n+4) { background-color:#f6f7f7; }

/* Кнопка експорту */
#wp-contact-export-btn { display:inline-flex; align-items:center; gap:6px; }

/* Панель сповіщень */
#wp-contact-notify-panel { display:none; padding:16px; margin:10px 0; background:#fff; border:1px solid #ccd0d4; border-radius:6px; }
#wp-contact-notify-panel.is-open { display:block; }
.wp-contact-emails { display:flex; flex-direction:column; gap:8px; max-width:560px; }
.wp-contact-email-row { display:flex; gap:8px; }
.wp-contact-email-row input[type="email"] { flex:1; }
CSS;
    }

    private function admin_inline_js() {
        return <<<JS
jQuery(function($){
    // Експорт CSV у невидимий iframe (не замінює поточну сторінку)
    $(document).on('click', '#wp-contact-export-btn', function(e){
        e.preventDefault();
        var url = $(this).data('exportUrl');
        if (!url) return;
        $('#wp-contact-export-iframe').remove();
        var \$iframe = $('<iframe>', {
            id: 'wp-contact-export-iframe',
            src: url,
            style: 'display:none;width:0;height:0;border:0;'
        });
        $('body').append(\$iframe);
    });

    // Тогл акордеону повідомлення
    $(document).on('click', '.button-toggle-msg', function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var row = $('#wp-contact-msg-row-' + id);
        row.toggleClass('is-open');
        if (row.hasClass('is-open')) {
            $('html,body').animate({ scrollTop: row.offset().top - 120 }, 200);
        }
    });

    // Тогл панелі сповіщень
    $(document).on('click', '#wp-contact-notify-toggle', function(e){
      e.preventDefault();
      $('#wp-contact-notify-panel').toggleClass('is-open');
    });

    // Додати email-рядок
    $(document).on('click', '#wp-contact-email-add', function(e){
      e.preventDefault();
      var \$list = $('.wp-contact-emails');
      var \$row = $('<div class="wp-contact-email-row">\
          <input type="email" name="notify_emails[]" placeholder="name@example.com" class="regular-text" />\
          <button class="button wp-contact-email-remove">✖ Прибрати</button>\
        </div>');
      \$list.append(\$row);
    });

    // Прибрати рядок
    $(document).on('click', '.wp-contact-email-remove', function(e){
      e.preventDefault();
      $(this).closest('.wp-contact-email-row').remove();
    });
});
JS;
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) return;

        global $wpdb;
        $table = $this->table();

        // Збереження списку емейлів сповіщень
        if ( isset($_POST['save_notify_emails']) && check_admin_referer('wp_contact_save_notify') ) {
            $emails_raw = isset($_POST['notify_emails']) ? (array) $_POST['notify_emails'] : [];
            $emails = [];
            foreach ($emails_raw as $em) {
                $em = sanitize_email(trim((string)$em));
                if ($em && is_email($em)) {
                    $emails[] = $em;
                }
            }
            update_option(self::OPTION_NOTIFY, array_values(array_unique($emails)));
            echo '<div class="updated"><p>Налаштування сповіщень збережено</p></div>';
        }

        // Видалення запису
        if (isset($_POST['delete'], $_POST['id']) && check_admin_referer('wp_contact_delete')) {
            $id = (int) $_POST['id'];
            $wpdb->delete($table, ['id' => $id], ['%d']);
            echo '<div class="updated"><p>Запис видалено</p></div>';
        }

        // Перемикання статусу answered
        if (isset($_POST['toggle_answer'], $_POST['id']) && check_admin_referer('wp_contact_toggle_answer')) {
            $id = (int) $_POST['id'];
            $current = (int) $_POST['current_answered'];
            $new = $current ? 0 : 1;
            $wpdb->update($table, ['answered' => $new], ['id' => $id], ['%d'], ['%d']);
            echo '<div class="updated"><p>Статус оновлено</p></div>';
        }

        // Пагінація
        $per_page = 20;
        $page = max(1, (int)($_GET['paged'] ?? 1));
        $offset = ($page - 1) * $per_page;

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $entries = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} ORDER BY answered ASC, created_at DESC, id DESC LIMIT %d OFFSET %d",
                $per_page, $offset
            ),
            ARRAY_A
        );
        $pages = max(1, (int)ceil($total / $per_page));

        echo '<div class="wrap"><h1>Запити зв\'язку</h1>';

        // Експорт у CSV + Налаштування сповіщень
        $notify_emails = get_option(self::OPTION_NOTIFY, []);
        $export_url = add_query_arg(
            [
                'action'   => 'wp_contact_export_csv',
                '_wpnonce' => wp_create_nonce('wp_contact_export_csv'),
            ],
            admin_url('admin-ajax.php')
        );

        echo '<p style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">';
        echo '<a href="#" id="wp-contact-export-btn" class="button button-primary" data-export-url="' . esc_url($export_url) . '"><span class="dashicons dashicons-download"></span> Експорт CSV</a>';
        echo '<a href="#" id="wp-contact-notify-toggle" class="button"><span class="dashicons dashicons-email"></span> Налаштування сповіщень</a>';
        echo '</p>';

        // Панель налаштувань e-mail сповіщень
        echo '<div id="wp-contact-notify-panel">';
        echo '<form method="post">';
        wp_nonce_field('wp_contact_save_notify');

        echo '<div class="wp-contact-emails">';

        if (empty($notify_emails)) {
            echo '<div class="wp-contact-email-row">
                    <input type="email" name="notify_emails[]" placeholder="name@example.com" class="regular-text" />
                    <button class="button wp-contact-email-remove">✖ Прибрати</button>
                  </div>';
        } else {
            foreach ($notify_emails as $em) {
                echo '<div class="wp-contact-email-row">
                        <input type="email" name="notify_emails[]" value="'.esc_attr($em).'" class="regular-text" />
                        <button class="button wp-contact-email-remove">✖ Прибрати</button>
                      </div>';
            }
        }

        echo '</div>'; // .wp-contact-emails

        echo '<p style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">';
        echo '<button id="wp-contact-email-add" class="button">Додати емейл</button>';
        echo '<button type="submit" name="save_notify_emails" value="1" class="button button-primary">Зберегти</button>';
        echo '</p>';

        echo '</form>';
        echo '</div>'; // #wp-contact-notify-panel

        // Таблиця
        echo '<table class="widefat striped wp-contact-table"><thead><tr>
            <th style="width:70px">ID</th>
            <th style="width:170px">Дата</th>
            <th style="width:220px">Від</th>
            <th>Email</th>
            <th style="width:140px">Статус</th>
            <th style="width:240px">Дії</th>
        </tr></thead><tbody>';

        if (!$entries) {
            echo '<tr><td colspan="6">Записів не знайдено</td></tr>';
        } else {
            foreach ($entries as $e) {
                $id   = (int)$e['id'];
                $ans  = (int)$e['answered'] === 1;
                $name = esc_html($e['name']);
                $email_link = '<a href="mailto:'.esc_attr($e['email']).'">'.esc_html($e['email']).'</a>';

                echo '<tr>';
                echo '<td>'.$id.'</td>';
                echo '<td>'.esc_html( get_date_from_gmt( $e['created_at'], 'Y-m-d H:i' ) ).'</td>';
                echo '<td>'. $name .'</td>';
                echo '<td>'. $email_link .'</td>';

                $badge = $ans
                    ? '<span class="wp-contact-badge wp-contact-badge--done"><span class="dashicons dashicons-yes"></span>Опрацьовано</span>'
                    : '<span class="wp-contact-badge wp-contact-badge--open"><span class="dashicons dashicons-clock"></span>Очікує відповіді</span>';
                echo '<td>'.$badge.'</td>';

                echo '<td class="wp-contact-actions">';

                echo '<a href="#" class="button button-secondary button-toggle-msg" data-id="'.$id.'"><span class="dashicons dashicons-editor-expand"></span> Відкрити</a>';

                echo '<form method="post" style="display:inline">'.
                        wp_nonce_field('wp_contact_toggle_answer', '_wpnonce', true, false).
                        '<input type="hidden" name="id" value="'.$id.'">'.
                        '<input type="hidden" name="current_answered" value="'.($ans ? 1 : 0).'">'.
                        '<button class="button" name="toggle_answer" value="1">'.
                            ($ans ? '<span class="dashicons dashicons-undo"></span> Зняти позначку' : '<span class="dashicons dashicons-saved"></span> Готово').
                        '</button>'.
                     '</form>';

                echo '<form method="post" style="display:inline">'.
                        wp_nonce_field('wp_contact_delete', '_wpnonce', true, false).
                        '<input type="hidden" name="id" value="'.$id.'">'.
                        '<button class="button button-delete" name="delete" value="1" onclick="return confirm(\'Видалити запис #'.$id.'?\')">'.
                            '<span class="dashicons dashicons-trash"></span> Видалити'.
                        '</button>'.
                     '</form>';

                echo '</td>';
                echo '</tr>';

                $msg_display = esc_html((string)$e['message']);
                echo '<tr id="wp-contact-msg-row-'.$id.'" class="wp-contact-msg-row"><td colspan="6" class="wp-contact-msg-cell">';
                echo '<div class="wp-contact-msg">'.$msg_display.'</div>';
                echo '</td></tr>';
            }
        }

        echo '</tbody></table>';

        // Пагінація
        echo '<div class="tablenav"><div class="tablenav-pages">';
        for ($i = 1; $i <= $pages; $i++) {
            $url = add_query_arg(['paged' => $i]);
            $class = $i === $page ? ' class="button button-primary"' : ' class="button"';
            echo '<a'.$class.' href="'.esc_url($url).'">'.$i.'</a> ';
        }
        echo '</div></div>';

        echo '</div>'; // .wrap
    }

    public function register_routes() {
        // POST /wp-json/contact/v1/submit
        register_rest_route('contact/v1', '/submit', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'args'                => [
                'name'    => ['required' => true],
                'email'   => ['required' => true],
                'message' => ['required' => true],
                'meta'    => ['required' => false],
            ],
            'callback'            => [$this, 'handle_submit'],
        ]);

        // GET /wp-json/contact/v1/entries (адміни)
        register_rest_route('contact/v1', '/entries', [
            'methods'             => 'GET',
            'permission_callback' => function(){ return current_user_can('manage_options'); },
            'callback'            => [$this, 'handle_list'],
        ]);
    }

    public function handle_submit(WP_REST_Request $req) {
        $name    = sanitize_text_field($req->get_param('name'));
        $email   = sanitize_email($req->get_param('email'));
        $message = wp_kses_post($req->get_param('message'));
        $meta    = $req->get_param('meta');

        if (!$name || !is_email($email) || !$message) {
            return new WP_Error('bad_request', 'Invalid payload', ['status' => 400]);
        }

        $ip  = $_SERVER['REMOTE_ADDR']     ?? '';
        $ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ref = $_SERVER['HTTP_REFERER']    ?? '';

        global $wpdb;
        $table = $this->table();

        $inserted = $wpdb->insert($table, [
            'name'       => $name,
            'email'      => $email,
            'message'    => $message,
            'ip'         => $ip,
            'user_agent' => $ua,
            'referer'    => $ref,
            'meta'       => $meta ? wp_json_encode($meta) : null,
            'answered'   => 0,
        ], ['%s','%s','%s','%s','%s','%s','%s','%d']);

        if (!$inserted) {
            return new WP_Error('save_failed', 'Could not save entry', ['status' => 500]);
        }

        $entry_id = (int) $wpdb->insert_id;

        // Сповіщення
        $recipients = get_option(self::OPTION_NOTIFY, []);
        if (empty($recipients)) {
            $recipients = [ get_option('admin_email') ];
        }
        $headers = ['Reply-To: '. $name .' <'. $email .'>'];
        $auto_subject = sprintf('Контактна форма: %s', $name);
        wp_mail($recipients, $auto_subject, "From: {$name} <{$email}>\n\n{$message}", $headers);

        return [
            'ok'      => true,
            'entryId' => $entry_id,
            'message' => 'Saved and mailed'
        ];
    }

    public function handle_list(WP_REST_Request $req) {
        global $wpdb;
        $table = $this->table();
        $per_page = max(1, (int)($req->get_param('per_page') ?: 20));
        $page     = max(1, (int)($req->get_param('page') ?: 1));
        $offset   = ($page - 1) * $per_page;

        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, created_at, name, email, message, user_agent, referer, meta, answered
                 FROM {$table}
                 ORDER BY answered ASC, created_at DESC, id DESC
                 LIMIT %d OFFSET %d",
                $per_page, $offset
            ),
            ARRAY_A
        );

        return [
            'total'    => $total,
            'per_page' => $per_page,
            'page'     => $page,
            'items'    => $rows
        ];
    }

    public function export_csv() {
        // Права
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden', 403);
        }

        // Нонс (зчитуємо саме _wpnonce)
        check_ajax_referer('wp_contact_export_csv', '_wpnonce');

        global $wpdb;
        $table = $this->table();

        // Без user_agent у експорті
        $rows = $wpdb->get_results(
            "SELECT id, created_at, name, email, message, referer, meta, answered
             FROM {$table}
             ORDER BY answered ASC, created_at DESC, id DESC",
            ARRAY_A
        );

        // Заголовки
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=contact_entries-' . date('Y-m-d-His') . '.csv');

        $out = fopen('php://output', 'w');

        // BOM для Excel
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Заголовок колонок (роздільник ; )
        fputcsv($out, ['id','created_at','name','email','message','referer','meta','answered'], ';');

        if ($rows) {
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['id'] ?? '',
                    $r['created_at'] ?? '',
                    $r['name'] ?? '',
                    $r['email'] ?? '',
                    $r['message'] ?? '',
                    $r['referer'] ?? '',
                    $r['meta'] ?? '',
                    (int)($r['answered'] ?? 0),
                ], ';');
            }
        }

        fclose($out);
        exit;
    }
}

new WP_Contact_API();
