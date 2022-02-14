<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$yesNoArr = applicationConstants::getYesNoArr($adminLangId);
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Addons', $adminLangId); ?> </h5>
                            <?php /* $this->includeTemplate('_partial/header/header-breadcrumb.php'); */ ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Addons_View', $adminLangId); ?> </h4>
                        <div>
                            <a href="<?php echo UrlHelper::generateUrl('AddonProducts', 'listing'); ?>" class="btn btn-outline-brand btn-sm no-print" title="
                                <?php echo Labels::getLabel('LBL_Back_to_listing', $adminLangId); ?>">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                    <div class="sectionbody space">
                        <div class="add border-box border-box--space">
                            <div class="repeatedrow">
                                <form class="web_form form_horizontal">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3><?php echo Labels::getLabel('LBL_Product_Information',$adminLangId); ?></h3>
                                        </div>
                                    </div>
                                    <div class="rowbody">
                                        <div class="listview">
                                        <?php
                                         
                                        
                                        if(!empty($addonProdData)) {
                                            foreach($addonProdData as $key => $addonProd) {
                                                switch ($key) {
                                                    case 'addonprod_title':
                                                        ?>
                                                        <dl class="list">
                                                            <dt><?php echo Labels::getLabel('LBL_Product_Name',$adminLangId); ?></dt>
                                                            <dd><?php echo $addonProd[$adminLangId];?></dd>
                                                        </dl>
                                                        <?php
                                                    break;
                                                    case 'taxcat_name':
                                                        ?>
                                                        <dl class="list">
                                                            <dt><?php echo Labels::getLabel('LBL_Tax_Category_Name',$adminLangId); ?></dt>
                                                            <dd><?php echo $addonProd;?></dd>
                                                        </dl>
                                                        <?php
                                                    break;
                                                    case 'addonprod_price':
                                                        ?>
                                                        <dl class="list">
                                                            <dt><?php echo Labels::getLabel('LBL_Product_Price',$adminLangId); ?></dt>
                                                            <dd><?php echo $addonProd;?></dd>
                                                        </dl>
                                                        <?php
                                                    break; 
                                                    case 'addonprod_description_' . $adminLangId:
                                                        if(!empty($addonProd)) {
                                                        ?>
                                                        <dl class="list">
                                                            <dt><?php echo Labels::getLabel('LBL_Product_Description',$adminLangId); ?></dt>
                                                            <dd><?php echo CommonHelper::renderHtml($addonProd);?></dd>
                                                        </dl>
                                                        <?php
                                                        }
                                                    break;
                                                    case 'selprod_is_eligible_cancel':
                                                        
                                                        ?>
                                                        <dl class="list">
                                                            <dt><?php echo Labels::getLabel('LBL_Is_Eligible_For_Cancel',$adminLangId); ?></dt>
                                                            <dd><?php echo CommonHelper::renderHtml($yesNoArr[$addonProd]);?></dd>
                                                        </dl>
                                                        <?php
                                                    break;
                                                    case 'selprod_is_eligible_refund':
                                                        ?>
                                                        <dl class="list">
                                                            <dt><?php echo Labels::getLabel('LBL_Is_Eligible_For_Refund',$adminLangId); ?></dt>
                                                            <dd><?php echo CommonHelper::renderHtml($yesNoArr[$addonProd]);?></dd>
                                                        </dl>
                                                        <?php
                                                    break;
                                                } ?>
                                            <?php } 
                                        } ?>		
                                        </div>
                                    </div>           
                                </form>  
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>