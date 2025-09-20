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
<div class="pagination">

  <?php if($this->firstPageLabel):?>
    <?php if($isFirst):?>
        <span <?=$linkAttributes?>><?=$this->firstPageLabel?></span>
      <?php else: ?>
        <a <?=$linkAttributes?> href="<?=$firstUrl?>"><?=$this->firstPageLabel?></a>
    <?php endif ?>
  <?php endif ?>

  <?php if($isFirst):?>
      <span <?=$linkAttributes?>><?=$this->prevPageLabel?></span>
    <?php else: ?>
      <a <?=$linkAttributes?> href="<?=$prevUrl?>"><?=$this->prevPageLabel?></a>
  <?php endif ?>

  <?php foreach ($this->_buttonStack as $key => $btnPage): ?>
    <a <?=$linkAttributes?> class="<?php if(($page)==$btnPage):?>active<?php endif ?>" href="<?=$this->pagination->createUrl($btnPage);?>"><?=$btnPage?></a>
  <?php endforeach ?>
  
  <?php if($isLast):?>
      <span <?=$linkAttributes?>><?=$this->nextPageLabel?></span>
    <?php else: ?>
      <a <?=$linkAttributes?> href="<?=$nextUrl?>"><?=$this->nextPageLabel?></a>
  <?php endif ?>

  <?php if($this->lastPageLabel):?>
    <?php if($isLast):?>
      <span <?=$linkAttributes?>><?=$this->lastPageLabel?></span>
    <?php else: ?>
      <a <?=$linkAttributes?> href="<?=$lastUrl?>"><?=$this->lastPageLabel?></a>
    <?php endif ?>
  <?php endif ?>

</div>