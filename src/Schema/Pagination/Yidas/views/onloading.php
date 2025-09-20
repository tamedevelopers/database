<div class="onload-container" data-pagination-scope style="text-align: center; margin: 20px 0;">
    <?php
      // Prepare variables in one scope
      $page = $this->pagination->page;
      $totalPages = $this->pagination->pageCount;
      $isLast = $page >= $totalPages;
      $nextPage = min($totalPages, $page + 1);
      $nextUrl = $this->pagination->createUrl($nextPage);
    ?>

    <div class="onload-status" aria-live="polite" aria-atomic="true" style="margin:10px 0;">
      <?php if(!$isLast): ?>
        <span class="onload-text">...</span> <!-- Loading ... -->
      <?php else: ?>
        <span class="onload-text"><?=$this->noContentLabel?></span>
      <?php endif; ?>
    </div>

    <?php if(!$isLast): ?>
      <a <?=$linkAttributes?>
        href="<?=$nextUrl?>"
        class="onload-trigger"
        data-page="<?=$nextPage?>"
        data-mode="append"
        data-target="[data-pagination-append]"
        data-history="none" 
        data-pagination="ajax"
        style="display:none;">Next</a>
    <?php endif; ?>
</div>

<script>
// Lightweight progressive AJAX for pagination (load-more friendly)
(function(){
  if(window.__TAME_PAGINATION_SCROLL_INITED__) return;
  window.__TAME_PAGINATION_SCROLL_INITED__ = true;

  function setupInfinite(scope){
    let link = scope.querySelector('.onload-trigger');
    if(!link) return;

    const targetSelector = link.getAttribute('data-target') || '[data-pagination-content]';
    const container = document.querySelector(targetSelector);
    if(!container) return;

    // sentinel inside scope, not after container
    let sentinel = document.createElement('div');
    sentinel.setAttribute('data-onload-sentinel','');
    sentinel.style.cssText = 'height:1px;';
    scope.appendChild(sentinel);

    let loading = false;
    let loadedPages = new Set();

    let observer = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting && !loading){
          let page = link.getAttribute('data-page');
          if(loadedPages.has(page)) return;

          loading = true;
          loadedPages.add(page);

          loadPage(link, container, scope).then(function(result){
            scope = result.newScope;
            link = scope.querySelector('.onload-trigger');
            let stillHasNext = result.stillHasNext;

            if(stillHasNext){
              sentinel.remove();
              sentinel = document.createElement('div');
              sentinel.setAttribute('data-onload-sentinel','');
              sentinel.style.cssText = 'height:1px;';
              scope.appendChild(sentinel);
              observer.observe(sentinel);
              loading = false;
            } else {
              observer.disconnect();
              sentinel.remove();
            }
          }).catch(function(){
            window.location.href = link.getAttribute('href');
          });
        }
      });
    }, { threshold: 1.0 });

    observer.observe(sentinel);
  }

  function loadPage(a, container, scope){
    return new Promise(function(resolve, reject){
      let href = a.getAttribute('href');
      let mode = a.getAttribute('data-mode') || 'replace'; 

      fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
          let parser = new DOMParser();
          let doc = parser.parseFromString(html, 'text/html');

          let newContainer = doc.querySelector(a.getAttribute('data-target'));
          let newScope = doc.querySelector('[data-pagination-scope]');
          if(!newContainer || !newScope) return reject();

          if(mode === 'append'){
            while(newContainer.firstChild){
              container.appendChild(newContainer.firstChild);
            }
          } else {
            container.innerHTML = newContainer.innerHTML;
          }

          scope.replaceWith(newScope);

          // update "showing"
          let newShowing = doc.querySelector('[data-pagination-showing]');
          let curShowing = document.querySelector('[data-pagination-showing]');
          if(newShowing && curShowing){
            curShowing.innerHTML = newShowing.innerHTML;
          }

          let nextLink = newScope.querySelector('.onload-trigger');
          resolve({ newScope: newScope, stillHasNext: !!nextLink });
        })
        .catch(reject);
    });
  }

  document.querySelectorAll('[data-pagination-scope]').forEach(setupInfinite);
})();
</script>
