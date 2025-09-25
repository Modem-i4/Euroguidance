<?php
add_action('wp_head', function () {
  if (empty($_GET['trp-edit-translation'])) return;
  ?>
  <script>
  (function(){
    try{
      var dTop = (window.top && window.top.document) ? window.top.document : null;
      if(!dTop) return;
      var css = "body.trp-editor-body #trp-editors-navigation-tabs,body.trp-editor-body #trp-view-as,body.trp-editor-body #trp-upsell-section-container{display:none!important}";
      var s = dTop.getElementById('tp-editor-cleanup');
      if(!s){
        s = dTop.createElement('style');
        s.id = 'tp-editor-cleanup';
        var head = dTop.head || dTop.getElementsByTagName('head')[0] || dTop.documentElement;
        head.insertBefore(s, head.firstChild || null);
      }
      s.textContent = css;
    }catch(e){}
  })();
  </script>
  <?php
}, PHP_INT_MIN);

add_action('wp_footer', function () {
  if (empty($_GET['trp-edit-translation'])) return;
  ?>
  <script>
  (function(){
    var d; try{ d = (window.top && window.top.document) ? window.top.document : document; }catch(e){ d = document; }
    function apply(){
      d.querySelectorAll('.trp-help-panel').forEach(function(el){ el.classList.add('trp-help-panel-open'); });
      d.querySelectorAll('.trp-controls-container.trp-show-editors-navigation').forEach(function(el){ el.classList.add('help-panel-open'); });
    }
    if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', apply, {once:true}); else apply();
    try{
      var mo = new MutationObserver(function(){ apply(); });
      mo.observe(d.documentElement || d.body, {childList:true, subtree:true});
      setTimeout(function(){ try{ mo.disconnect(); }catch(e){} }, 5000);
    }catch(e){}
  })();
  </script>
  <?php
}, 9999);

add_action('enqueue_block_editor_assets', function() {
    wp_dequeue_script('trp-block-controls'); // handle має збігатися з тим, що реєструє TranslatePress
});
