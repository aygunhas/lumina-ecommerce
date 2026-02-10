<div x-show="!loaded"
     x-cloak
     x-transition:leave="transition ease-out duration-1000"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100] bg-white flex items-center justify-center"
     aria-hidden="true"
     style="display: block;">
    <span class="font-display text-3xl tracking-[0.5em] text-primary animate-pulse">LUMINA</span>
</div>
<script>
// Fallback: Alpine.js yüklenmezse loader'ı kapat
(function() {
  var loader = document.querySelector('[x-show]');
  if (!loader) return;
  
  function hideLoader() {
    if (loader) {
      loader.style.transition = 'opacity 1s ease-out';
      loader.style.opacity = '0';
      setTimeout(function() {
        if (loader) loader.style.display = 'none';
      }, 1000);
    }
  }
  
  // Sayfa yüklendiğinde kapat
  if (document.readyState === 'complete') {
    setTimeout(hideLoader, 800);
  } else {
    window.addEventListener('load', function() {
      setTimeout(hideLoader, 800);
    });
  }
  
  // Maksimum 3 saniye sonra zorla kapat
  setTimeout(hideLoader, 3000);
})();
</script>