<?php
if (isset($collection['faqs']) && count($collection['faqs']) > 0) {
    $faqCategories = array();
    foreach ($collection['faqs'] as $faq) {
        $faqCategories[$faq['faqcat_id']]['faqcat_name'] = $faq['faqcat_name'];
        $faqCategories[$faq['faqcat_id']]['faqs'][$faq['faq_id']] = $faq;
    }
?>
    <section class="section collection-faq" id="faq_lay2_<?php echo $collection['collection_id']; ?>">
        <div class="container container--narrow">
            <div class="section__title  text-center">
                <h5><?php echo $collection['collection_description']; ?></h5>
                <h2><?php echo $collection['collection_name']; ?></h2>
            </div>
            <div class="faqTabs--flat-js">
                <ul class="tabs--faq">
                    <?php
                    $count = 0;
                    foreach ($faqCategories as $faqCatId => $faqCat) {
                        if ($count > $recordLimit) {
                            break;
                        }
                    ?>
                        <li class="<?php echo ($count == 0) ? 'active' : ''; ?>">
                            <a href="#tbfaq-<?php echo $collection['collection_id'] . '_' . $faqCatId; ?>" data-id="tbfaq-<?php echo $collection['collection_id'] . '_' . $faqCatId; ?>" class="tabOpen-js"><?php echo $faqCat['faqcat_name']; ?></a>
                        </li>
                    <?php
                        $count++;
                    }
                    ?>
                </ul>
            </div>
            <div class="faq-wrapper">
                <?php
                $count = 0;
                foreach ($faqCategories as $faqCatId => $faqCat) {

                    if ($count > $recordLimit) {
                        break;
                    } ?>
                    <div id="tbfaq-<?php echo $collection['collection_id'] . '_' . $faqCatId; ?>" class="tabs-content tabs-content-home--js">
                        <?php
                        $i = 0;
                        foreach ($faqCat['faqs'] as $faqId => $faq) {
                        ?>
                            <div class="<?php echo ($i == 0) ? "is-active" : ""; ?> js-group faq-component">
                                <h4 class="faq-title js-group-head"><?php echo $faq['faq_title']; ?></h4>
                                <div class="faq-component-wrapp faq-content js-group-body">
                                    <p><?php echo $faq['faq_content']; ?></p>
                                </div>
                            </div>
                        <?php
                            $i++;
                        }
                        $count++;
                        ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </section>
<?php } ?>