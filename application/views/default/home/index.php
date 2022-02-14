<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<script>
    events.viewContent();
</script>

<div id="body" class="body" role="main">
    <!--slider[-->
    <?php
    if (isset($slides) && count($slides)) {
        $this->includeTemplate('_partial/heroSlider.php', array('slides' => $slides, 'siteLangId' => $siteLangId, 'searchForm' => $searchForm), false);
    }
    ?>
    <?php
    foreach ($collectionTemplates as $collection) {
        echo FatUtility::decodeHtmlEntities($collection['html']);
    }
    ?>
</div>

<!-- compare products lists starts here-->
<div id="compare_product_list_js"></div>
            <!-- compare products lists ends here-->
<?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1)) { ?>
    <script type="text/javascript">
        var data = 'detail_page=1';
        fcom.ajax(fcom.makeUrl('CompareProducts', 'listing'), data, function(res) 
        {
            $("#compare_product_list_js").html(res);
            $('body').addClass('is-compare-visible');
        });
    </script>
<?php } ?> 