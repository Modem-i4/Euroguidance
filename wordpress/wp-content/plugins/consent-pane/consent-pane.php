<?php
/**
 * Plugin Name:  Панель згоди на Cookies
 * Description:  Простий банер згоди з cookies (дві кнопки: «Усі» / «Лише необхідні») + Google Consent Mode v2. Банер на всіх фронтенд-сторінках. Шорткод: [cookie_settings].
 * Version:      1.1.3
 * Author:       Everydev (Modemi4)
 * License:      MIT
 * Text Domain:  everydev-cmp
 */

if (!defined('ABSPATH')) exit;

final class Everydev_CMP {
    const VERSION     = '1.1.3';
    const COOKIE_NAME = 'cmp_choice_v2';

    public static function boot() {
        add_action('wp_head',   [__CLASS__, 'print_consent_default'], 0);
        add_action('wp_footer', [__CLASS__, 'print_banner'], 100);
        add_shortcode('cookie_settings', [__CLASS__, 'shortcode_settings']);
    }

    public static function print_consent_default() {
        if (is_admin()) return;
        echo "<script>
window.dataLayer = window.dataLayer || [];
function gtag(){ dataLayer.push(arguments); }
gtag('consent','default',{
  analytics_storage:  'denied',
  ad_storage:         'denied',
  ad_user_data:       'denied',
  ad_personalization: 'denied',
  wait_for_update:    500
});
</script>\n";
    }

    public static function print_banner() {
        if (is_admin()) return;

        $cookie   = apply_filters('everydev_cmp_cookie_name', self::COOKIE_NAME);
        $days     = (int) apply_filters('everydev_cmp_cookie_days', 180);
        $privacy  = esc_url( apply_filters('everydev_cmp_privacy_url', '/privacy') );

        $t = wp_parse_args((array) apply_filters('everydev_cmp_texts', []), [
            'more'       => __('політику використання cookies', 'everydev-cmp'),
            'accept'     => __('Прийняти всі', 'everydev-cmp'),
            'essential'  => __('Прийняти лише необхідні', 'everydev-cmp'),
            'aria_label' => __('Налаштування cookies', 'everydev-cmp'),
        ]);

        echo '<style id="everydev-cmp-css">
:root{
  --edcmp-grad: linear-gradient(90deg,#C65094 0%,#4884C3 49.52%,#6C3088 100%);
  --edcmp-bg: #ECEEF2;
  --edcmp-tx: #3A3D46;
  --edcmp-link: #3A3D46;
}
#edcmpBanner{pointer-events:none}
.edcmp-banner{
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  bottom: 12px;
  z-index: 198;
  width: min(1040px, calc(100% - 28px));
  margin: 0 auto;
  background-color: rgba(255, 255, 255, .8);
  backdrop-filter: blur(8px);
  border: 2px solid #2f8540;
  will-change: transform, opacity;
  opacity:0; transform:translate(-50%, 8px);
  transition:opacity .35s ease, transform .35s ease;
  pointer-events:auto;
  display:none;
}
.edcmp-banner.is-visible{opacity:1; transform:translate(-50%, 0)}
.edcmp-banner.is-hiding{opacity:0; transform:translate(-50%, 8px)}
.edcmp-wrap{
  display:flex; align-items:center; gap:18px;
  padding:12px 16px;
}
.edcmp-icon{flex:0 0 auto; display:flex; align-items:center; justify-content:center}
.edcmp-text{flex:1 1 auto; color:var(--edcmp-tx); font-size:14px; line-height:1.55}
.edcmp-text a{color:var(--edcmp-link); text-decoration:underline}
.edcmp-text a:hover{opacity:.9}
.edcmp-actions{display:flex; gap:12px; flex:0 0 auto}
.edcmp-btn{
  appearance:none; border:0; cursor:pointer; font-weight:600;
  padding:10px 22px; border-radius:0; font-size:14px;
  transition:transform .12s ease, filter .12s ease, box-shadow .12s ease, background-color .12s ease;
  color:#fff; background:#82B58C;
  box-shadow: 0 3px 12px rgba(0,0,0,.18);
}
.edcmp-btn:hover{background-color:#2f8540;}
.edcmp-btn:active{transform:translateY(1px)}
.edcmp-btn:focus-visible{outline:2px solid #fff; outline-offset:2px}
@media (max-width: 720px){
  .edcmp-wrap{flex-wrap:wrap; row-gap:10px}
  .edcmp-actions{width:100%; justify-content:flex-end}
}
</style>';

        echo '<div id="edcmpBanner" role="dialog" aria-live="polite" aria-label="'.esc_attr($t['aria_label']).'">
  <div class="edcmp-banner" id="edcmpPanel" aria-hidden="true">
    <div class="edcmp-wrap">
      <div class="edcmp-icon" aria-hidden="true">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M16 21.3333V16M16 10.6666H16.0133M29.3333 16C29.3333 23.3638 23.3638 29.3333 16 29.3333C8.63619 29.3333 2.66666 23.3638 2.66666 16C2.66666 8.63616 8.63619 2.66663 16 2.66663C23.3638 2.66663 29.3333 8.63616 29.3333 16Z" stroke="url(#paint0_linear_730_339)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/> <defs> <linearGradient id="paint0_linear_730_339" x1="2.66666" y1="15.4017" x2="29.3333" y2="15.4017" gradientUnits="userSpaceOnUse"> <stop stop-color="#7ABF92"/> <stop offset="1" stop-color="#4A78A1"/> </linearGradient> </defs> </svg>
      </div>
      <div class="edcmp-text">'.
        esc_html__('Ми використовуємо cookies для аналітики відвідуваності сайту. Перегляньте ', 'everydev-cmp') .
        '<a href="'.$privacy.'" target="_blank" rel="noopener">'.esc_html($t['more']).'</a>' .
        esc_html__(', щоб дізнатися більше.', 'everydev-cmp')
      .'</div>
      <div class="edcmp-actions">
        <button id="edcmpAccept"    class="edcmp-btn">'.esc_html($t['accept']).'</button>
        <button id="edcmpEssential" class="edcmp-btn">'.esc_html($t['essential']).'</button>
      </div>
    </div>
  </div>
</div>';

        $cookie_js = esc_js( apply_filters('everydev_cmp_cookie_name', self::COOKIE_NAME) );
        $days_js   = (int)  apply_filters('everydev_cmp_cookie_days', 180);

        echo "<script>
(function(){
  var COOKIE = '{$cookie_js}';
  var DAYS   = {$days_js};

  function getCookie(n){
    return document.cookie.split('; ').reduce(function(r,v){
      var p=v.split('='); return p[0]===n ? decodeURIComponent(p[1]) : r;
    },'');
  }
  function setCookie(n,val,days){
    var d=new Date(); d.setTime(d.getTime()+days*24*60*60*1000);
    document.cookie = n+'='+encodeURIComponent(val)+'; path=/; expires='+d.toUTCString()+'; SameSite=Lax';
  }
  function updateConsent(all){
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    gtag('consent','update',{
      analytics_storage:  all ? 'granted' : 'denied',
      ad_storage:         all ? 'granted' : 'denied',
      ad_user_data:       all ? 'granted' : 'denied',
      ad_personalization: all ? 'granted' : 'denied'
    });
  }

  var panel  = document.getElementById('edcmpPanel');
  var btnAll = document.getElementById('edcmpAccept');
  var btnEs  = document.getElementById('edcmpEssential');

  function show(){
    if(!panel) return;
    panel.style.display = 'block';
    panel.setAttribute('aria-hidden','false');
    void panel.offsetWidth;
    panel.classList.add('is-visible');
  }
  function hide(){
    if(!panel) return;
    panel.classList.remove('is-visible');
    panel.classList.add('is-hiding');
    panel.addEventListener('transitionend', function onEnd(e){
      if (e.propertyName !== 'opacity') return;
      panel.removeEventListener('transitionend', onEnd);
      panel.classList.remove('is-hiding');
      panel.style.display = 'none';
      panel.setAttribute('aria-hidden','true');
    });
  }

  var saved = getCookie(COOKIE);
  if (!saved) show();
  if (saved==='all')       updateConsent(true);
  if (saved==='essential') updateConsent(false);

  if (btnAll) btnAll.addEventListener('click', function(){
    setCookie(COOKIE, 'all', DAYS); updateConsent(true);  hide();
  });
  if (btnEs) btnEs.addEventListener('click', function(){
    setCookie(COOKIE, 'essential', DAYS); updateConsent(false); hide();
  });

  window.CMP_showBanner = function(){ show(); };

  function delCookie(n){
    document.cookie = n + '=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
  }

  window.CMP_resetConsent = function(){
    delCookie(COOKIE);
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    gtag('consent','update',{
      analytics_storage:  'denied',
      ad_storage:         'denied',
      ad_user_data:       'denied',
      ad_personalization: 'denied'
    });
    CMP_showBanner();
  };

  document.addEventListener('click', function(e){
    var a = e.target.closest('.privacyLinks a[href=\"#\"]');
    if(!a) return;
    e.preventDefault();
    if (window.CMP_resetConsent) window.CMP_resetConsent();
  });
})();
</script>\n";
    }

    public static function shortcode_settings($atts) {
        $text = isset($atts['text']) ? $atts['text'] : __('Cookie settings', 'everydev-cmp');
        return '<a href="#" onclick="if(window.CMP_showBanner){CMP_showBanner();} return false;">'.esc_html($text).'</a>';
    }
}

Everydev_CMP::boot();
