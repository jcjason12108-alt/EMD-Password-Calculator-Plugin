
(function(){
  function qs(s){ return document.querySelector(s); }
  document.addEventListener('DOMContentLoaded', function(){
    var btn = qs('.emd-pwc-copy');
    var input = qs('.emd-pwc-shortcode-input');
    var badge = qs('.emd-pwc-copied');
    if(!btn || !input || !badge) return;
    btn.addEventListener('click', function(){
      input.select();
      try {
        document.execCommand('copy');
      } catch (e) {}
      badge.hidden = false;
      setTimeout(function(){ badge.hidden = true; }, 1200);
    });
  });
})();
