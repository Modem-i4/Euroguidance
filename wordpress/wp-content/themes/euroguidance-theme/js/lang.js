(function(){
  var links = document.querySelectorAll('.pb-lang-switch a');
  if (!links.length) return;

  var loc  = window.location;
  var path = loc.pathname || '/';
  var qs   = loc.search || '';
  var hash = loc.hash || '';
  var isEn = /^\/en(?:\/|$)/.test(path);

  var to = isEn ? path.replace(/^\/en(?:\/|$)/,'/')
                : (path === '/' ? '/en' : '/en' + path);

  var href = to.replace(/\/+/g,'/') + qs + hash;

  links.forEach(a => a.setAttribute('href', href));
})();
