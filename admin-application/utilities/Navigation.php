<?php

class Navigation
{

    public static function setLeftNavigationVals($template)
    {
        $db = FatApp::getDb();
        $langId = CommonHelper::getLangId();
        $userObj = new User();

        /* seller approval requests */
        $supReqSrchObj = $userObj->getUserSupplierRequestsObj();
        $supReqSrchObj->addCondition('usuprequest_status', '=', 0);
        $supReqSrchObj->addMultipleFields(array('count(usuprequest_id) as countOfRec'));
        $supReqResult = $db->fetch($supReqSrchObj->getResultset());
        $supReqCount = FatUtility::int($supReqResult['countOfRec']);

        /* product catalog requests */
        $catReqSrchObj = $userObj->getUserCatalogRequestsObj();
        $catReqSrchObj->addCondition('scatrequest_status', '=', 0);
        $catReqSrchObj->addMultipleFields(array('count(scatrequest_id) as countOfRec'));
        $catReqResult = $db->fetch($catReqSrchObj->getResultset());
        $catReqCount = FatUtility::int($catReqResult['countOfRec']);

        /* Custom catalog requests */
        $custReqSrchObj = ProductRequest::getSearchObject(0, false, true);
        $custReqSrchObj->addCondition('preq_status', '=', ProductRequest::STATUS_PENDING);
        $custReqSrchObj->addMultipleFields(array('count(preq_id) as countOfRec'));
        $custProdReqResult = $db->fetch($custReqSrchObj->getResultset());
        $custProdReqCount = FatUtility::int($custProdReqResult['countOfRec']);

        /* Custom brand requests */
        $brandReqSrchObj = Brand::getSearchObject(0, true, false);
        $brandReqSrchObj->addCondition('brand_status', '=', Brand::BRAND_REQUEST_PENDING);
        $brandReqSrchObj->addMultipleFields(array('count(brand_id) as countOfRec'));
        $brandReqResult = $db->fetch($brandReqSrchObj->getResultset());
        $brandReqCount = FatUtility::int($brandReqResult['countOfRec']);

        /* Product category requests */
        $categoryReqSrchObj = ProductCategory::getSearchObject(false, 0, false, ProductCategory::REQUEST_PENDING);
        $categoryReqSrchObj->addOrder('m.prodcat_active', 'DESC');
        $categoryReqSrchObj->addMultipleFields(array('count(prodcat_id) as countOfRec'));
        $categoryReqResult = $db->fetch($categoryReqSrchObj->getResultset());
        $categoryReqCount = FatUtility::int($categoryReqResult['countOfRec']);

        /* withdrawal requests */
        $drReqSrchObj = new WithdrawalRequestsSearch();
        $drReqSrchObj->addCondition('withdrawal_status', '=', 0);
        $drReqSrchObj->addMultipleFields(array('count(withdrawal_id) as countOfRec'));
        $drReqResult = $db->fetch($drReqSrchObj->getResultset());
        $drReqCount = FatUtility::int($drReqResult['countOfRec']);

        /* order cancellation requests */
        $orderCancelReqSrchObj = new OrderCancelRequestSearch($langId);
        $orderCancelReqSrchObj->joinTable(OrderProductData::DB_TBL, 'INNER JOIN', 'ocrequest_op_id = opd_op_id', 'opd');
        $orderCancelReqSrchObj->addCondition('ocrequest_status', '=', 0);
        $orderCancelReqSrchObj->addMultipleFields(array('count(ocrequest_id) as countOfRec'));

        $saleCancelReqSrchObj = clone $orderCancelReqSrchObj;
        $saleCancelReqSrchObj->addCondition('opd_sold_or_rented', '=', applicationConstants::ORDER_TYPE_SALE);
        $orderCancelReqResult = $db->fetch($saleCancelReqSrchObj->getResultset());
        $orderCancelReqCount = FatUtility::int($orderCancelReqResult['countOfRec']);

        $rentalCancelReqSrchObj = clone $orderCancelReqSrchObj;
        $rentalCancelReqSrchObj->addCondition('opd_sold_or_rented', '=', applicationConstants::ORDER_TYPE_RENT);
        $rentalOrderCancelReqResult = $db->fetch($rentalCancelReqSrchObj->getResultset());
        $rentalOrderCancelReqCount = FatUtility::int($rentalOrderCancelReqResult['countOfRec']);
        /* order return/refund requests */

        $orderRetReqSrchObj = new OrderReturnRequestSearch();
        $orderRetReqSrchObj->joinTable(OrderProductData::DB_TBL, 'INNER JOIN', 'orrequest_op_id = opd_op_id', 'opd');
        $orderRetReqSrchObj->addCondition('orrequest_status', '=', 0);
        $orderRetReqSrchObj->addMultipleFields(array('count(orrequest_id) as countOfRec'));

        $saleOrderRetReqSrchObj = clone $orderRetReqSrchObj;
        $saleOrderRetReqSrchObj->addCondition('opd_sold_or_rented', '=', applicationConstants::ORDER_TYPE_SALE);
        $saleOrderRetReqResult = $db->fetch($saleOrderRetReqSrchObj->getResultset());
        $orderRetReqCount = FatUtility::int($saleOrderRetReqResult['countOfRec']);

        $rentalOrderRetReqSrchObj = clone $orderRetReqSrchObj;
        $rentalOrderRetReqSrchObj->addCondition('opd_sold_or_rented', '=', applicationConstants::ORDER_TYPE_RENT);
        $renralOrderRetReqResult = $db->fetch($rentalOrderRetReqSrchObj->getResultset());
        $rentalOrderRetReqCount = FatUtility::int($renralOrderRetReqResult['countOfRec']);

        /* blog contributions */
        $blogContrSrchObj = BlogContribution::getSearchObject();
        $blogContrSrchObj->addCondition('bcontributions_status', '=', 0);
        $blogContrSrchObj->addMultipleFields(array('count(bcontributions_id) as countOfRec'));
        $blogContrResult = $db->fetch($blogContrSrchObj->getResultset());
        $blogContrCount = FatUtility::int($blogContrResult['countOfRec']);

        /* blog comments */
        $blogCommentsSrchObj = BlogComment::getSearchObject();
        $blogCommentsSrchObj->addCondition('bpcomment_approved', '=', 0);
        $blogCommentsSrchObj->addMultipleFields(array('count(bpcomment_id) as countOfRec'));
        $blogCommentsResult = $db->fetch($blogCommentsSrchObj->getResultset());
        $blogCommentsCount = FatUtility::int($blogCommentsResult['countOfRec']);

        /* threshold level products */
        $selProdSrchObj = SellerProduct::getSearchObject($langId);

        $selProdSrchObj->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $selProdSrchObj->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . CommonHelper::getLangId(), 'p_l');
        $selProdSrchObj->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'cred.credential_user_id = selprod_user_id', 'cred');
        $selProdSrchObj->joinTable('tbl_email_archives', 'LEFT OUTER JOIN', 'arch.emailarchive_to_email = cred.credential_email', 'arch');
        $selProdSrchObj->addDirectCondition('selprod_stock <= selprod_threshold_stock_level');
        $selProdSrchObj->addDirectCondition('selprod_track_inventory = ' . Product::INVENTORY_TRACK);

        $selProdSrchObj->addCondition('emailarchive_tpl_name', 'LIKE', 'threshold_notification_vendor_custom');
        $selProdSrchObj->addMultipleFields(array('count(selprod_id) as countOfRec'));
        $threshSelProdResult = $db->fetch($selProdSrchObj->getResultset());
        $threshSelProdCount = FatUtility::int($threshSelProdResult['countOfRec']);

        /* seller orders */
        $sellerOrderStatus = FatApp::getConfig('CONF_BADGE_COUNT_ORDER_STATUS', FatUtility::VAR_STRING, '0');
        if ($sellerOrderStatus && $sellerOrderStatusArr = (array) unserialize($sellerOrderStatus)) {
            $sellerOrderSrchObj = new OrderProductSearch($langId);
            $sellerOrderSrchObj->addStatusCondition($sellerOrderStatusArr);
            $sellerOrderSrchObj->addMultipleFields(array('count(op_id) as countOfRec'));

            $sellerSaleOrderSrchObj = clone $sellerOrderSrchObj;
            $sellerSaleOrderSrchObj->addCondition('opd_sold_or_rented', '=', applicationConstants::ORDER_TYPE_SALE);
            $sellerOrderResult = $db->fetch($sellerSaleOrderSrchObj->getResultset());
            $template->set('sellerOrderCount', FatUtility::int($sellerOrderResult['countOfRec']));

            $sellerRentOrderSrchObj = clone $sellerOrderSrchObj;
            $sellerRentOrderSrchObj->addCondition('opd_sold_or_rented', '=', applicationConstants::ORDER_TYPE_RENT);
            $sellerRentOrderResult = $db->fetch($sellerRentOrderSrchObj->getResultset());
            $template->set('sellerRentalOrderCount', FatUtility::int($sellerRentOrderResult['countOfRec']));
        }

        /* User GDPR requests */
        $gdprSrch = new UserGdprRequestSearch();
        $gdprSrch->addCondition('ureq_status', '=', UserGdprRequest::STATUS_PENDING);
        $gdprSrch->addCondition('ureq_deleted', '=', applicationConstants::NO);
        $gdprSrch->getResultSet();
        $gdprReqCount = $gdprSrch->recordCount();

        /* set counter variables [ */
        $template->set('brandReqCount', $brandReqCount);
        $template->set('categoryReqCount', $categoryReqCount);
        $template->set('custProdReqCount', $custProdReqCount);
        $template->set('supReqCount', $supReqCount);
        $template->set('catReqCount', $catReqCount);
        $template->set('drReqCount', $drReqCount);
        $template->set('orderCancelReqCount', $orderCancelReqCount);
        $template->set('rentalOrderCancelReqCount', $rentalOrderCancelReqCount);
        $template->set('orderRetReqCount', $orderRetReqCount);
        $template->set('rentalOrderRetReqCount', $rentalOrderRetReqCount);
        $template->set('blogContrCount', $blogContrCount);
        $template->set('blogCommentsCount', $blogCommentsCount);
        $template->set('threshSelProdCount', $threshSelProdCount);
        $template->set('gdprReqCount', $gdprReqCount);
        $template->set('adminLangId', CommonHelper::getLangId());
        /* ] */

        $template->set('objPrivilege', AdminPrivilege::getInstance());
        $template->set('adminName', AdminAuthentication::getLoggedAdminAttribute("admin_name"));
    }

}
