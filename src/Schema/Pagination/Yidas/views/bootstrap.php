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

<nav aria-label="Page navigation example" data-pagination-scope>
  <ul class="pagination <?php if($this->alignCenter):?>justify-content-center<?php endif?> <?=$this->ulCssClass?>">
    
    <?php if($this->firstPageLabel):?>
      <li class="<?=$this->pageCssClass?> <?=$this->firstPageCssClass?> <?php if($isFirst):?>disabled<?php endif ?>">
        <?php if($isFirst):?>
          <span <?=$linkAttributes?> class="page-link"><?=$this->firstPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> data-pagination="ajax" data-history="none" data-mode="replace" data-target="[data-pagination-content]" class="page-link" href="<?=$firstUrl?>"><?=$this->firstPageLabel?></a>
        <?php endif ?>
      </li>
    <?php endif ?>

    <li class="<?=$this->pageCssClass?> <?=$this->prevPageCssClass?> <?php if($isFirst):?>disabled<?php endif ?>">
        <?php if($isFirst):?>
          <span <?=$linkAttributes?> class="page-link"><?=$this->prevPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> data-pagination="ajax" data-history="none" data-mode="replace" data-target="[data-pagination-content]" class="page-link" href="<?=$prevUrl?>"><?=$this->prevPageLabel?></a>
        <?php endif ?>
    </li>

    <?php foreach ($this->_buttonStack as $key => $btnPage): ?>
      <li class="<?=$this->pageCssClass?> <?php if(($page)==$btnPage):?>active<?php endif ?>">
        <a <?=$linkAttributes?> data-pagination="ajax" data-history="none" data-mode="replace" data-target="[data-pagination-content]" class="page-link" href="<?=$this->pagination->createUrl($btnPage);?>"><?=$btnPage?></a>
      </li>
    <?php endforeach ?>

    <li class="<?=$this->pageCssClass?> <?=$this->nextPageCssClass?> <?php if($isLast):?>disabled<?php endif ?>">
      <?php if($isLast):?>
        <span <?=$linkAttributes?> class="page-link"><?=$this->nextPageLabel?></span>
      <?php else: ?>
        <a <?=$linkAttributes?> data-pagination="ajax" data-history="none" data-mode="replace" data-target="[data-pagination-content]" class="page-link" href="<?=$nextUrl?>"><?=$this->nextPageLabel?></a>
      <?php endif ?>
    </li>

    <?php if($this->lastPageLabel):?>
      <li class="<?=$this->pageCssClass?> <?=$this->lastPageCssClass?> <?php if($isLast):?>disabled<?php endif ?>">
        <?php if($isLast):?>
          <span <?=$linkAttributes?> class="page-link"><?=$this->lastPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> data-pagination="ajax" data-history="none" data-mode="replace" data-target="[data-pagination-content]" class="page-link" href="<?=$lastUrl?>"><?=$this->lastPageLabel?></a>
        <?php endif ?>
      </li>
    <?php endif ?>

  </ul>
</nav>

<script>
// Lightweight progressive AJAX for pagination (bootstrap view)
(function(){
  if(window.__TAME_PAGINATION_BOOTSTRAP_INITED__) return;
  window.__TAME_PAGINATION_BOOTSTRAP_INITED__ = true;

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

    e.preventDefault();

    var mode = a.getAttribute('data-mode') || 'replace';
    var targetSelector = a.getAttribute('data-target') || '[data-pagination-content]';
    var scope = a.closest('[data-pagination-scope]');
    var container = document.querySelector(targetSelector);

    if(!container || !scope){ window.location.href = href; return; }

    a.setAttribute('aria-busy', 'true');

    var historyMode = a.getAttribute('data-history') || 'push'; // 'push' | 'replace' | 'none'
    var fetchOptions = { headers: { 'X-Requested-With': 'XMLHttpRequest' } };

    fetch(href, fetchOptions)
      .then(function(res){ return res.text(); })
      .then(function(html){
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');

        var newContainer = doc.querySelector(targetSelector);
        var newScope = doc.querySelector('[data-pagination-scope]');

        if(!newContainer || !newScope){ window.location.href = href; return; }

        if(mode === 'append'){
          while(newContainer.firstChild){
            container.appendChild(newContainer.firstChild);
          }
        } else {
          container.innerHTML = newContainer.innerHTML;
        }

        scope.replaceWith(newScope);

        var newShowing = doc.querySelector('[data-pagination-showing]');
        var curShowing = document.querySelector('[data-pagination-showing]');
        if(newShowing && curShowing){
          curShowing.innerHTML = newShowing.innerHTML;
        }

        try {
          if(historyMode === 'push') window.history.pushState({}, '', a.getAttribute('href'));
          else if(historyMode === 'replace') window.history.replaceState({}, '', a.getAttribute('href'));
        } catch(_e) {}
      })
      .catch(function(){ window.location.href = a.getAttribute('href'); })
      .finally(function(){ a.removeAttribute('aria-busy'); });
  });
})();
</script>
