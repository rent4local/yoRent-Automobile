<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section post-detail">
    <div class="container">
        <div class="row">
            <div class="col-xl-9 col-lg-8 mb-4 mb-md-0">
                <div class="posted-content">
                    <div id="blogs-listing-js" class="row"></div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4">
                <?php $this->includeTemplate('_partial/blogSidePanel.php', array('popularPostList' => $popularPostList, 'featuredPostList' => $featuredPostList, 'siteLangId' => $siteLangId)); ?>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    var keyword = '<?php echo (isset($keyword)) ? $keyword : ''; ?>';
</script>
