<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
	'orrequest_reference'=> Labels::getLabel('LBL_REFERENCE_NUMBER',$adminLangId),
	'buyer_detail'=>Labels::getLabel('LBL_Buyer_Details',$adminLangId),
	'vendor_detail'=>Labels::getLabel('LBL_Seller_Details',$adminLangId),
	'op_invoice_number'=>Labels::getLabel('LBL_Order_Id/invoice_Number',$adminLangId),
	'product'	=>	Labels::getLabel('LBL_Product',$adminLangId),
	'orrequest_qty'	=>	Labels::getLabel('LBL_Qty',$adminLangId),
	/* 'orrequest_type'	=>	Labels::getLabel('LBL_Request_Type',$adminLangId), */
	/* 'amount'=>Labels::getLabel('LBL_Amount',$adminLangId),	 */	
	'orrequest_date'=>Labels::getLabel('LBL_Date',$adminLangId),
	'orrequest_status'=>Labels::getLabel('LBL_Status',$adminLangId),
	'action' => '',
);
$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
	$e = $th->appendElement('th', array(), $val);
}
$sr_no = $page==1?0:$pageSize*($page-1);
foreach ($arrListing as $sn=>$row){
	$sr_no++;
	$tr = $tbl->appendElement('tr');
	
	foreach ($arr_flds as $key=>$val){
		$td = $tr->appendElement('td');
		switch ($key){
			case 'orrequest_id':
				$td->appendElement('plaintext', array(), $row['orrequest_reference'] /* CommonHelper::formatOrderReturnRequestNumber($row["orrequest_id"]) */ );
			break;
			case 'buyer_detail': 	
				$txt = '<strong>'.Labels::getLabel('LBL_N',$adminLangId).':  </strong>'. "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $row['buyer_user_id'] . ")'>" . $row['buyer_name'] . "</a>";
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_U',$adminLangId).':  </strong>'.$row['buyer_username'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_E',$adminLangId).':  </strong>'.$row['buyer_email'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_P',$adminLangId).':  </strong>'. $row['user_dial_code'] . ' ' . $row['buyer_phone'];
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'vendor_detail':
				$txt = '<strong>'.Labels::getLabel('LBL_N',$adminLangId).':  </strong>'. "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $row['seller_user_id'] . ")'>" . $row['seller_name'] . "</a>";
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_U',$adminLangId).':  </strong>'.$row['seller_username'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_E',$adminLangId).':  </strong>'.$row['seller_email'];
				$txt .= '<br/><strong>'.Labels::getLabel('LBL_P',$adminLangId).':  </strong>'.$row['user_dial_code'] . ' ' . $row['seller_phone'];
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'product':
				$txt = '';
				if( $row['op_selprod_title'] != '' ){
					$txt .= $row['op_selprod_title'].'<br/>'.'<small>'.$row['op_product_name'].'</small>';
				} else {
					$txt .= $row['op_product_name'];
				}
				if( $row['op_selprod_options'] != '' ){
					$txt .= '<br/>'.$row['op_selprod_options'];
				}
				if( $row['op_brand_name'] != '' ){
					$txt .= '<br/><strong>'.Labels::getLabel('LBL_Brand',$adminLangId).':  </strong> '.$row['op_brand_name'];
				}
				
				if( $row['op_shop_name'] != '' ){
					$txt .= '<br/><strong>'.Labels::getLabel('LBL_Shop',$adminLangId).':  </strong> '."<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $row['op_shop_id'] . ")'>" . $row['op_shop_name'] . "</a>";
				}
				
				$td->appendElement('plaintext', array(), $txt, true);
			break;
			case 'orrequest_type':
				$td->appendElement('plaintext', array(), isset($requestTypeArr[$row[$key]])?$requestTypeArr[$row[$key]]:'' , true);
			break;
			case 'orrequest_date':
				$td->appendElement('plaintext', array(), FatDate::format( $row[$key], true ), true);
			break;
			case 'amount':
				$amt = '';
				$priceTotalPerItem = CommonHelper::orderProductAmount($row,'netamount',true);
				$price = 0;								
				if($row['orrequest_status'] != OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED){
					if(FatApp::getConfig('CONF_RETURN_SHIPPING_CHARGES_TO_CUSTOMER',FatUtility::VAR_INT,0)){
						$shipCharges = isset($row['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount'])?$row['charges'][OrderProduct::CHARGE_TYPE_SHIPPING][OrderProduct::DB_TBL_CHARGES_PREFIX.'amount']:0;
						$unitShipCharges = round(($shipCharges / $row['op_qty']),2);
						$priceTotalPerItem = $priceTotalPerItem + $unitShipCharges;		
						$price = $priceTotalPerItem * $row['orrequest_qty'];
					}	
				}
				
				if(!$price){
					$price = $priceTotalPerItem * $row['orrequest_qty'];
					$price = $price + $row['op_refund_shipping'];
				}
				
				$amt = CommonHelper::displayMoneyFormat($price, true, true);				
				$td->appendElement('plaintext', array(), $amt, true);
			break;
			case 'orrequest_status':
				$td->appendElement('label', array('class'=>'label label--'.$requestTypeClassArr[$row[$key]].''), $requestStatusArr[$row[$key]]);
			break;
			case 'action':
				$td->appendElement('a', array('href'=>UrlHelper::generateUrl('OrderReturnRequests','view',array($row['orrequest_id'])),'class'=>'btn btn-clean btn-sm btn-icon','title'=>Labels::getLabel('LBL_View',$adminLangId)),"<i class='ion-eye'></i>", true);					
			break;
			default:
				$td->appendElement('plaintext', array(), $row[$key], true);
			break;
		}
	}
}
if (count($arrListing) == 0){
	$tbl->appendElement('tr')->appendElement('td', array('colspan'=>count($arr_flds)), Labels::getLabel('LBL_No_Records_Found',$adminLangId));
} 
echo $tbl->getHtml();
$postedData['page']=$page;
echo FatUtility::createHiddenFormFromData ( $postedData, array (
		'name' => 'frmOrderReturnRequestSearchPaging'
) );
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'pageSize'=>$pageSize,'recordCount'=>$recordCount,'adminLangId'=>$adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr,false);
?>