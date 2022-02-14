<?php
$searchForm->setFormTagAttribute('onSubmit', 'submitSiteSearch(this, 12); return(false);');
$keywordFld = $searchForm->getField('keyword');
$keywordFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword_Search', $siteLangId));

$locFld = $searchForm->getField('location');
$locFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Location_Search', $siteLangId));

$locFld = $searchForm->getField('rentaldates');
$locFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Dates', $siteLangId));
if (isset($postedData['rentalstart']) && !empty($postedData['rentalstart'])) {
    $locFld->addFieldTagAttribute('id', $postedData['rentalstart'] . ' - ' . $postedData['rentalend']);
}
?>
<div class="main-search-bar" data-close-on-click-outside="main-search-bar">
    <?php echo $searchForm->getFormTag(); ?>
    <div class="site-search-form">
        <ul>
            <li>
                <div class="form-group">
                    <label class="field_label"><?php echo $searchForm->getField('keyword')->getCaption(); ?></label>
                    <?php echo $searchForm->getFieldHtml('keyword'); ?>
                </div>

            </li>
            <li>
                <div class="form-group">
                    <label class="field_label"><?php echo $searchForm->getField('location')->getCaption(); ?></label>
                    <?php echo $searchForm->getFieldHtml('location'); ?>
                </div>
            </li>
            <li>
                <div class="form-group">
                    <label class="field_label"><?php echo $searchForm->getField('rentaldates')->getCaption(); ?></label>
                    <?php echo $searchForm->getFieldHtml('rentaldates'); ?>
                    <?php echo $searchForm->getFieldHtml('rentalstart'); ?>
                    <?php echo $searchForm->getFieldHtml('rentalend'); ?>

                </div>
            </li>
            <li>
                <div class="form-group">
                    <?php echo $searchForm->getFieldHtml('searchButton'); ?>

                </div>
            </li>

        </ul>


    </div>
    </form>
</div>