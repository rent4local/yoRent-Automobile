<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Navigations', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div id="listing">
                        <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
    var NAVLINK_TYPE_CATEGORY_PAGE = <?php echo NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE; ?>;
</script>

