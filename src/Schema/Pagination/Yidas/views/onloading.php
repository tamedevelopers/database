<div class="onload-container" data-pagination-scope style="text-align: center; margin: 20px 0;">
  <?php
    // Prepare variables
    $page = $this->pagination->page;
    $totalPages = $this->pagination->pageCount;
    $isLast = $page >= $totalPages;
    $nextPage = min($totalPages, $page + 1);
    $nextUrl = $this->pagination->createUrl($nextPage);
  ?>

  <div class="onload-status" aria-live="polite" aria-atomic="true" style="margin:10px 0;">
    <?php if(!$isLast): ?>
      <span class="onload-text">Loading on scroll...</span>
    <?php else: ?>
      <span class="onload-text">No more content to load.</span>
    <?php endif; ?>
  </div>

  <?php if(!$isLast): ?>
    <a <?=$linkAttributes?> href="<?=$nextUrl?>" class="onload-trigger" data-page="<?=$nextPage?>" data-mode="append" data-target="[data-pagination-append]" data-history="push" style="display:none;">Next</a>
  <?php endif; ?>
</div>
<script>
// Infinite scroll (onloading view)
(function(){
  if(window.__TAME_PAGINATION_ONLOADING_INITED__) return; // Guard against multiple inits
  window.__TAME_PAGINATION_ONLOADING_INITED__ = true;

  function closestAnchor(el){
    while(el && el !== document){ if(el.tagName === 'A') return el; el = el.parentNode; }
    return null;
  }

  function setupInfinite(scope){
    var link = scope.querySelector('.onload-trigger');
    if(!link) return;

    var sentinel = document.createElement('div');
    sentinel.setAttribute('data-onload-sentinel', '');
    sentinel.style.cssText = 'height: 1px;';

    var targetSelector = link.getAttribute('data-target') || '[data-pagination-content]';
    var container = document.querySelector(targetSelector);
    if(!container){ return; }

    // place sentinel after container to detect end of list
    container.parentNode.insertBefore(sentinel, container.nextSibling);

    var loading = false;
    var observer = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting && !loading){
          loading = true;
          link.click(); // reuse existing click/AJAX logic
          setTimeout(function(){ loading = false; }, 50);
        }
      });
    }, { rootMargin: '0px 0px 200px 0px' });

    observer.observe(sentinel);

    // Cleanup if controls are replaced
    scope.addEventListener('DOMNodeRemoved', function(ev){
      if(ev.target === scope){ try{ observer.disconnect(); }catch(_e){} }
    });
  }

  // Delegate click to reuse loading view behavior with history
  document.addEventListener('click', function(e){
    var a = closestAnchor(e.target);
    if(!a) return;
    if(a.classList.contains('onload-trigger')){
      // Ensure it is treated as AJAX pagination
      a.setAttribute('data-pagination', 'ajax');
    }
  });

  // Initialize for current scope
  document.querySelectorAll('[data-pagination-scope]').forEach(function(scope){
    if(scope.closest('.onload-container')){ setupInfinite(scope); }
  });

  // After AJAX replacement, re-init when new scope appears
  var mo = new MutationObserver(function(){
    document.querySelectorAll('[data-pagination-scope]').forEach(function(scope){
      if(scope.closest('.onload-container') && !scope.__onloadInited){
        scope.__onloadInited = true;
        setupInfinite(scope);
      }
    });
  });
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();
</script>