<?php

class OrderCancellationRequestsController extends AdminBaseController
{

    public function __construct($action)
    {
        $ajaxCallArray = array();
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewOrderCancellationRequests($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditOrderCancellationRequests($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        if(!FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) {
            FatUtility::exitWithErrorCode(404);
        }
        
        $this->objPrivilege->canViewOrderCancellationRequests();
        $frmSearch = $this->getOrderCancellationRequestSearchForm($this->adminLangId, applicationConstants::ORDER_TYPE_SALE);
        $data = FatApp::getPostedData();
        if ($data) {
            $data['ocrequest_id'] = FatUtility::int($data['id']);
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->set('isRentalOrder', false);
        $this->_template->render();
    }

    public function rental()
    {
        $this->objPrivilege->canViewOrderCancellationRequests();
        $frmSearch = $this->getOrderCancellationRequestSearchForm($this->adminLangId, applicationConstants::ORDER_TYPE_RENT);
        $data = FatApp::getPostedData();
        if ($data) {
            $data['ocrequest_id'] = FatUtility::int($data['id']);
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->set('isRentalOrder', true);
        $this->_template->render(true, true, 'order-cancellation-requests/index.php');
    }

    public function search()
    {
        $this->objPrivilege->canViewOrderCancellationRequests();
        $orderFor = FatApp::getPostedData('order_product_for', FatUtility::VAR_INT, applicationConstants::ORDER_TYPE_SALE);
        $frmSearch = $this->getOrderCancellationRequestSearchForm($this->adminLangId, $orderFor);
        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'IFNULL(sum(opcharge_amount), 0) as shipping_charges'));
        $ocSrch->addCondition('opcharge_type', '=', OrderProduct::CHARGE_TYPE_SHIPPING);
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();
        
        
        $srch = new OrderCancelRequestSearch($this->adminLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderSellerUser();
        $srch->joinOrderProductStatus();
        $srch->joinOrderCancelReasons();
        $srch->addOrderProductCharges();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('ocrequest_date', 'DESC');
        $srch->addCondition('opd_sold_or_rented', '=', $orderFor);
        $srch->addMultipleFields(
                array(
                    'ocrequest_id', 'ocrequest_message', 'ocrequest_date', 'ocrequest_status',
                    'buyer.user_name as buyer_name', 'buyer_cred.credential_username as buyer_username', 'buyer.user_id as buyer_user_id',
                    'buyer_cred.credential_email as buyer_email', 'buyer.user_dial_code', 'buyer.user_phone as buyer_phone',
                    'seller.user_name as seller_name', 'seller_cred.credential_username as seller_username', 'seller.user_id as seller_user_id', 'seller_cred.credential_email as seller_email', 'seller.user_dial_code', 'seller.user_phone as seller_phone', 'op_invoice_number',
                    'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name',
                    'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'op_qty', 'opd_rental_start_date',
                    'op_unit_price', 'order_tax_charged', 'op_rounding_off', 'ocrequest_refund_amount', 'opd_sold_or_rented', 'opd_rental_security', 'ocrequest_is_penalty_applicable', 'ocrequest_hours_before_rental', 'opcc.*'
                )
        );

        $keyword = trim(FatApp::getPostedData('keyword', null, ''));
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op_invoice_number', '=', $keyword);
            $cnd->attachCondition('op_order_id', '=', $keyword);
            $cnd->attachCondition('ocrequest_message', 'LIKE', "%" . $keyword . "%");
        }

        if (isset($post['ocrequest_status']) && $post['ocrequest_status'] != '') {
            $ocrequest_status = FatUtility::int($post['ocrequest_status']);
            $srch->addCondition('ocrequest_status', '=', $ocrequest_status);
        }

        if (isset($post['op_status_id']) && $post['op_status_id'] != '') {
            $op_status_id = FatUtility::int($post['op_status_id']);
            $srch->addCondition('op_status_id', '=', $op_status_id);
        }
        if (isset($post['ocrequest_id']) && $post['ocrequest_id'] > 0) {
            $srch->addCondition('ocrequest_id', '=', $post['ocrequest_id']);
        }

        if (isset($post['ocrequest_ocreason_id']) && $post['ocrequest_ocreason_id'] != '') {
            $ocrequest_ocreason_id = FatUtility::int($post['ocrequest_ocreason_id']);
            $srch->addCondition('ocrequest_ocreason_id', '=', $ocrequest_ocreason_id);
        }

        if (isset($post['buyer']) && $post['buyer'] != '') {
            $buyer = trim($post['buyer']);
            $cnd = $srch->addCondition('buyer.user_name', 'LIKE', "%" . $buyer . "%");
            $cnd->attachCondition('buyer_cred.credential_username', 'LIKE', "%" . $buyer . "%");
            $cnd->attachCondition('buyer_cred.credential_email', 'LIKE', "%" . $buyer . "%");
            $cnd->attachCondition('buyer.user_phone', 'LIKE', "%" . $buyer . "%");
        }

        if (isset($post['seller']) && $post['seller'] != '') {
            $seller = trim($post['seller']);
            $cnd = $srch->addCondition('seller.user_name', '=', $seller);
            $cnd->attachCondition('seller_cred.credential_username', '=', $seller);
            $cnd->attachCondition('seller_cred.credential_email', '=', $seller);
            $cnd->attachCondition('seller.user_phone', '=', $seller);
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $rs = $srch->getResultSet();
        $arrListing = FatApp::getDb()->fetchAll($rs);

        $this->set("arrListing", $arrListing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('requestStatusArr', OrderCancelRequest::getRequestStatusArr($this->adminLangId));
        $this->set('statusClassArr', OrderCancelRequest::getStatusClassArr());
        $this->set('orderType', $orderFor);
        $this->_template->render(false, false);
    }

    public function viewRequest($id) 
    {
        $this->objPrivilege->canViewOrderCancellationRequests();

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'IFNULL(sum(opcharge_amount), 0) as shipping_charges'));
        $ocSrch->addCondition('opcharge_type', '=', OrderProduct::CHARGE_TYPE_SHIPPING);
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();
        
        
        $srch = new OrderCancelRequestSearch($this->adminLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderSellerUser();
        $srch->joinOrderProductStatus();
        $srch->joinOrderCancelReasons();
        $srch->addOrderProductCharges();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->addOrder('ocrequest_date', 'DESC');
        $srch->addCondition('ocrequest_id', '=', $id);
        $srch->addMultipleFields(
                array(
                    'ocrequest_id', 'ocrequest_message', 'ocrequest_date', 'ocrequest_status',
                    'buyer.user_name as buyer_name', 'buyer_cred.credential_username as buyer_username', 'buyer.user_id as buyer_user_id',
                    'buyer_cred.credential_email as buyer_email', 'buyer.user_dial_code', 'buyer.user_phone as buyer_phone',
                    'seller.user_name as seller_name', 'seller.user_id as seller_user_id', 'seller_cred.credential_username as seller_username', 'seller_cred.credential_email as seller_email', 'seller.user_dial_code', 'seller.user_phone as seller_phone', 'op_invoice_number',
                    'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name',
                    'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'op_qty', 'opd_rental_start_date',
                    'op_unit_price', 'order_tax_charged', 'op_rounding_off', 'ocrequest_refund_amount', 'opd_sold_or_rented', 'opd_rental_security', 'ocrequest_is_penalty_applicable', 'ocrequest_hours_before_rental', 'opcc.*'
                )
        );

        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs);
        $this->set('requestStatusArr', OrderCancelRequest::getRequestStatusArr($this->adminLangId));
        $this->set('statusClassArr', OrderCancelRequest::getStatusClassArr());
        $this->set("data", $data);
        $this->_template->render(false, false);

    }

    public function updateStatusForm($ocrequest_id)
    {
        $srch = new OrderCancelRequestSearch();
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->addCondition('ocrequest_id', '=', $ocrequest_id);
        $srch->joinOrderProductChargesByType(OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addMultipleFields(array('order_reward_point_used', 'order_pmethod_id', 'opcharge_amount', 'order_reward_point_value'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $orderRewardUsed = 0;
        if (!empty($row) && $row['order_reward_point_used'] > 0) {
            //$orderRewardUsed = $row['order_reward_point_used'];
            $orderRewardUsed = -1 * ($row['order_reward_point_used'] / $row['order_reward_point_value']) * $row['opcharge_amount'];
        }

        $canRefundToCard = false;
        $pluginKey = Plugin::getAttributesById($row['order_pmethod_id'], 'plugin_code');
        $paymentMethodObj = new PaymentMethods();
        if (true === $paymentMethodObj->canRefundToCard($pluginKey, $this->adminLangId)) {
            $canRefundToCard = true;
        }

        $this->set('orderRewardUsed', $orderRewardUsed);
        $this->objPrivilege->canEditOrderCancellationRequests();
        $this->set('frm', $this->getUpdateStatusForm($ocrequest_id, $this->adminLangId, $canRefundToCard));
        $this->_template->render(false, false);
    }

    public function setupUpdateStatus()
    {
        $this->objPrivilege->canEditOrderCancellationRequests();

        $ocrequest_id = FatApp::getPostedData('ocrequest_id', FatUtility::VAR_INT, 0);
        $frm = $this->getUpdateStatusForm($ocrequest_id, $this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false == $post) {
            Message::addErrorMessage($frm->getValidationErrors());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new OrderCancelRequestSearch($this->adminLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->addCondition('ocrequest_id', '=', $ocrequest_id);
        $srch->addCondition('ocrequest_status', '=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('op_id', 'ocrequest_id', 'ocrequest_status', 'ocrequest_op_id', 'o.order_language_id', 'op_status_id', 'order_pmethod_id', 'opd.*', 'ocrequest_refund_amount', 'opd_rental_security', 'ocrequest_is_penalty_applicable', 'op_selprod_user_id', 'op_commission_percentage', 'op_qty'));

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_or_Status_is_already_Approved_or_Declined!', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        $msgString = Labels::getLabel('LBL_Cancellation_request_has_been_{updatedStatus}_successfully.', $this->adminLangId);
        switch ($post['ocrequest_status']) {
            case OrderCancelRequest::CANCELLATION_REQUEST_STATUS_APPROVED:
                $notAllowedStatusChangeArr = array_merge(
                        unserialize(FatApp::getConfig("CONF_PROCESSING_ORDER_STATUS")), unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")), (array) FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")
                );
                $status = Orders::getOrderStatusArr($this->adminLangId);
                if (in_array($row['op_status_id'], $notAllowedStatusChangeArr)) {
                    Message::addErrorMessage(Labels::getLabel(str_replace('{currentStatus}', $status[$row['op_status_id']], 'LBL_This_order_is_{currentStatus}_now,_so_not_eligible_for_cancellation'), $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }

                $transferTo = FatApp::getPostedData('ocrequest_refund_in_wallet', FatUtility::VAR_INT, 0);
                $dataToUpdate = array('ocrequest_status' => OrderCancelRequest::CANCELLATION_REQUEST_STATUS_APPROVED, 'ocrequest_refund_in_wallet' => $transferTo, 'ocrequest_admin_comment' => $post['ocrequest_admin_comment']);
                $successMsgString = str_replace(strToLower('{updatedStatus}'), OrderCancelRequest::getRequestStatusArr($this->adminLangId)[OrderCancelRequest::CANCELLATION_REQUEST_STATUS_APPROVED], $msgString);
                $oObj = new Orders();
                if (true == $oObj->addChildProductOrderHistory($row['ocrequest_op_id'], 0, $row['order_language_id'], FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), Labels::getLabel('MSG_Cancellation_Request_Approved', $row['order_language_id']), true, '', 0, $transferTo)) {
                    if ((PaymentMethods::MOVE_TO_CUSTOMER_CARD == $transferTo)) {
                        $pluginKey = Plugin::getAttributesById($row['order_pmethod_id'], 'plugin_code');

                        $paymentMethodObj = new PaymentMethods();
                        if (true === $paymentMethodObj->canRefundToCard($pluginKey, $row['order_language_id'])) {
                            if (false == $paymentMethodObj->initiateRefund($row, PaymentMethods::REFUND_TYPE_CANCEL)) {
                                $db->rollbackTransaction();
                                FatUtility::dieJsonError($paymentMethodObj->getError());
                            }
                            $resp = $paymentMethodObj->getResponse();
                            if (empty($resp)) {
                                $db->rollbackTransaction();
                                FatUtility::dieJsonError(Labels::getLabel('LBL_UNABLE_TO_PLACE_GATEWAY_REFUND_REQUEST', $row['order_language_id']));
                            }
                            $dataToUpdate['ocrequest_payment_gateway_req_id'] = $resp->id;

                            // Debit from wallet if plugin/payment method support's direct payment to card.
                            if (!empty($resp->id)) {
                                $childOrderInfo = $oObj->getOrderProductsByOpId($row['ocrequest_op_id'], $this->adminLangId);
                                $txnAmount = $paymentMethodObj->getTxnAmount();
                                $refundableAmountForSeller = 0;
                                if ($row['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && $row['ocrequest_is_penalty_applicable'] > 0 /* && FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0) */) {
                                    $updatesTxnAmont = $txnAmount - ($row['opd_rental_security'] * $row['op_qty']);
                                    $txnAmount = (($updatesTxnAmont) * $row['ocrequest_refund_amount'] / 100);
                                    $refundableAmountForSeller = $updatesTxnAmont - $txnAmount;
                                    $txnAmount = $txnAmount + ($row['opd_rental_security'] * $row['op_qty']);
                                }
                                $comments = Labels::getLabel('LBL_TRANSFERED_TO_YOUR_CARD._INVOICE_#{invoice-no}', $this->adminLangId);
                                $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $childOrderInfo['op_invoice_number']]);
                                Transactions::debitWallet($childOrderInfo['order_user_id'], Transactions::TYPE_ORDER_REFUND, $txnAmount, $this->adminLangId, $comments, $row['ocrequest_op_id'], $resp->id);
                                /* CREDIT REFUNDED PENALTY AMOUNT TO SELLER AFTER ORDER CANCEL */
                                if ($refundableAmountForSeller > 0) {
                                    $commissionAmount = $refundableAmountForSeller * $row['op_commission_percentage'] / 100;
                                    $finalAmount = $refundableAmountForSeller - $commissionAmount;
                                    $comments = str_replace('{invoice}', $childOrderInfo['op_invoice_number'], Labels::getLabel('LBL_Credited_Order_Cancel_Penalty_For_{invoice}', $this->adminLangId));
                                    Transactions::creditWallet($childOrderInfo['op_selprod_user_id'], Transactions::TYPE_ORDER_PENALTY_REFUND_FOR_SELLER, $finalAmount, $this->adminLangId, $comments, $row['ocrequest_op_id']);
                                }
                                /* ] */
                            }
                        }
                    }



                    if ($row['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT && $row['opd_product_type'] == SellerProduct::PRODUCT_TYPE_PRODUCT) {
                        $addonProductIds = Orders::getAddonsIdsByProduct($row['ocrequest_op_id'], false);
                        if (!empty($addonProductIds)) {
                            foreach ($addonProductIds as $addonRow) {
                                $opId = $addonRow['op_id'];
                                /* $transferTo = $addonRow['opd_is_eligible_cancel']; */
                                if ($oObj->addChildProductOrderHistory($opId,0, $row['order_language_id'], FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), Labels::getLabel('MSG_Cancellation_Request_Approved', $row['order_language_id']), true, '', 0, true)) {
                                    /* if ((PaymentMethods::MOVE_TO_CUSTOMER_CARD == $transferTo)) {
                                        $childRow = $row;
                                        $childRow['op_id'] = $opId;
                                        $pluginKey = Plugin::getAttributesById($row['order_pmethod_id'], 'plugin_code');
                                        $paymentMethodObj = new PaymentMethods();
                                        if (true === $paymentMethodObj->canRefundToCard($pluginKey, $row['order_language_id'])) {
                                            if (false == $paymentMethodObj->initiateRefund($childRow, PaymentMethods::REFUND_TYPE_CANCEL)) {
                                                $db->rollbackTransaction();
                                                FatUtility::dieJsonError($paymentMethodObj->getError());
                                            }
                                            $resp = $paymentMethodObj->getResponse();
                                            if (empty($resp)) {
                                                $db->rollbackTransaction();
                                                FatUtility::dieJsonError(Labels::getLabel('LBL_UNABLE_TO_PLACE_GATEWAY_REFUND_REQUEST', $row['order_language_id']));
                                            }
                                            $dataToUpdate['ocrequest_payment_gateway_req_id'] = $resp->id;
                                            // Debit from wallet if plugin/payment method support's direct payment to card.
                                            if (!empty($resp->id)) {
                                                $childOrderInfo = $oObj->getOrderProductsByOpId($childRow['op_id'], $this->adminLangId);
                                                $txnAmount = $paymentMethodObj->getTxnAmount();
                                                $comments = Labels::getLabel('LBL_TRANSFERED_TO_YOUR_CARD._INVOICE_#{invoice-no}', $this->adminLangId);
                                                $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $childOrderInfo['op_invoice_number']]);
                                                Transactions::debitWallet($childOrderInfo['order_user_id'], Transactions::TYPE_ORDER_REFUND, $txnAmount, $this->adminLangId, $comments, $row['ocrequest_op_id'], $resp->id);
                                            }
                                        }
                                    } */
                                }
                            }
                        }
                    }
                }
                break;
            case OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED:
                $successMsgString = str_replace(strToLower('{updatedStatus}'), OrderCancelRequest::getRequestStatusArr($this->adminLangId)[OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED], $msgString);
                $dataToUpdate = array('ocrequest_status' => OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED);
                break;
            case OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING:
                $successMsgString = str_replace(strToLower('{updatedStatus}'), OrderCancelRequest::getRequestStatusArr($this->adminLangId)[OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING], $msgString);
                $dataToUpdate = array('ocrequest_status' => OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING);
                break;
        }
        $whereArr = array('smt' => 'ocrequest_id = ?', 'vals' => array($row['ocrequest_id']));
        $db = FatApp::getDb();
        if (!empty($dataToUpdate)) {
            if (!$db->updateFromArray(OrderCancelRequest::DB_TBL, $dataToUpdate, $whereArr)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($db->getError());
                CommonHelper::redirectUserReferer();
            }
        }
        $emailObj = new EmailHandler();
        if (!$emailObj->sendOrderCancellationRequestUpdateNotification($row['ocrequest_id'], $this->adminLangId)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Email_Sending_Error', $this->adminLangId) . " " . $emailObj->getError());
            CommonHelper::redirectUserReferer();
        }
        $db->commitTransaction();
        FatUtility::dieJsonSuccess($successMsgString);
    }

    private function getOrderCancellationRequestSearchForm($langId, $orderType = applicationConstants::ORDER_TYPE_SALE)
    {
        $frm = new Form('frmRequestSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));

        $frm->addSelectBox(Labels::getLabel('LBL_Request_Status', $this->adminLangId), 'ocrequest_status', OrderCancelRequest::getRequestStatusArr($langId), '', array(), 'All Request Status');

        $frm->addSelectBox(Labels::getLabel('LBL_Order_Payment_Status', $this->adminLangId), 'op_status_id', Orders::getOrderProductStatusArr($langId), '', array(), 'All Order Payment Status');
        $frm->addSelectBox(Labels::getLabel('LBL_Cancel_Reason', $this->adminLangId), 'ocrequest_ocreason_id', OrderCancelReason::getOrderCancelReasonArr($langId), '', array(), 'All Order Cancel Reason');
        $frm->addTextBox(Labels::getLabel('LBL_Buyer_Details', $this->adminLangId), 'buyer');
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Details', $this->adminLangId), 'seller');
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly'));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly'));

        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'ocrequest_id', 0);
        $frm->addHiddenField('', 'order_product_for', $orderType);
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getUpdateStatusForm($ocrequest_id, $langId, $canRefundToCard = false)
    {
        $statusTypeArr = OrderCancelRequest::getRequestStatusArr($langId);
        unset($statusTypeArr[OrderCancelRequest::CANCELLATION_REQUEST_STATUS_APPROVED_BY_SELLER]);
    
        $frm = new Form('frmUpdateStatus');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'ocrequest_status', $statusTypeArr, '', array(), '');
        // $frm->addCheckBox(Labels::getLabel('LBL_Transfer_Refund_to_Wallet', $this->adminLangId), 'ocrequest_refund_in_wallet', 1, array('checked' => 'checked'), false, 0);
        $moveRefundLocationArr = PaymentMethods::moveRefundLocationsArr($this->adminLangId);
        if (false == $canRefundToCard) {
            unset($moveRefundLocationArr[PaymentMethods::MOVE_TO_CUSTOMER_CARD]);
        } else {
            unset($moveRefundLocationArr[PaymentMethods::MOVE_TO_CUSTOMER_WALLET]);
        }

        // $frm->addRadioButtons(Labels::getLabel('LBL_TRANSFER_REFUND', $this->adminLangId), 'ocrequest_refund_in_wallet', $moveRefundLocationArr, PaymentMethods::MOVE_TO_ADMIN_WALLET, array('class' => 'list-inline'));
        $frm->addTextarea(Labels::getLabel('LBL_Comment', $this->adminLangId), 'ocrequest_admin_comment');
        $frm->addHiddenField('', 'ocrequest_id', $ocrequest_id);
        $frm->addHiddenField('', 'ocrequest_refund_in_wallet');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

}
