<?php
  // Prepare variables in one scope (no JS added here)
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
<nav aria-label="Page navigation example">
  <ul class="pagination <?php if($this->alignCenter):?>justify-content-center<?php endif?> <?=$this->ulCssClass?>">
    
    <?php if($this->firstPageLabel):?>
      <li class="<?=$this->pageCssClass?> <?=$this->firstPageCssClass?> <?php if($isFirst):?>disabled<?php endif ?>">
        <?php if($isFirst):?>
          <span <?=$linkAttributes?> class="page-link"><?=$this->firstPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> class="page-link" href="<?=$firstUrl?>"><?=$this->firstPageLabel?></a>
        <?php endif ?>
      </li>
    <?php endif ?>

    <li class="<?=$this->pageCssClass?> <?=$this->prevPageCssClass?> <?php if($isFirst):?>disabled<?php endif ?>">
        <?php if($isFirst):?>
          <span <?=$linkAttributes?> class="page-link"><?=$this->prevPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> class="page-link" href="<?=$prevUrl?>"><?=$this->prevPageLabel?></a>
        <?php endif ?>
    </li>

    <?php foreach ($this->_buttonStack as $key => $btnPage): ?>
      <li class="<?=$this->pageCssClass?> <?php if(($page)==$btnPage):?>active<?php endif ?>">
        <a <?=$linkAttributes?> class="page-link" href="<?=$this->pagination->createUrl($btnPage);?>"><?=$btnPage?></a>
      </li>
    <?php endforeach ?>

    <li class="<?=$this->pageCssClass?> <?=$this->nextPageCssClass?> <?php if($isLast):?>disabled<?php endif ?>">
      <?php if($isLast):?>
        <span <?=$linkAttributes?> class="page-link"><?=$this->nextPageLabel?></span>
      <?php else: ?>
        <a <?=$linkAttributes?> class="page-link" href="<?=$nextUrl?>"><?=$this->nextPageLabel?></a>
      <?php endif ?>
    </li>

    <?php if($this->lastPageLabel):?>
      <li class="<?=$this->pageCssClass?> <?=$this->lastPageCssClass?> <?php if($isLast):?>disabled<?php endif ?>">
        <?php if($isLast):?>
          <span <?=$linkAttributes?> class="page-link"><?=$this->lastPageLabel?></span>
        <?php else: ?>
          <a <?=$linkAttributes?> class="page-link" href="<?=$lastUrl?>"><?=$this->lastPageLabel?></a>
        <?php endif ?>
      </li>
    <?php endif ?>

  </ul>
</nav>