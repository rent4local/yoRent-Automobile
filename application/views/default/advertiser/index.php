<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/advertiser/advertiserDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('Lbl_Advertiser', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="js-widget-scroll widget-scroll">
                <?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
                <div class="widget widget-stats">
                    <a href="<?php echo UrlHelper::generateUrl('Account', 'credits'); ?>">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><?php echo Labels::getLabel('LBL_Credits', $siteLangId);?></h5>
                                <i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#credits" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#Credits"></use>
                                    </svg>
                                </i>
                            </div>
                            <div class="card-body ">
                                <div class="stats">
                                    <div class="stats-number">
                                        <ul>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Total', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($walletBalance);?></span>
                                            </li>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Credits_earned_today', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($txnsSummary['total_earned']);?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php }?>
                <div class="widget widget-stats">
                    <a href="javascript:void(0)" onClick="redirectToPromotions('<?php echo UrlHelper::generateUrl('advertiser', 'promotions');?>')">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><?php echo Labels::getLabel('LBL_Active_Promotions', $siteLangId);?></h5>
                                <i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#speaker" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#speaker"></use>
                                    </svg>
                                </i>
                            </div>
                            <div class="card-body ">
                                <div class="stats">
                                    <div class="stats-number">
                                        <ul>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Total_Active_promotions', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo $totActivePromotions; ?></span>
                                            </li>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Active_promotions_Expense', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($activePromotionChargedAmount);?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="widget widget-stats">
                    <a href="<?php echo UrlHelper::generateUrl('advertiser', 'promotionCharges');?>">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><?php echo Labels::getLabel('LBL_All_Promotions', $siteLangId);?></h5>
                                <i class="icn">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#give-money" href="<?php echo CONF_WEBROOT_URL;?>images/retina/sprite.svg#give-money"></use>
                                    </svg>
                                </i>
                            </div>
                            <div class="card-body ">
                                <div class="stats">
                                    <div class="stats-number">
                                        <ul>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Total_Promotions', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($totPromotions); ?></span>
                                            </li>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Total_Expense', $siteLangId);?></span>
                                                <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($totChargedAmount);?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title "><?php echo Labels::getLabel('LBL_Active_Promotions', $siteLangId);?></h5>
                            <?php if (count($activePromotions)>0) { ?>
                            <div class="action">
                                <a href="<?php echo UrlHelper::generateUrl('advertiser', 'promotions');?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId);?></a>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="card-body ">
							<div class="scroll scroll-x js-scrollable table-wrap">
								<table class="table table-justified">                                
								    <thead>
										<tr class="">
											<th colspan="2"><?php echo Labels::getLabel('LBL_Promotions', $siteLangId);?></th>
											<th ><?php echo Labels::getLabel('LBL_Type', $siteLangId);?></th>
											<th ><?php echo Labels::getLabel('LBL_CPC', $siteLangId);?></th>
											<th ><?php echo Labels::getLabel('LBL_Budget', $siteLangId);?></th>
											<th><?php echo Labels::getLabel('LBL_Clicks', $siteLangId);?></th>
											<th><?php echo Labels::getLabel('LBL_Duration', $siteLangId);?></th>
											<th><?php echo Labels::getLabel('LBL_Approved', $siteLangId);?></th>
											<th></th>
										</tr>
										</thead>
										<tbody>
										<?php if (count($activePromotions) > 0) {
											$arrYesNo = applicationConstants::getYesNoArr($siteLangId);
											foreach ($activePromotions as $promotionId => $row) {
												$duraionStr = Labels::getLabel('LBL_Start_Date', $siteLangId).' : '.FatDate::format($row[    'promotion_start_date']).'<br>';
												$duraionStr.= Labels::getLabel('LBL_End_Date', $siteLangId).' : '.FatDate::format($row['promotion_end_date']); ?>
										<tr>
											<td  colspan="2">
												<?php echo $row['promotion_name']; ?>
											</td>
											<td><?php echo $typeArr[$row['promotion_type']]; ?>
											</td>
											<td>
												<?php echo CommonHelper::displayMoneyFormat($row['promotion_cpc']); ?>
											</td>
											<td>
												<?php echo CommonHelper::displayMoneyFormat($row['promotion_budget']) ; ?>
											</td>
											<td>
												<?php echo FatUtility::int($row['clicks']); ?>
											</td>
											<td>
												<?php   echo $duraionStr; ?>
											</td>
											<td>
												<?php echo $arrYesNo[$row['promotion_approved']]; ?>
											</td>
											<td>
												<ul class="actions">
													<li><a  title="<?php echo Labels::getLabel('LBL_Analytics', $siteLangId); ?>"   href="<?php echo UrlHelper::generateUrl('advertiser', 'analytics', array($row['promotion_id'])); ?>"><i class="far fa-file-alt"></i></a></li>
												</ul>
											</td>
										</tr>
												<?php
											}
										} else { ?>
										<tr>
											<td colspan="8">
												<?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false); ?>
											</td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
            <div class="row">
                <div class="col-lg-12 col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title "><?php echo Labels::getLabel('LBL_Transaction_History', $siteLangId);?></h5>
                            <?php if (count($transactions) > 0) { ?>
                            <div class="action">
                                <a href="<?php echo UrlHelper::generateUrl('Account', 'credits');?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId);?></a>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="card-body ">
                            <div class="scroll scroll-x js-scrollable table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr class="">
                                            <th><?php echo Labels::getLabel('LBL_Txn._Id', $siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Date', $siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Credit', $siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Debit', $siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Balance', $siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Comments', $siteLangId);?></th>
                                            <th><?php echo Labels::getLabel('LBL_Status', $siteLangId);?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($transactions) > 0) {
                                            foreach ($transactions as $row) { ?>
                                        <tr>
                                            <td>
                                                <div class="txn__id">
                                                    <?php echo Labels::getLabel('Lbl_Txn._Id', $siteLangId)?>: <?php echo Transactions::formatTransactionNumber($row['utxn_id']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="txn__date">
                                                    <?php echo FatDate::format($row['utxn_date']);?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="txn__credit">
                                                    <?php echo CommonHelper::displayMoneyFormat($row['utxn_credit']);?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="txn__debit">
                                                    <?php echo CommonHelper::displayMoneyFormat($row['utxn_debit']);?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="txn__balance">
                                                    <?php echo CommonHelper::displayMoneyFormat($row['balance']);?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="txn__comments">
                                                    <?php echo $row['utxn_comments'];?>
                                                </div>
                                            </td>
                                            <td>
                                            <span class="label label-inline <?php echo $txnStatusClassArr[$row['utxn_status']];?>"> <?php echo $txnStatusArr[$row['utxn_status']];?> </span>
                                            </td>
                                        </tr>
                                            <?php }
                                        } else { ?>
                                        <tr>
                                            <td colspan="7">
                                                <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId), false); ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php }?>
        </div>
    </div>
</main>
<script>
    redirectToPromotions = function(url) {
        var input = '<input type="hidden" name="active_promotion" value="' + 1 + '">';
        $('<form action="' + url + '" method="POST">' + input + '</form>').appendTo($(document.body)).submit();
    };
</script>
