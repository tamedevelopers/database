<div class="load-more-container" style="text-align: center; margin: 20px 0;">
    <?php
        $page = $this->pagination->page;
        $totalPages = $this->pagination->pageCount;
        $isLast = $page >= $totalPages;
        $nextPage = $page + 1;
        $nextUrl = $this->pagination->createUrl($nextPage);
    ?>
    <?php if (!$isLast): ?>
        <button type="button" class="load-more-btn" data-page="<?php echo $nextPage; ?>" data-url="<?php echo $nextUrl; ?>" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Load More
        </button>
    <?php else: ?>
        <p>No more content to load.</p>
    <?php endif; ?>
</div>