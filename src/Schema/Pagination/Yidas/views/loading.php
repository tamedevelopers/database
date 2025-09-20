<div class="load-more-container" data-pagination-scope style="text-align: center; margin: 20px 0;">
    <?php
        // Prepare variables in one scope
        $page = $this->pagination->page;
        $totalPages = $this->pagination->pageCount;
        $isFirst = $page <= 1;
        $isLast = $page >= $totalPages;
        $prevPage = max(1, $page - 1);
        $nextPage = min($totalPages, $page + 1);
        $firstUrl = $this->pagination->createUrl(1);
        $prevUrl = $this->pagination->createUrl($prevPage);
        $nextUrl = $this->pagination->createUrl($nextPage);
        $lastUrl = $this->pagination->createUrl($totalPages);
    ?>
    <?php if (!$isLast): ?>
        <a <?=$linkAttributes?> href="<?php echo $nextUrl; ?>" class="load-more-btn" data-page="<?php echo $nextPage; ?>" data-mode="append" data-target="[data-pagination-append]" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block;">
            Load More
        </a>
    <?php else: ?>
        <p>No more content to load.</p>
    <?php endif; ?>
</div>
<script>
// Lightweight progressive AJAX for pagination (load-more friendly)
(function(){
  if(window.__TAME_PAGINATION_LOADING_INITED__) return; // Guard against multiple inits for loading view
  window.__TAME_PAGINATION_LOADING_INITED__ = true;

  function closestAnchor(el){
    while(el && el !== document){
      if(el.tagName === 'A') return el;
      el = el.parentNode;
    }
    return null;
  }

  document.addEventListener('click', function(e){
    var a = closestAnchor(e.target);
    if(!a) return;
    if(a.getAttribute('data-pagination') !== 'ajax') return;
    var href = a.getAttribute('href');
    if(!href) return;

    // Only prevent default if this is our AJAX pagination link
    e.preventDefault();

    var mode = a.getAttribute('data-mode') || 'replace';
    var targetSelector = a.getAttribute('data-target') || '[data-pagination-content]';
    var scope = a.closest('[data-pagination-scope]');
    var container = document.querySelector(targetSelector);

    // Fallback when no container or scope found
    if(!container || !scope){ window.location.href = href; return; }

    a.setAttribute('aria-busy', 'true');

    fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
      .then(function(res){ return res.text(); })
      .then(function(html){
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');

        var newContainer = doc.querySelector(targetSelector);
        var newScope = doc.querySelector('[data-pagination-scope]');

        if(!newContainer || !newScope){ window.location.href = href; return; }

        if(mode === 'append'){
          // Append children
          while(newContainer.firstChild){
            container.appendChild(newContainer.firstChild);
          }
        } else {
          // Replace content
          container.innerHTML = newContainer.innerHTML;
        }

        // Replace controls to keep next/prev in sync
        scope.replaceWith(newScope);

        try { window.history.pushState({}, '', href); } catch(_e) {}
      })
      .catch(function(){ window.location.href = href; })
      .finally(function(){ a.removeAttribute('aria-busy'); });
  });
})();
</script>