<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<section class="section">
	<div class="sectionhead">
		<h1><?php echo Labels::getLabel('LBL_User_Transactions',$adminLangId); ?></h1>
	</div>
	<div class="sectionbody space">  
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a class="active" href="javascript:void(0)" onclick="transactions(<?php echo $userId ?>);"><?php echo Labels::getLabel('LBL_Transactions',$adminLangId); ?></a></li>
				<li><a href="javascript:void(0)" onclick="addUserTransaction(<?php echo $userId ?>);"><?php echo Labels::getLabel('LBL_Add_New',$adminLangId); ?></a></li>				
			</ul>
			<div class="tabs_panel_wrap">			
				<div class="tabs_panel">
					<?php 
					$arr_flds = array(
						'utxn_id'=> Labels::getLabel('LBL_Transaction_Id',$adminLangId),
						'utxn_date'=>Labels::getLabel('LBL_Date',$adminLangId),
						'utxn_credit'=>Labels::getLabel('LBL_Credit',$adminLangId),						
						'utxn_debit' => Labels::getLabel('LBL_Debit',$adminLangId),
						'balance' => Labels::getLabel('LBL_Balance',$adminLangId),
						'utxn_comments' => Labels::getLabel('LBL_Description',$adminLangId),
						'utxn_status' => Labels::getLabel('LBL_Status',$adminLangId),
						);
					$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table-responsive fixed-layout'));
					$th = $tbl->appendElement('thead')->appendElement('tr');
					foreach ($arr_flds as $key=>$val) {					
						$e = $th->appendElement('th', array(), $val,true);
					}
					$sr_no = 0;
					foreach ($arr_listing as $sn=>$row){ 
						$sr_no++;
						$tr = $tbl->appendElement('tr');
						
						foreach ($arr_flds as $key=>$val){
							$td = $tr->appendElement('td');
							switch ($key){
								case 'utxn_id':
									$td->appendElement('plaintext', array(), Transactions::formatTransactionNumber($row[$key]) );
								break;
								case 'utxn_date':
									$td->appendElement('plaintext', array(),FatDate::format($row[$key]));
								break;
								case 'utxn_credit':
								case 'utxn_debit':
								case 'balance':
									$td->appendElement('plaintext', array(),CommonHelper::displayMoneyFormat($row[$key]));
								break;														
								case 'utxn_comments':	
									$transDesc = Transactions::formatTransactionComments($row[$key]);	
									$descTxt = "<span class='lessText'>" . CommonHelper::truncateCharacters($transDesc, 30, '', '', true) . "</span>";
									if (strlen($transDesc) > 30) {
										$descTxt .= "<span class='moreText hidden'>";
										$descTxt .= $transDesc ."</span>";
										$descTxt .= "</br><a href='javascript:void(0);' class='readMore'>" . Labels::getLabel('LBL_Read_More', $adminLangId) . "</a>";
									}						
									$td->appendElement('plaintext', array(), $descTxt, true);
								break;
								case 'utxn_status':								
									$td->appendElement('plaintext', array(), $statusArr[$row[$key]],true);
								break;							
								default:
									$td->appendElement('plaintext', array(), $row[$key], true);
								break;
							}
						}
					}
					if (count($arr_listing) == 0){
						$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
					}
					echo $tbl->getHtml();
					$postedData['page'] = $page;
					echo FatUtility::createHiddenFormFromData ( $postedData, array (
							'name' => 'frmTransactionSearchPaging'
					) );
					$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'callBackJsFunc'=>'goToTransactionPage','adminLangId'=>$adminLangId);
					$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
					?>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
    var $linkMoreText = '<?php echo Labels::getLabel('Lbl_READ_MORE', $adminLangId); ?>';
    var $linkLessText = '<?php echo Labels::getLabel('Lbl_READ_LESS', $adminLangId); ?>';

	/* read more functionality [ */
	$(document).ready(function () {
		$('.readMore').click(function () {
			var $this = $(this);
			var $moreText = $this.siblings('.moreText');
			var $lessText = $this.siblings('.lessText');

			if ($this.hasClass('expanded')) {
				$moreText.hide();
				$lessText.fadeIn();
				$this.text($linkMoreText);
			} else {
				$lessText.hide();
				$moreText.fadeIn();
				$this.text($linkLessText);
				$('.content.fbminwidth').css('overflow-y', 'scroll');
				$('.content.fbminwidth').css('max-height', '831px');
			}
			$this.toggleClass('expanded');
		});
	});
	/* ] */
</script>
<style>
    .hidden {display: none;}
</style>