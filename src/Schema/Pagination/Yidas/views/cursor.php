<div data-pagination-scope>
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
  <div class="pagination ">
  
    <div class="pagination-cursor">
      <?php if($isFirst):?>
          <span <?=$linkAttributes?>><?=$this->prevPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> data-target="[data-pagination-content]" href="<?=$prevUrl?>"><?=$this->prevPageLabel?></a>
      <?php endif ?>
    
      <?php foreach ($this->_buttonStack as $key => $btnPage): ?>
        <a <?=$linkAttributes?> data-target="[data-pagination-content]" class="<?php if(($page)==$btnPage):?>active<?php endif ?>" href="<?=$this->pagination->createUrl($btnPage);?>"><?=$btnPage?></a>
      <?php endforeach ?>
      
      <?php if($isLast):?>
          <span <?=$linkAttributes?>><?=$this->nextPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> data-target="[data-pagination-content]" href="<?=$nextUrl?>"><?=$this->nextPageLabel?></a>
      <?php endif ?>
    </div>
  
  </div>
</div>
<script>
// Lightweight progressive AJAX for pagination (cursor view)
(function(){
  if(window.__TAME_PAGINATION_CURSOR_INITED__) return; // Guard for cursor view only
  window.__TAME_PAGINATION_CURSOR_INITED__ = true;

  function closestAnchor(el){
    while(el && el !== document){ if(el.tagName === 'A') return el; el = el.parentNode; }
    return null;
  }

  document.addEventListener('click', function(e){
    var a = closestAnchor(e.target);
    if(!a) return;
    if(a.getAttribute('data-pagination') !== 'ajax') return;

    var href = a.getAttribute('href');
    if(!href) return;

    // Prevent default only for our ajax link
    e.preventDefault();

    var targetSelector = a.getAttribute('data-target') || '[data-pagination-content]';
    var scope = a.closest('[data-pagination-scope]');
    var container = document.querySelector(targetSelector);
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

        container.innerHTML = newContainer.innerHTML;

        // Replace controls to keep next/prev in sync
        scope.replaceWith(newScope);

        // Update "showing" summary if present on the page
        var newShowing = doc.querySelector('[data-pagination-showing]');
        var curShowing = document.querySelector('[data-pagination-showing]');
        if(newShowing && curShowing){
          curShowing.innerHTML = newShowing.innerHTML;
        }

        try { window.history.pushState({}, '', href); } catch(_e) {}
      })
      .catch(function(){ window.location.href = href; })
      .finally(function(){ a.removeAttribute('aria-busy'); });
  });
})();
</script>