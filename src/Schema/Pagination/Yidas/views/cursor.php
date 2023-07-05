<div class="pagination ">

  <div class="pagination-cursor">
    <?php if($isFirst):?>
        <span <?=$linkAttributes?>><?=$this->prevPageLabel?></span>
      <?php else: ?>
        <a <?=$linkAttributes?> href="<?=$this->pagination->createUrl($page-1);?>"><?=$this->prevPageLabel?></a>
    <?php endif ?>
  
    <?php foreach ($this->_buttonStack as $key => $btnPage): ?>
      <a <?=$linkAttributes?> class="<?php if(($page)==$btnPage):?>active<?php endif ?>" href="<?=$this->pagination->createUrl($btnPage);?>"><?=$btnPage?></a>
    <?php endforeach ?>
    
    <?php if($isLast):?>
        <span <?=$linkAttributes?>><?=$this->nextPageLabel?></span>
      <?php else: ?>
        <a <?=$linkAttributes?> href="<?=$this->pagination->createUrl($page+1);?>"><?=$this->nextPageLabel?></a>
    <?php endif ?>
  </div>

</div>