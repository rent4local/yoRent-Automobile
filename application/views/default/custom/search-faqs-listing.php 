<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="col-md-8 faqSectionJs">
    <?php if (!empty($result)) {
        $catsHtml = '';
        $catsResult = [];
        $quesHtml = '';
        foreach ($result as $index => $faqCat) {
            if (!in_array($faqCat['faqcat_id'], $catsResult)) {
                $catsResult[] = $faqCat['faqcat_id'];
                $catsHtml .= '<a href="javascript:void(0);" onClick="searchFaqs(\'' . $page . '\',' . $faqCat['faqcat_id'] . ');" id="' . $faqCat['faqcat_id'] . '" class="faqCatIdJs ' . (0 == $index ? "is--active" : "") . '">' . $faqCat['faqcat_name'] . '</a>';
            }
        } ?>
        <div class="faq-filters mb-4" id="categoryPanel">
            <?php echo $catsHtml; ?>
        </div>
        <ul class="faqlist" id="listing">
            <?php  $this->includeTemplate('_partial/custom/faq-list.php', array('list' => $result, 'siteLangId' => $siteLangId), false); ?>
        </ul>
    <?php } else { ?>        
        <div class="faq-filters mb-4" id="categoryPanel"></div>
        <ul class="faqlist" id="listing">
        <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false);?>
        </ul>
    <?php } ?>
</div>