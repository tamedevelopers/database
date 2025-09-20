<div data-pagination-scope>
  <div class="pagination ">
  
    <div class="pagination-cursor">
      <?php if($isFirst):?>
          <span <?=$linkAttributes?>><?=$this->prevPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> data-target="[data-pagination-content]" href="<?=$this->pagination->createUrl($page-1);?>"><?=$this->prevPageLabel?></a>
      <?php endif ?>
    
      <?php foreach ($this->_buttonStack as $key => $btnPage): ?>
        <a <?=$linkAttributes?> data-target="[data-pagination-content]" class="<?php if(($page)==$btnPage):?>active<?php endif ?>" href="<?=$this->pagination->createUrl($btnPage);?>"><?=$btnPage?></a>
      <?php endforeach ?>
      
      <?php if($isLast):?>
          <span <?=$linkAttributes?>><?=$this->nextPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> data-target="[data-pagination-content]" href="<?=$this->pagination->createUrl($page+1);?>"><?=$this->nextPageLabel?></a>
      <?php endif ?>
    </div>
  
  </div>
</div>
<script>
// Lightweight progressive AJAX for pagination (cursor view)
(function(){
  if(window.__TAME_PAGINATION_INITED__) return;
  window.__TAME_PAGINATION_INITED__ = true;

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
        scope.replaceWith(newScope);
        try { window.history.pushState({}, '', href); } catch(_e) {}
      })
      .catch(function(){ window.location.href = href; })
      .finally(function(){ a.removeAttribute('aria-busy'); });
  });
})();
</script>