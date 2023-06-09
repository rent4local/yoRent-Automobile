<?php

defined('SYSTEM_INIT') or die('Invalid Usage.');
if ($reportType == applicationConstants::PRODUCT_FOR_RENT) {
    $arrFlds = array(
        'shop_name' => Labels::getLabel('LBL_Name', $adminLangId),
        'owner_name' => Labels::getLabel('LBL_Owner', $adminLangId),
        'totProducts' => Labels::getLabel('LBL_Items', $adminLangId),
        'totRentedQty' => Labels::getLabel('LBL_Rented_Qty', $adminLangId),
        'refundedQty' => Labels::getLabel('LBL_Refunded_QTY', $adminLangId),
        'cancelledOrdersQty' => Labels::getLabel('LBL_Cancelled_Order_QTY', $adminLangId),
        'rentTotal' => Labels::getLabel('LBL_Rental_Amount', $adminLangId),
        'totalFavorites' => Labels::getLabel('LBL_Favorites', $adminLangId),
        'totalRefundedAmount' => Labels::getLabel('LBL_Refunded_Amount', $adminLangId),
        'cancelledOrderAmt' => Labels::getLabel('LBL_Cancelled_Order_Amount', $adminLangId),
        'rental_commission' => Labels::getLabel('LBL_Site_Commission', $adminLangId),
        'totReviews' => Labels::getLabel('LBL_Reviews', $adminLangId),
        'totRating' => Labels::getLabel('LBL_Rating', $adminLangId),
    );
} else {
    $arrFlds = array(
        'shop_name' => Labels::getLabel('LBL_Name', $adminLangId),
        'owner_name' => Labels::getLabel('LBL_Owner', $adminLangId),
        'totProducts' => Labels::getLabel('LBL_Items', $adminLangId),
        'totSoldQty' => Labels::getLabel('LBL_Sold_Qty', $adminLangId),
		'refundedQty' => Labels::getLabel('LBL_Refunded_QTY', $adminLangId),
		'cancelledOrdersQty' => Labels::getLabel('LBL_Cancelled_Order_QTY', $adminLangId),
        'saleTotal' => Labels::getLabel('LBL_Sales', $adminLangId),
        'totalFavorites' => Labels::getLabel('LBL_Favorites', $adminLangId),
		'totalRefundedAmount' => Labels::getLabel('LBL_Refunded_Amount', $adminLangId),
        'cancelledOrderAmt' => Labels::getLabel('LBL_Cancelled_Order_Amount', $adminLangId),
        'sale_commission' => Labels::getLabel('LBL_Site_Commission', $adminLangId),
        'totReviews' => Labels::getLabel('LBL_Reviews', $adminLangId),
        'totRating' => Labels::getLabel('LBL_Rating', $adminLangId),
    );
}



$tbl = new HtmlElement(
        'table', array('width' => '100%', 'class' => 'table table-responsive table--hovered')
);

$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arrFlds as $val) {
    $e = $th->appendElement('th', array(), $val, true);
}

$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arrFlds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;

            case 'shop_name':
                $shop = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $row['shop_id'] . ")'>" . $row['shop_name'] . "</a>";
                $shop .= '<br/>Created On: ' . FatDate::format($row['shop_created_on'], false, true, FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get()));

                $td->appendElement('plaintext', array(), $shop, true);
                break;

            case 'owner_name':
                /* $td->appendElement('plaintext', array(), $row['owner_name'] . '<br/>(' . $row['owner_email'] . ')', true); */
                $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['user_id'] . ')'), $row['owner_name'], true);
                $td->appendElement('plaintext', array(), '<br/>' . $row['owner_email'], true);
                break;

            case 'totProducts':
                $td->appendElement('plaintext', array(), $row['totProducts'], true);
                break;

            case 'totSoldQty':
                $td->appendElement('plaintext', array(), $row['totSoldQty'], true);
                break;

            case 'saleTotal':
            case 'rentTotal':
            case 'totalRefundedAmount':
            case 'cancelledOrderAmt':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                break;

            case 'commission':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row['commission'], true, true), true);
                break;
            case 'rental_commission':
            case 'sale_commission':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                break;

            case 'totalFavorites':
                $td->appendElement('plaintext', array(), $row['totalFavorites'], true);
                break;

            case 'totReviews':
                $td->appendElement('plaintext', array(), $row['totReviews'], true);
                break;

            case 'totRating':
                $rating = '<ul class="rating list-inline">';
                for ($j = 1; $j <= 5; $j++) {
                    $class = ($j <= round($row['totRating'])) ? "active" : "in-active";
                    $fillColor = ($j <= round($row['totRating'])) ? "#ff3a59" : "#474747";
                    $rating .= '<li class="' . $class . '">
                    <svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
                    <g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="' . $fillColor . '" /></g></svg>

                  </li>';
                }
                $rating .= '</ul>';
                $td->appendElement('plaintext', array(), $rating, true);
                break;

            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement(
            'td', array(
        'colspan' => count($arrFlds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId)
    );
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmShopsReportSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
