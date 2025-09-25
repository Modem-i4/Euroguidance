<?php
// ===== Disable comments admin-wide for THIS THEME only =====

// 1) Прибираємо підтримку коментарів/треκбеків у всіх типів записів
add_action('init', function () {
	foreach (get_post_types() as $pt) {
		if (post_type_supports($pt, 'comments')) {
			remove_post_type_support($pt, 'comments');
		}
		if (post_type_supports($pt, 'trackbacks')) {
			remove_post_type_support($pt, 'trackbacks');
		}
	}
}, 100);

// 2) Закриваємо коментарі і пінги на рівні фільтрів
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('get_comments_number', function ($count) { return 0; }, 20, 2);

// 3) Прибираємо пункт "Коментарі" в меню адмінки
add_action('admin_menu', function () {
	remove_menu_page('edit-comments.php');
    remove_submenu_page( 'tools.php', 'tools.php' );
    remove_submenu_page( 'tools.php', 'import.php' ); 
    remove_submenu_page( 'tools.php', 'site-health.php' );
}, 999);

// 4) Ховаємо іконку коментарів у верхньому адмін-барі
add_action('admin_bar_menu', function ($wp_admin_bar) {
	$wp_admin_bar->remove_node('comments');
}, 999);

// 5) Редірект зі сторінки списку коментарів, якщо зайти напряму
add_action('load-edit-comments.php', function () {
	wp_safe_redirect(admin_url());
	exit;
});

// 6) Прибираємо віджет "Останні коментарі" з Дашборду
add_action('wp_dashboard_setup', function () {
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}, 99);

// 7) Прибираємо метабокси коментарів у редакторі
add_action('admin_init', function () {
	foreach (get_post_types() as $pt) {
		remove_meta_box('commentstatusdiv', $pt, 'normal'); // Статус коментарів
		remove_meta_box('commentsdiv',      $pt, 'normal'); // Список коментарів
		remove_meta_box('trackbacksdiv',    $pt, 'normal'); // Трекбеки
	}
});

// 8) (Необов'язково) Прибрати "Обговорення" з "Налаштування"
add_action('admin_menu', function () {
	remove_submenu_page('options-general.php', 'options-discussion.php');
}, 999);

// 9) На фронті — не вантажити скрипт відповіді на коментар
add_action('wp_enqueue_scripts', function () {
	wp_deregister_script('comment-reply');
}, 20);



// Admin: сховати "Майстерня" та "Медіа"
add_action('admin_menu', function () {
    remove_menu_page('index.php');   // Майстерня / Dashboard
    remove_menu_page('upload.php');  // Медіа / Media
    // optionally: сховати "Оновлення" всередині Майстерні
    remove_submenu_page('index.php', 'update-core.php');

    // Сховати підменю "Категорії" та "Позначки" у "Записах"
    remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category');
    remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');
}, 999);


// Видаляємо "Переглянути" з дій у списку користувачів
add_filter( 'user_row_actions', function( $actions, $user ) {
    if ( isset( $actions['view'] ) ) {
        unset( $actions['view'] );
    }
    return $actions;
}, 10, 2 );

// Ховаємо кнопку "Згорнути меню" в адмінці
add_action('admin_head', function() {
    echo '<style>
        #collapse-menu { display: none !important; }
    </style>';
});

add_post_type_support( 'page', 'excerpt' );