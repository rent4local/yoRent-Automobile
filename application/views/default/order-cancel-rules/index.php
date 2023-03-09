<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Cancellation_Penalty_Rules', $siteLangId); ?>
                </h2>
                <small class="note">(<?php echo Labels::getLabel('LBL_Valid_Only_With_Rental_Orders', $siteLangId); ?>)</small>
                <?php
                if (!empty($warningMsg)) {
                    echo "<div class='row mt-3'><div class='col-md-12'><h6>" . Labels::getLabel('LBL_Slots_Are_Missing_For_Following_Duartions', $siteLangId) . "</h6>
                    <ul class='missing-duration'>";
                    foreach ($warningMsg as $msg) {
                        echo '<li class="text-danger">' . $msg . '</li>';
                    }
                    echo "</ul>
                    <small class='note'>" . Labels::getLabel('LBL_For_Missing_Slots_100%_Refund_will_be_initiated.', $siteLangId) . "</small>
                    </div></div>";
                }
                // if (!empty($extendChildOrder)) {
                //     echo '<h6 class="text-danger">' . Labels::getLabel('LBL_This_order_is_extended_By', $siteLangId) . ' <a href="' . UrlHelper::generateUrl('Buyer', 'viewOrder', array($extendChildOrder['opd_order_id'], $extendChildOrder['opd_op_id'])) . '">#' . $extendChildOrder['opd_order_id'] . '</a> </h6>';
                // }
                ?>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="ruleForm--js">
                                <?php $this->includeTemplate('order-cancel-rules/form.php', array('siteLangId' => $siteLangId, 'frm' => $frm, 'isInfinty' => 0), false); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <?php
            $changeRuleTitle = Labels::getLabel('LBL_Enable_All_Rules', $siteLangId);
            $statusToChange = applicationConstants::ACTIVE;
            if(!empty($data) && $data[0]['ocrule_active'] == applicationConstants::ACTIVE) { 
                $changeRuleTitle = Labels::getLabel('LBL_Disable_All_Rules', $siteLangId);
                $statusToChange = applicationConstants::INACTIVE;
             } ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"></div>
                            <div class="">
                                <a class="btn btn-outline-brand btn-sm" title="<?php echo $changeRuleTitle; ?>" onclick="changeCancelRuleStatus(<?php echo $statusToChange;?>)" href="javascript:void(0);">
                                    <?php echo $changeRuleTitle; ?>
                                </a>
                                <a class="btn btn-outline-brand btn-sm" title="<?php echo Labels::getLabel('LBL_View_Admin_Rules', $siteLangId); ?>" onclick="viewAdminRules()" href="javascript:void(0);">
                                    <?php echo Labels::getLabel('LBL_View_Admin_Rules', $siteLangId); ?>
                                </a>
                                <a class="btn btn-outline-brand btn-sm formActionBtn-js formActions-css" title="<?php echo Labels::getLabel('LBL_Delete_Rules', $siteLangId); ?>" onclick="deleteSelected()" href="javascript:void(0);">
                                    <?php echo Labels::getLabel('LBL_REMOVE', $siteLangId); ?>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="listing">
                                <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>