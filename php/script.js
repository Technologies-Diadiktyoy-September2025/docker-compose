(function(){
  function setCookie(name, value, days){
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days*24*60*60*1000));
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
  }

  function getCookie(name){
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
  }

  function applyTheme(theme){
    var html = document.documentElement;
    if(theme === 'dark'){
      html.setAttribute('data-theme', 'dark');
    } else {
      html.setAttribute('data-theme', 'light');
    }
  }

  function initTheme(){
    var saved = getCookie('site_theme');
    var preferred = saved || 'light';
    applyTheme(preferred);
    var toggleBtn = document.querySelector('[data-action="toggle-theme"]');
    if (toggleBtn){
      toggleBtn.textContent = (preferred === 'dark') ? 'Light mode' : 'Dark mode';
      toggleBtn.addEventListener('click', function(){
        var current = document.documentElement.getAttribute('data-theme') || 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        setCookie('site_theme', next, 365);
        toggleBtn.textContent = (next === 'dark') ? 'Light mode' : 'Dark mode';
      });
    }
  }

  function initAccordion(){
    var items = Array.prototype.slice.call(document.querySelectorAll('.accordion-item'));
    if(items.length === 0) return;
    items.forEach(function(item){
      var header = item.querySelector('.accordion-header');
      if(!header) return;
      header.addEventListener('click', function(){
        // Close others
        items.forEach(function(other){
          if(other !== item) other.classList.remove('open');
        });
        // Toggle current
        item.classList.toggle('open');
      });
    });
  }

  function onReady(fn){
    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  onReady(function(){
    initTheme();
    initAccordion();
  });
})();

