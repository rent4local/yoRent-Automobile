<?php

require_once CONF_INSTALLATION_PATH . 'library/APIs/twitteroauth-master/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

class BuyerController extends BuyerBaseController
{
    protected $error;

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $user = new User($userId);
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'B';

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinShippingCharges();
        $srch->joinSellerProductGroup();
        $srch->addCountsOfOrderedProducts();
        $srch->joinSellerProductSpecifics();
        $srch->joinShopSpecifics();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        //$srch->addBuyerOrdersCounts(date('Y-m-d',strtotime("-1 days")),date('Y-m-d'),'yesterdayOrder');
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS", null, '')));
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addOrder("op_id", "DESC");

        $srch->setPageNumber(1);
        $srch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);

        $srch->addMultipleFields(
            array(
                'order_id', 'order_user_id', 'op_selprod_id', 'op_is_batch', 'selprod_product_id',
                'order_date_added', 'order_net_amount', 'op_invoice_number',
                'totCombinedOrders as totOrders', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name',
                'op_product_type', 'op_status_id', 'op_id', 'op_qty', 'op_selprod_options',
                'op_brand_name', 'op_shop_name', 'op_other_charges', 'op_unit_price',
                'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name',
                'orderstatus_color_class', 'order_pmethod_id', 'opshipping_fulfillment_type',
                'op_rounding_off', 'opd.*'
            )
        );
        $srch->addFld(
            array(
                'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age',
                'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age'
            )
        );

        $rentalOdrSrch = clone $srch;
        $addonSrch = clone $rentalOdrSrch;
        $addonSrch->addCondition('opd_product_type', '=', 'mysql_func_'. SellerProduct::PRODUCT_TYPE_ADDON, 'AND', true);

        $rentalOdrSrch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. applicationConstants::ORDER_TYPE_RENT, 'AND', true);
        $rentalOdrSrch->addCondition('opd_product_type', '=', 'mysql_func_'. SellerProduct::PRODUCT_TYPE_PRODUCT, 'AND', true);

        $rentRs = $rentalOdrSrch->getResultSet();
        $renralOrders = FatApp::getDb()->fetchAll($rentRs);

        $srch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. applicationConstants::ORDER_TYPE_SALE, 'AND', true);
        $srch->addCondition('opd_product_type', '=', 'mysql_func_'. SellerProduct::PRODUCT_TYPE_PRODUCT, 'AND', true);
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        if (!empty($renralOrders)) {
            $opIds  = array_column($renralOrders, 'op_id');
            $addonSrch->addCondition('op_attached_op_id', 'IN', $opIds);
            $addonSrch->addMultipleFields(
                array(
                    'order_net_amount', 'op_id', 'op_qty', 'op_other_charges', 'op_unit_price', 'op_tax_collected_by_seller',
                    'op_selprod_user_id', 'opshipping_by_seller_user_id', 'opshipping_fulfillment_type', 'op_rounding_off', 'op_product_type', 'op_status_id', 'op_attached_op_id'
                )
            );
            $addonRs = $addonSrch->getResultSet();
            $addons = FatApp::getDb()->fetchAll($addonRs);
            $addonAmountArr = [];
            if (!empty($addons)) {
                foreach ($addons as $addon) {
                    $charges = $oObj->getOrderProductChargesArr($addon['op_id']);
                    $addon['charges'] = $charges;
                    $totalAmount = CommonHelper::orderProductAmount($addon, 'netamount');
                    if (isset($addonAmountArr[$addon['op_attached_op_id']])) {
                        $addonAmountArr[$addon['op_attached_op_id']] += $totalAmount;
                    } else {
                        $addonAmountArr[$addon['op_attached_op_id']] = $totalAmount;
                    }
                }
            }

            foreach ($renralOrders as &$order) {
                $charges = $oObj->getOrderProductChargesArr($order['op_id']);
                $order['charges'] = $charges;
                $order['addon_amount'] = (isset($addonAmountArr[$order['op_id']])) ? $addonAmountArr[$order['op_id']] : 0;
            }
        }

        /* Orders Counts [ */
        $orderSrch = new OrderProductSearch($this->siteLangId, true, true);
        $orderSrch->doNotCalculateRecords();
        $orderSrch->doNotLimitRecords();

        $completedOrderStatus = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS", FatUtility::VAR_STRING, ''));
        if (!empty($completedOrderStatus)) {
            $orderSrch->addCondition('op_status_id', 'NOT IN', $completedOrderStatus);
        }
        $orderSrch->addGroupBy('order_user_id');
        $orderSrch->addCondition('order_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $orderSrch->addMultipleFields(array('COUNT(o.order_id) as pendingOrderCount'));

        $orderRentSrch = clone $orderSrch;
        $orderRentSrch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. applicationConstants::ORDER_TYPE_RENT, 'AND', true);
        $orderRentSrch->addCondition('opd_product_type', '=', 'mysql_func_'. SellerProduct::PRODUCT_TYPE_PRODUCT, 'AND', true);

        $rentrs = $orderRentSrch->getResultSet();
        $rentalOrdersStats = FatApp::getDb()->fetch($rentrs);

        $orderSrch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. applicationConstants::ORDER_TYPE_SALE, 'AND', true);
        $orderSrch->addCondition('opd_product_type', '=', 'mysql_func_'. SellerProduct::PRODUCT_TYPE_PRODUCT, 'AND', true);
        $rs = $orderSrch->getResultSet();
        $ordersStats = FatApp::getDb()->fetch($rs);
        /* ] */

        /*
         * Return Request Listing
         */
        $srchReturnReq = $this->orderReturnRequestObj();
        $srchReturnReq->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $srchReturnReq->getResultSet();
        $returnRequests = FatApp::getDb()->fetchAll($rs);

        /*
         * Cancellation Request Listing
         */
        $canSrch = $this->orderCancellationRequestObj();
        $canSrch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $canSrch->getResultSet();
        $cancellationRequests = FatApp::getDb()->fetchAll($rs);

        /*
         * Offers Listing
         */
        $offers = DiscountCoupons::getUserCoupons(UserAuthentication::getLoggedUserId(), $this->siteLangId);

        $txnObj = new Transactions();
        $txnsSummary = $txnObj->getTransactionSummary($userId, date('Y-m-d'));

        $this->set('offers', $offers);
        $this->set('data', $user->getProfileData());
        $this->set('orders', $orders);
        $this->set('renralOrders', $renralOrders);
        $this->set('returnRequests', $returnRequests);
        $this->set('cancellationRequests', $cancellationRequests);
        $this->set('OrderReturnRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('OrderRetReqStatusClassArr', OrderReturnRequest::getRequestStatusClassArr());
        $this->set('OrderCancelRequestStatusArr', OrderCancelRequest::getRequestStatusArr($this->siteLangId));
        $this->set('cancelReqStatusClassArr', OrderCancelRequest::getStatusClassArr());


        $this->set('rentalOrdersCount', $rentalOdrSrch->recordCount());
        $this->set('pendingRentalOrderCount', isset($rentalOrdersStats['pendingOrderCount']) ? FatUtility::int($rentalOrdersStats['pendingOrderCount']) : 0);


        $this->set('ordersCount', $srch->recordCount());
        $this->set('pendingOrderCount', isset($ordersStats['pendingOrderCount']) ? FatUtility::int($ordersStats['pendingOrderCount']) : 0);

        $this->set('userBalance', User::getUserBalance($userId));
        $this->set('totalRewardPoints', UserRewardBreakup::rewardPointBalance($userId));
        $this->set('txnsSummary', $txnsSummary);
        $this->set('classArr', applicationConstants::getClassArr());
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true);
    }

    public function viewOrder($orderId, $opId = 0, $print = 0)
    {
        $print = ($print == 1) ? true : false;
        if (!$orderId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $opId = FatUtility::int($opId);
        if (0 < $opId) {
            $opOrderId = OrderProduct::getAttributesById($opId, 'op_order_id');
            if ($orderId != $opOrderId) {
                $message = Labels::getLabel('MSG_Invalid_Order', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                CommonHelper::redirectUserReferer();
            }
        }
        $primaryOrderDisplay = false;
        $orderObj = new Orders();

        $userId = UserAuthentication::getLoggedUserId();

        $orderDetail = $orderObj->getOrderById($orderId, $this->siteLangId);
        if (!$orderDetail || ($orderDetail && $orderDetail['order_user_id'] != $userId)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }
        $orderDetail['charges'] = $orderObj->getOrderProductChargesByOrderId($orderDetail['order_id']);
        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinLateChargesHistory();
        $srch->joinOrderProductShipment();
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->joinShippingCharges();
        $srch->joinAddress();
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('order_id', '=', $orderId);
        $srch->addOrder('opd_product_type', 'ASC');

        if (0 < $opId) {
            $srch->joinSellerProductSpecifics();
            $srch->joinShopSpecifics();
            if (true === MOBILE_APP_API_CALL) {
                $srch->joinTable(SelProdReview::DB_TBL, 'LEFT OUTER JOIN', 'o.order_id = spr.spreview_order_id and op.op_selprod_id = spr.spreview_selprod_id', 'spr');
                $srch->joinTable(SelProdRating::DB_TBL, 'LEFT OUTER JOIN', 'sprating.sprating_spreview_id = spr.spreview_id', 'sprating');
                $srch->addFld(array('*', 'IFNULL(ROUND(AVG(sprating_rating),2),0) as prod_rating'));
            }
            $addonProductIds = Orders::getAddonsIdsByProduct($opId);
            $addonProductIds = array_merge($addonProductIds, array($opId));
            //$addonProductIds = array($opId);
            $srch->addCondition('op_id', 'IN', $addonProductIds);
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
            $primaryOrderDisplay = true;
        }

        if (true === MOBILE_APP_API_CALL) {
            $srch->joinTable(
                OrderReturnRequest::DB_TBL,
                'LEFT OUTER JOIN',
                'orr.orrequest_op_id = op.op_id',
                'orr'
            );
            $srch->joinTable(
                OrderCancelRequest::DB_TBL,
                'LEFT OUTER JOIN',
                'ocr.ocrequest_op_id = op.op_id',
                'ocr'
            );
            $srch->addFld(array('*', 'IFNULL(orrequest_id, 0) as return_request', 'IFNULL(ocrequest_id, 0) as cancel_request'));
        }

        $rs = $srch->getResultSet();
        $childOrderDetail = FatApp::getDb()->fetchAll($rs, 'op_id');
        $isContainRentalProducts = false;
        $servicesArr = [];
        $servicesCartTotal = 0;
        $servicesNetTotal = 0;
        $servicesTaxTotal = 0;
        $servicesLateCharges = 0;
        $shopAgreementArr = [];
        $activeAction = 'orders';
        foreach ($childOrderDetail as $op_id => $val) {
            $childOrderDetail[$op_id]['charges'] = $orderDetail['charges'][$op_id];
            $opChargesLog = new OrderProductChargeLog($op_id);
            $taxOptions = $opChargesLog->getData($this->siteLangId);
            $childOrderDetail[$op_id]['taxOptions'] = $taxOptions;
            if ($val['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                $isContainRentalProducts = true;
                $activeAction = 'rentalOrders';
            }
            if ($val['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                $parentOpId = $val['op_attached_op_id'];
                $servicesArr[$parentOpId][$op_id] = $val;
                $servicesArr[$parentOpId][$op_id]['charges'] = $orderDetail['charges'][$op_id];
                $opChargesLog = new OrderProductChargeLog($op_id);
                $taxOptions = $opChargesLog->getData($this->siteLangId);
                $servicesArr[$parentOpId][$op_id]['taxOptions'] = $taxOptions;
                $servicesCartTotal += CommonHelper::orderProductAmount($val, 'CART_TOTAL');
                $servicesTaxTotal += CommonHelper::orderProductAmount($val, 'TAX');
                $servicesNetTotal += CommonHelper::orderProductAmount($val, 'netamount');
                $servicesLateCharges += $val['charge_total_amount'];
                unset($childOrderDetail[$op_id]);
            } else {
                $childOrderDetail[$op_id]['charges'] = $orderDetail['charges'][$op_id];
                $opChargesLog = new OrderProductChargeLog($op_id);
                $taxOptions = $opChargesLog->getData($this->siteLangId);
                $childOrderDetail[$op_id]['taxOptions'] = $taxOptions;
                $childOrderDetail[$op_id]['deliveredMarkedBy'] = OrderProduct::checkOrderDeliveredMarkedByBuyer($op_id, $val['order_user_id']);
                if ($val['opd_rental_agreement_afile_id'] > 0) {
                    $shopAgreementArr[$val['op_shop_id']] = ['agreementFileId' => $val['opd_rental_agreement_afile_id'], 'shopName' => $val['op_shop_name']];
                }
            }
        }

        $signatureData = array();
        if (FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1)) {
            $signatureData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $orderDetail['order_order_id'], 0, -1, true, 0, false);
        }
        $serviceTotalPriceArr = [
            'cart_total' => $servicesCartTotal,
            'tax_total' => $servicesTaxTotal,
            'net_total' => $servicesNetTotal,
            'late_charges_total' => $servicesLateCharges,
        ];
        $this->set('serviceTotalPriceArr', $serviceTotalPriceArr);

        if ($opId > 0) {
            $childOrderDetail = array_shift($childOrderDetail);
            /* [ DELETE TEMP UPLOADED FILES IF ANY */
            $criteria = [
                'afile_type' => AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE,
                'afile_record_id' => $opId,
            ];
            $whr = ['smt' => 'afile_type = ? and afile_record_id = ?', 'vals' => [AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $opId]];
            $this->removeTempFileByCriteria($criteria, $whr);
            /* ] */
        }

        if (empty($childOrderDetail) || 1 > count($childOrderDetail)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $address = $orderObj->getOrderAddresses($orderDetail['order_id']);
        $orderDetail['billingAddress'] = $address[Orders::BILLING_ADDRESS_TYPE];
        $orderDetail['shippingAddress'] = (!empty($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($orderDetail['order_id'], $opId);
        $orderDetail['pickupAddress'] = (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        if ($opId > 0) {
            $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id" => $childOrderDetail['op_id']), 0, true);
        } else {
            $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("order_id" => $orderDetail['order_id']), 0, true, true);
        }

        $childOrderProducts = $orderObj->getChildOrders(['order_id' => $orderDetail['order_id']]);
        $childOrderProdCount = count($childOrderProducts);
        if (1 > $opId || 1 == $childOrderProdCount) {
            $payments = $orderObj->getOrderPayments(array("order_id" => $orderDetail['order_id']));
            if (true === MOBILE_APP_API_CALL) {
                $payments = array_values($payments);
            }
            $orderDetail['payments'] = $payments;
        }

        $productType = !empty($childOrderDetail['selprod_product_id']) ? Product::getAttributesById($childOrderDetail['selprod_product_id'], 'product_type') : 0;
        $soldOrRented = '';
        $extendFromOpId = '';
        if ($primaryOrderDisplay) {
            $soldOrRented = $childOrderDetail['opd_sold_or_rented'];
            $extendFromOpId = $childOrderDetail['opd_extend_from_op_id'];
        }

        $oVfldsObj = $orderObj->getOrderVerificationDataSrchObj($orderId, true);
        if ($opId > 0) {
            $oVfldsObj->addCondition('optvf_op_id', '=', 'mysql_func_'. $opId, 'AND', true);
        }
        $oVfldsObj->doNotCalculateRecords();
        $oVfldsObj->doNotLimitRecords();
        $oVfldsObj->addMultipleFields(array('ovd_order_id', 'ovd_order_id', 'ovd_vflds_type', 'ovd_vflds_name', 'ovd_value', 'optvf_selprod_id', 'optvf_op_id', 'ovd_vfld_id'));
        $rs = $oVfldsObj->getResultSet();
        $verificationFldsData = FatApp::getDb()->fetchAll($rs);

        $frm = $this->getTransferBankForm($this->siteLangId, $orderId);
        $this->set('frm', $frm);
        $this->set('orderDetail', $orderDetail);
        $this->set('childOrderDetail', $childOrderDetail);
        $this->set('primaryOrder', $primaryOrderDisplay);
        $this->set('childOrderProdCount', $childOrderProdCount);
        $this->set('productType', $productType);
        $this->set('languages', Language::getAllNames());
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        $this->set('rentalTypeArr', applicationConstants::rentalTypeArr($this->siteLangId));
        $this->set('verificationFldsData', $verificationFldsData);
        $urlParts = array($orderId, $opId);
        $this->set('urlParts', $urlParts);
        $this->set('print', $print);
        $this->set('opId', $opId);
        $this->set('isContainRentalProducts', $isContainRentalProducts);
        $this->set('shopAgreementArr', $shopAgreementArr);
        $this->set('signatureData', $signatureData);

        if ($primaryOrderDisplay || $soldOrRented == applicationConstants::PRODUCT_FOR_RENT) {

            /* ---- check message thread --- */
            $thData = Thread::getMsgThreadByRecordId($opId, Thread::THREAD_TYPE_ORDER_PRODUCT);
            $thread_id = 0;
            $message_id = 0;
            if (!empty($thData)) {
                $thread_id = $thData['thread_id'];
                $message_id = $thData['message_id'];
            }
            $this->set('thread_id', $thread_id);
            $this->set('message_id', $message_id);
            /*====*/
        }

        /* [ ATTACHED SERVICES ] */
        $this->set('attachedServicesArr', $servicesArr);
        /* ] */

        $this->set('activeAction', $activeAction);
        if ($soldOrRented == applicationConstants::PRODUCT_FOR_RENT) {
            $processingOrderStatus = unserialize(FatApp::getConfig("CONF_DELIVERED_MARK_STATUS_FOR_BUYER"));
            $processingOrderStatus[] = FatApp::getConfig('CONF_DEFAULT_DEIVERED_ORDER_STATUS');
            $this->set('statusAddressData', $this->getDropOffAddressData($orderDetail['comments']));
            if (in_array($childOrderDetail['op_status_id'], $processingOrderStatus) && $childOrderDetail['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                $orderStatusFrm = $this->getOrderCommentsForm($childOrderDetail, $processingOrderStatus);
                $orderStatusFrm->fill($childOrderDetail);
                $this->set('orderStatusFrm', $orderStatusFrm);
                $address = new Address();
                $shopAddresses = $address->getPickupData(Address::TYPE_SHOP_PICKUP, $childOrderDetail['selprod_product_id'], 0, false);
                if (empty($shopAddresses)) {
                    $shopAddresses = $address->getData(Address::TYPE_SHOP_PICKUP, $childOrderDetail['op_shop_id'], 0, false);
                }
                $this->set('shopAddresses', $shopAddresses);
            }

            $extendChildOrderdata = OrderProductData::getOrderProductData($opId, true);
            $parentOrderId = '';
            if ($extendFromOpId > 0) {
                $parentOrderId = OrderProduct::getOrderIdByOprId($extendFromOpId);
            }
            $this->set('extendChildOrder', $extendChildOrderdata);
            $this->set('parentOrderId', $parentOrderId);
            $this->set('extendFromOpId', $extendFromOpId);

            $this->set('statusForReadyToReturn', OrderStatus::getStatusForMarkOrderReadyForReturn());
            $attachedFile = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $opId);
            $this->set('statusAttachedFiles', CommonHelper::groupAttachmentFilesData($attachedFile, 'afile_record_subid'));
            $this->_template->addJs(['buyer/page-js/view-order.js']);
            /* $this->_template->render(true, true, 'buyer/rent-view-order.php', false, true); */
            /* $this->_template->render(true, true, 'buyer/parent-order-view.php', false, true); */
        } else {
            /* if (!$primaryOrderDisplay) {
                $this->_template->render(true, true, 'buyer/parent-order-view.php', false, true);
            } else {
                $this->_template->render();
            } */
        }
        $orderStatusList = [['orderstatus_id' => 0, 'orderstatus_name' => Labels::getLabel('Lbl_Payment_Received', $this->siteLangId), 'priority' => '0']];
        if ($primaryOrderDisplay) {
            $orderStatusArr = (array) array_keys($orderDetail['comments']);
            $isRent = ($soldOrRented == applicationConstants::ORDER_TYPE_RENT) ? true : false;
            $orderStatusList = OrderStatus::orderStatusFlow($this->siteLangId, $orderDetail['order_payment_status'], $orderStatusArr, $isRent, $childOrderDetail['opshipping_fulfillment_type']);
            $this->set('currentOrderStatusPriority', FatUtility::int(OrderStatus::getAttributesById($childOrderDetail['op_status_id'], 'orderstatus_priority')));
        }
        $this->set('orderStatusList', $orderStatusList);
        $this->_template->render(true, true, 'buyer/parent-order-view.php', false, true);
    }

    private function getDropOffAddressData(array $orderStatusArr): array
    {
        if (empty($orderStatusArr)) {
            return [];
        }
        $addressDataArr = [];
        $rentalReturnStatus = FatApp::getConfig('CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END', FatUtility::VAR_INT, 17);
        $rentalReturnStatusArr = (isset($orderStatusArr[$rentalReturnStatus])) ? $orderStatusArr[$rentalReturnStatus] : [];
        if (empty($rentalReturnStatusArr)) {
            return [];
        }

        foreach ($rentalReturnStatusArr as $statusArr) {
            if ($statusArr['oshistory_fullfillment_type'] != OrderProduct::RENTAL_ORDER_RETURN_TYPE_DROP || 1 > $statusArr['oshistorydropoff_addr_id']) {
                continue;
            }
            $addObj = new Address($statusArr['oshistorydropoff_addr_id'], $this->siteLangId);
            $addressDataArr[$statusArr['oshistory_id']] = $addObj->getData(Address::TYPE_SHOP_PICKUP, $statusArr['op_shop_id']);
        }
        return $addressDataArr;
    }

    private function getOrderCommentsForm($orderData = array(), $processingOrderStatus = [])
    {
        $frm = new Form('frmOrderComments');

        $availStatus = [];
        if ($orderData['op_status_id'] == FatApp::getConfig('CONF_DEFAULT_DEIVERED_ORDER_STATUS')) {
            $availStatus[] = FatApp::getConfig("CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END");
        } elseif (in_array($orderData['op_status_id'], $processingOrderStatus)) {
            $availStatus[] = FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS");
            $availStatus[] = FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS");
        }
        $orderStatusArr = Orders::getOrderProductStatusArr($this->siteLangId, $availStatus, $orderData['op_status_id']);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'op_status_id', $orderStatusArr, '', array(), /* Labels::getLabel('Lbl_Select', $this->siteLangId) */ '');
        $fld->requirements()->setRequired();

        if (in_array(FatApp::getConfig('CONF_RETURN_REQUEST_ORDER_STATUS'), $availStatus)) {
            $orderReturnReasonsArr = OrderReturnReason::getOrderReturnReasonArr($this->siteLangId);
            $fld = $frm->addSelectBox(Labels::getLabel('LBL_Reason_to_Return', $this->siteLangId), 'op_return_reason', $orderReturnReasonsArr, '', array(), '');
        }

        if (in_array(FatApp::getConfig('CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END'), $availStatus)) {
            $fullfillfld = $frm->addSelectBox(Labels::getLabel('LBL_FULFILLMENT_TYPE', $this->siteLangId), 'op_return_fullfillment_type', OrderProduct::getRentalOrderReturnType($this->siteLangId), $orderData['op_qty'], array(), '');
            $fullfillfld->requirements()->setRequired();


            $frm->addHiddenField('', 'op_qty');
            /* $frm->addHiddenField('', 'address_id'); */
            if ($orderData['order_is_rfq']) {
                $qtyFld = $frm->addHiddenField('', 'return_qty', $orderData['op_qty']);
            } else {
                $qtyFld = $frm->addIntegerField(Labels::getLabel('LBL_Return_Qty', $this->siteLangId), 'return_qty', '');
                $qtyFld->requirements()->setRequired(true);
                $qtyFld->requirements()->setIntPositive(true);
                if ($orderData['op_qty'] > 1) {
                    $qtyFld->requirements()->setRange(1, $orderData['op_qty']);
                }
            }


            $frm->addTextBox(Labels::getLabel('LBL_Tracking_Number', $this->siteLangId), 'tracking_number')->requirements()->setRequired();

            $trackingNumberUnReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->siteLangId));
            $trackingNumberUnReqObj->setRequired(false);

            $trackingNumberReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->siteLangId));
            $trackingNumberReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_Tracking_URL', $this->siteLangId), 'tracking_url');

            /*  $trackingUrlUnReqObj = new FormFieldRequirement('tracking_url', Labels::getLabel('LBL_Tracking_URL', $this->siteLangId));
            $trackingUrlUnReqObj->setRequired(false);

            $trackingUrlReqObj = new FormFieldRequirement('tracking_url', Labels::getLabel('LBL_Tracking_URL', $this->siteLangId));
            $trackingUrlReqObj->setRequired(true); */


            $frm->addTextBox(Labels::getLabel('LBL_Courier', $this->siteLangId), 'tracking_courier')->requirements()->setRequired();

            $trackingUnReqObj = new FormFieldRequirement('tracking_courier', Labels::getLabel('LBL_Courier', $this->siteLangId));
            $trackingUnReqObj->setRequired(false);

            $trackingReqObj = new FormFieldRequirement('tracking_courier', Labels::getLabel('LBL_Courier', $this->siteLangId));
            $trackingReqObj->setRequired(true);

            $fullfillfld->requirements()->addOnChangerequirementUpdate(OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP, 'eq', 'tracking_number', $trackingNumberReqObj);
            $fullfillfld->requirements()->addOnChangerequirementUpdate(OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP, 'ne', 'tracking_number', $trackingNumberUnReqObj);

            $fullfillfld->requirements()->addOnChangerequirementUpdate(OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP, 'eq', 'tracking_courier', $trackingReqObj);
            $fullfillfld->requirements()->addOnChangerequirementUpdate(OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP, 'ne', 'tracking_courier', $trackingUnReqObj);

            /* $fullfillfld->requirements()->addOnChangerequirementUpdate(OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP, 'eq', 'tracking_url', $trackingReqObj);
            $fullfillfld->requirements()->addOnChangerequirementUpdate(OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP, 'ne', 'tracking_url', $trackingUnReqObj); */
        }

        $frm->addTextArea(Labels::getLabel('LBL_Your_Comments', $this->siteLangId), 'comments');

        $fileFld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_Product_Images', $this->siteLangId), 'file', array('accept' => 'image/*,.zip', 'class' => 'commentFileJs', 'onChange' => 'uploadCommentFormFile()'));
        $fileFld->htmlBeforeField = '<div class="filefield"><span class="filename"></span>';
        $fileFld->htmlAfterField = '</div><span class="form-text text-muted">' . Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed._You_Can_Upload_multiple_files_At_same_time', $this->siteLangId) . '</span>';
        $frm->addHTML("", 'file_field', '<ul class="uploaded-media uploadedFilesJs grid-column-2"></ul>');

        $frm->addHTML("", 'shop_address', "");
        $frm->addHiddenField('', 'op_id', $orderData['op_id']);
        $frm->addHiddenField('', 'customer_notified', 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    /* public function viewInvoice($orderId, $opId = 0)
    {
        if (!$orderId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $opId = FatUtility::int($opId);
        if (0 < $opId) {
            $opOrderId = OrderProduct::getAttributesById($opId, 'op_order_id');
            if ($orderId != $opOrderId) {
                $message = Labels::getLabel('MSG_Invalid_Order', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                CommonHelper::redirectUserReferer();
            }
        }

        $orderObj = new Orders();
        $userId = UserAuthentication::getLoggedUserId();

        $orderDetail = $orderObj->getOrderById($orderId, $this->siteLangId);
        if (!$orderDetail || ($orderDetail && $orderDetail['order_user_id'] != $userId)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $orderDetail['charges'] = $orderObj->getOrderProductChargesByOrderId($orderDetail['order_id']);

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinShop();
        $srch->joinShopSpecifics();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->addOrderProductCharges();
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('order_id', '=', $orderId);
        if (0 < $opId) {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
            $addonProductIds = Orders::getAddonsIdsByProduct($opId);
            $addonProductIds = array_merge($addonProductIds, array($opId));
            $srch->addCondition('op_id', 'IN', $addonProductIds);
        }
        $srch->addMultipleFields(array('*', 'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city'));
        $rs = $srch->getResultSet();

        $childOrderDetail = FatApp::getDb()->fetchAll($rs, 'op_id');

        if (count($childOrderDetail)) {
            foreach ($childOrderDetail as &$arr) {
                $arr['options'] = SellerProduct::getSellerProductOptions($arr['op_selprod_id'], true, $this->siteLangId);
            }
        }

        foreach ($childOrderDetail as $op_id => $val) {
            $childOrderDetail[$op_id]['charges'] = $orderDetail['charges'][$op_id];

            $opChargesLog = new OrderProductChargeLog($op_id);
            $taxOptions = $opChargesLog->getData($this->siteLangId);
            $childOrderDetail[$op_id]['taxOptions'] = $taxOptions;
        }

        if (empty($childOrderDetail) || 1 > count($childOrderDetail)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $address = $orderObj->getOrderAddresses($orderDetail['order_id']);
        $orderDetail['billingAddress'] = $address[Orders::BILLING_ADDRESS_TYPE];
        $orderDetail['shippingAddress'] = (!empty($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($orderDetail['order_id'], $opId);
        $orderDetail['pickupAddress'] = (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $template = new FatTemplate('', '');
        $template->set('siteLangId', $this->siteLangId);
        $template->set('orderDetail', $orderDetail);
        $template->set('childOrderDetail', $childOrderDetail);
        $template->set('opId', $opId);

        require_once(CONF_INSTALLATION_PATH . 'library/tcpdf/tcpdf.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId));
        $pdf->SetKeywords(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId));
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->SetHeaderMargin(0);
        $pdf->SetHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));
        $pdf->setFooterData(array(0, 0, 0), array(200, 200, 200));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetMargins(10, 10, 10);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->SetTitle(Labels::getLabel('LBL_Tax_Invoice', $this->siteLangId));
        $pdf->SetSubject(Labels::getLabel('LBL_Tax_Invoice', $this->siteLangId));

        // set LTR direction for english translation
        $pdf->setRTL(('rtl' == Language::getLayoutDirection($this->siteLangId)));
        // set font
        $pdf->SetFont('dejavusans');

        $templatePath = "buyer/view-invoice.php";
        $html = addslashes($template->render(false, false, $templatePath, true, true));
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->lastPage();

        ob_end_clean();
        // $saveFile = CONF_UPLOADS_PATH . 'demo-pdf.pdf';
        //$pdf->Output($saveFile, 'F');
        $pdf->Output('tax-invoice.pdf', 'I');
        return true;
    } */

    public function viewInvoice($orderId, $opId = 0)
    {
        if (!$orderId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $opId = FatUtility::int($opId);
        if (0 < $opId) {
            $opOrderId = OrderProduct::getAttributesById($opId, 'op_order_id');
            if ($orderId != $opOrderId) {
                $message = Labels::getLabel('MSG_Invalid_Order', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                CommonHelper::redirectUserReferer();
            }
        }

        $orderObj = new Orders();
        $userId = UserAuthentication::getLoggedUserId();

        $orderDetail = $orderObj->getOrderById($orderId, $this->siteLangId);
        if (!$orderDetail || ($orderDetail && $orderDetail['order_user_id'] != $userId)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $orderDetail['charges'] = $orderObj->getOrderProductChargesByOrderId($orderDetail['order_id']);

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinShop();
        $srch->joinShopSpecifics();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->addOrderProductCharges();
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('order_id', '=', $orderId);
        if (0 < $opId) {
            /* $srch->addCondition('op_id', '=', $opId); */
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
            $addonProductIds = Orders::getAddonsIdsByProduct($opId);
            $addonProductIds = array_merge($addonProductIds, array($opId));
            $srch->addCondition('op_id', 'IN', $addonProductIds);
        }
        $srch->addMultipleFields(array('*', 'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city'));
        $rs = $srch->getResultSet();

        $childOrderDetail = FatApp::getDb()->fetchAll($rs, 'op_id');
        $servicesCartTotal = 0;
        $servicesNetTotal = 0;
        $servicesTaxTotal = 0;
        $servicesLateCharges = 0;

        foreach ($childOrderDetail as $op_id => $val) {
            $childOrderDetail[$op_id]['charges'] = $orderDetail['charges'][$op_id];
            $opChargesLog = new OrderProductChargeLog($op_id);
            $taxOptions = $opChargesLog->getData($this->siteLangId);
            $childOrderDetail[$op_id]['taxOptions'] = $taxOptions;
            if ($val['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                $parentOpId = $val['op_attached_op_id'];
                $opChargesLog = new OrderProductChargeLog($op_id);
                $taxOptions = $opChargesLog->getData($this->siteLangId);
                $servicesCartTotal += CommonHelper::orderProductAmount($val, 'CART_TOTAL');
                $servicesTaxTotal += CommonHelper::orderProductAmount($val, 'TAX');
                $servicesNetTotal += CommonHelper::orderProductAmount($val, 'netamount');
                $servicesLateCharges += $val['charge_total_amount'];
                $childOrderDetail[$parentOpId]['services'][$op_id] =  $val;
                $childOrderDetail[$parentOpId]['services'][$op_id]['charges'] =  $orderDetail['charges'][$op_id];
                $childOrderDetail[$parentOpId]['services'][$op_id]['taxOptions'] =  $taxOptions;
                unset($childOrderDetail[$op_id]);
            } else {
                $childOrderDetail[$op_id]['charges'] = $orderDetail['charges'][$op_id];
                $opChargesLog = new OrderProductChargeLog($op_id);
                $taxOptions = $opChargesLog->getData($this->siteLangId);
                $childOrderDetail[$op_id]['services'] = [];
                $childOrderDetail[$op_id]['taxOptions'] = $taxOptions;
                $childOrderDetail[$op_id]['deliveredMarkedBy'] = OrderProduct::checkOrderDeliveredMarkedByBuyer($op_id, $val['order_user_id']);
            }
        }

        if (count($childOrderDetail)) {
            foreach ($childOrderDetail as &$arr) {
                $arr['options'] = SellerProduct::getSellerProductOptions($arr['op_selprod_id'], true, $this->siteLangId);
            }
        }

        if (empty($childOrderDetail) || 1 > count($childOrderDetail)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $address = $orderObj->getOrderAddresses($orderDetail['order_id']);
        $orderDetail['billingAddress'] = $address[Orders::BILLING_ADDRESS_TYPE];
        $orderDetail['shippingAddress'] = (!empty($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($orderDetail['order_id'], $opId);
        $orderDetail['pickupAddress'] = (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $template = new FatTemplate('', '');
        $template->set('siteLangId', $this->siteLangId);
        $template->set('orderDetail', $orderDetail);
        /* $template->set('childOrderDetail', $childOrderDetail); */
        $template->set('opId', $opId);

        /* get invoice attachment */
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_INVOICE_LOGO, 0, 0, $this->siteLangId);
        $logoImgUrl = '';
        if ($file_row['afile_id'] > 0) {
            $logoImgUrl =  UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . 'user-uploads/' . AttachedFile::FILETYPE_INVOICE_LOGO_PATH  . $file_row['afile_physical_path'];
        }
        $template->set('logoImgUrl', $logoImgUrl);
        /* ---- */

        require_once(CONF_INSTALLATION_PATH . 'library/tcpdf/tcpdf.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId));
        $pdf->SetKeywords(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId));
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->SetHeaderMargin(0);
        $pdf->SetHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));
        $pdf->setFooterData(array(0, 0, 0), array(200, 200, 200));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetMargins(10, 10, 10);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->SetTitle(Labels::getLabel('LBL_Tax_Invoice', $this->siteLangId));
        $pdf->SetSubject(Labels::getLabel('LBL_Tax_Invoice', $this->siteLangId));

        // set LTR direction for english translation
        $pdf->setRTL(('rtl' == Language::getLayoutDirection($this->siteLangId)));
        // set font
        $pdf->SetFont('dejavusans');


        $count = 0;
        foreach ($childOrderDetail as $childOrder) {
            $template->set('childOrder', $childOrder);
            $template->set('servicesNetTotal', $servicesNetTotal);
            if ($count > 0) {
                $pdf->AddPage('P', 'A4');
            }

            $templatePath = "buyer/view-invoice.php";
            $html = $template->render(false, false, $templatePath, true, true);
            $pdf->writeHTML($html, true, false, true, false, '');
            $count++;
        }
        /* $html = addslashes($template->render(false, false, $templatePath, true, true)); */

        $pdf->lastPage();

        ob_end_clean();
        // $saveFile = CONF_UPLOADS_PATH . 'demo-pdf.pdf';
        //$pdf->Output($saveFile, 'F');
        $pdf->Output('tax-invoice.pdf', 'I');
        return true;
    }

    public function orders()
    {
        if(!FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) {
            FatUtility::exitWithErrorCode(404);
        }
        
        $data = FatApp::getPostedData();
        $data['order_type'] = applicationConstants::PRODUCT_FOR_SALE;
        $frmOrderSrch = $this->getOrderSearchForm($this->siteLangId);
        if (!empty($data)) {
            $frmOrderSrch->fill($data);
        }
    
        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->set('orderType', applicationConstants::PRODUCT_FOR_SALE);
        $this->_template->render(true, true);
    }

    public function RentalOrders()
    {
        $data = FatApp::getPostedData();
        $data['order_type'] = applicationConstants::PRODUCT_FOR_RENT;
        $frmOrderSrch = $this->getOrderSearchForm($this->siteLangId);
        if (!empty($data)) {
            $frmOrderSrch->fill($data);
        }
    
    
        /* $frmOrderSrch = $this->getOrderSearchForm($this->siteLangId);
        $data = array('order_type' => applicationConstants::PRODUCT_FOR_RENT);
        $frmOrderSrch->fill($data); */

        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->set('orderType', applicationConstants::PRODUCT_FOR_RENT);
        $this->_template->render(true, true, 'buyer/orders.php', false, true);
    }

    public function orderSearchListing()
    {
        $frm = $this->getOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $orderType = (isset($post['order_type'])) ? $post['order_type'] : applicationConstants::PRODUCT_FOR_SALE;
        $user_id = UserAuthentication::getLoggedUserId();
        $orderReportType = FatApp::getPostedData('orderReportType', FatUtility::VAR_INT, 0);

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addCountsOfOrderedProducts();
        $srch->joinShippingCharges();
        $srch->joinShopSpecifics();
        $srch->joinSellerProductSpecifics();
        $srch->joinOrderProductSpecifics();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinTable(
            OrderReturnRequest::DB_TBL,
            'LEFT OUTER JOIN',
            'orr.orrequest_op_id = op.op_id',
            'orr'
        );
        $srch->joinTable(
            OrderCancelRequest::DB_TBL,
            'LEFT OUTER JOIN',
            'ocr.ocrequest_op_id = op.op_id',
            'ocr'
        );

        if (true === MOBILE_APP_API_CALL) {
            $srch->joinSellerProducts();
            $srch->addfld('selprod_product_id');
        }

        $srch->addCondition('order_is_rfq', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $srch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. $orderType, 'AND', true);

        $addonSrch = new OrderProductSearch(0, true, true);
        $addonSrch->joinSellerProducts();
        $addonSrch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $addonSrch->addCondition('order_is_rfq', '=', 'mysql_func_'.applicationConstants::NO, 'AND', true);
        $addonSrch->doNotCalculateRecords();
        $addonSrch->addMultipleFields(['IFNULL(SUM((op_qty * op_unit_price)), 0) as addonAmount', 'op_attached_op_id']);
        $addonSrch->addGroupBy('op_attached_op_id');
        $addonSrch->addCondition('order_user_id', '=', 'mysql_func_'.$user_id, 'AND', true);
        $addonSrch->addCondition('opd.opd_sold_or_rented', '=', 'mysql_func_'.applicationConstants::PRODUCT_FOR_RENT, 'AND', true);
        $addonSrch->addCondition('opd_product_type', '=', 'mysql_func_'.SellerProduct::PRODUCT_TYPE_ADDON, 'AND', true);
        
        $srch->joinTable('(' . $addonSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'op.op_id = addonQry.op_attached_op_id', 'addonQry');
        $srch->addCondition('opd_product_type', '=', 'mysql_func_'. SellerProduct::PRODUCT_TYPE_PRODUCT, 'AND', true);
        $srch->joinPaymentMethod();
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(
            array(
                'order_id', 'order_user_id', 'order_date_added', 'order_net_amount', 'op_invoice_number',
                'totCombinedOrders as totOrders', 'op_selprod_id', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_id', 'op_other_charges', 'op_unit_price',
                'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_status_id', 'op_product_type',
                'IF(opshipping_fulfillment_type = '. Shipping::FULFILMENT_PICKUP .' AND op_status_id = '. OrderStatus::ORDER_DELIVERED .', "'. Labels::getLabel('LBL_Picked', $this->siteLangId) .'", IFNULL(orderstatus_name, orderstatus_identifier)) as orderstatus_name', 'orderstatus_color_class', 'order_pmethod_id', 'order_status', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'IFNULL(orrequest_id, 0) as return_request', 'IFNULL(ocrequest_id, 0) as cancel_request', 'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age', 'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age', 'order_payment_status', 'order_deleted', 'plugin_code', 'opshipping_fulfillment_type', 'op_rounding_off', 'opd.*', 'op_delivery_time', '(op_qty * op_unit_price + (opd_rental_security * op_qty) + op_other_charges + addonAmount) as totalAmount', 'addonQry.addonAmount as addon_amount'
            )
        );

        if ($orderType == applicationConstants::ORDER_TYPE_RENT) {
            $statusCheckSrch = new SearchBase(Orders::DB_TBL_ORDER_STATUS_HISTORY, 'opstatus');
            $statusCheckSrch->addCondition('oshistory_orderstatus_id', '=', 'mysql_func_'. FatApp::getConfig('CONF_DEFAULT_DEIVERED_ORDER_STATUS'), 'AND', true);
            $statusCheckSrch->addFld('oshistory_status_updated_by');
            $statusCheckSrch->addDirectCondition('opstatus.oshistory_status_updated_by = order_user_id');
            $statusCheckSrch->addDirectCondition('opstatus.oshistory_op_id = op_id');
            $statusCheckSrch->doNotCalculateRecords();
            $statusCheckSrch->doNotLimitRecords();
            $statusCheckQry = $statusCheckSrch->getQuery();
            $srch->addFld('IFNULL((' . $statusCheckQry . '), 0) as deliveredMarkedBy');
        }


        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        if ($orderReportType == 0) {
            $op_status_id = FatApp::getPostedData('status', null, '0');
            if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")))) {
                $srch->addStatusCondition($op_status_id, ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
            } else {
                $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")), ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
            }
        } else {
            $completedOrderStatus = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS", FatUtility::VAR_STRING, ''));
            switch ($orderReportType) {
                case Stats::COMPLETED_SALES: 
                    $srch->addCondition('op_status_id', 'IN', $completedOrderStatus);
                    break;
                case Stats::INPROCESS_SALES:
                    /* $completedOrderStatus[] = FatApp::getConfig('CONF_DEFAULT_ORDER_STATUS'); */
                    $srch->addCondition('op_status_id', 'NOT IN', $completedOrderStatus);
                    break;
            }
        }
        
        

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addHaving('totalAmount', '>=', $priceFrom);
            /* $srch->addHaving('totOrders', '=', '1');
            $srch->addMinPriceCondition($priceFrom); */
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addHaving('totalAmount', '<=', $priceTo);
            /* $srch->addHaving('totOrders', '=', '1');
            $srch->addMaxPriceCondition($priceTo); */
        }

        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);
        $addonAmountArr = [];
        $oObj = new Orders();
        

        $orderProductStatusArr = [];
        if (!empty($orders)) {
            $opIds = array_column($orders, 'op_id');
            $opStatusObj = new OrderProduct();
            $orderProductStatusArr = $opStatusObj->getStatusHistoryArr($opIds, true);
        }


        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id'], MOBILE_APP_API_CALL);
            $order['charges'] = $charges;
            $order['status_history'] = (isset($orderProductStatusArr[$order['op_id']])) ? $orderProductStatusArr[$order['op_id']] : [];
        }
        $this->set('orders', $orders);
        $this->set('pageSize', $pagesize);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('classArr', applicationConstants::getClassArr());
        $this->set('statusForReadyToReturn', OrderStatus::getStatusForMarkOrderReadyForReturn());

        if (true === MOBILE_APP_API_CALL) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")), 0, 0, false);
            $this->set('orderStatuses', $orderStatuses);
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function orderCancellationRequest($op_id)
    {
        $op_id = FatUtility::int($op_id);

        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinShopSpecifics();
        $srch->joinSellerProductSpecifics();
        $srch->joinOrderProductSpecifics();
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $srch->addCondition('op_id', '=', 'mysql_func_'.$op_id, 'AND', true);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(
            array(
                'op_status_id', 'op_id', 'op_product_type', 'opd_sold_or_rented', 'order_date_added',
                'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age',
                'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age', 'op_selprod_user_id', 'opd_rental_start_date'
            )
        );
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'orderCancellationRequests'));
        }

        $oReturnRequestSrch = new OrderReturnRequestSearch();
        $oReturnRequestSrch->doNotCalculateRecords();
        $oReturnRequestSrch->doNotLimitRecords();
        $oReturnRequestSrch->addCondition('orrequest_op_id', '=', 'mysql_func_'. $opDetail['op_id'] , 'AND', true);
        $oReturnRequestSrch->addCondition('orrequest_status', '!=', 'mysql_func_'. OrderReturnRequest::RETURN_REQUEST_STATUS_CANCELLED, 'AND', true);
        $oReturnRequestRs = $oReturnRequestSrch->getResultSet();

        if (FatApp::getDb()->fetch($oReturnRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_return_request', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'orderCancellationRequests'));
        }

        if (!in_array($opDetail["op_status_id"], (array) Orders::getBuyerAllowedOrderCancellationStatuses())) {
            Message::addErrorMessage(Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'orderCancellationRequests'));
        }
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && !FatApp::getConfig('CONF_ALLOW_RENTAL_ORDER_CANCEL_FROM_BUYER_END', FatUtility::VAR_INT, 0)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Order_Cancellation_is_not_activated_for_rental_orders', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', $action));
        }

        $datediff = time() - strtotime($opDetail['order_date_added']);
        $orderCancelPenaltyRules = [];
        $daysSpent = 0;
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
            $action = 'rentalOrderCancellationRequests';
            $hoursForRentStart = ceil((strtotime($opDetail['opd_rental_start_date']) - time()) / (60 * 60)); // Cancel and Return Age in Hours
            if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) {
                $orderCancelPenaltyRules = OrderCancelRule::getCancelRefundAmountByDuration($hoursForRentStart, $opDetail['op_selprod_user_id'], true);
            }
        } else {
            $msg = $opDetail['cancellation_age'] . ' ' . Labels::getLabel('Lbl_Day(s)', $this->siteLangId);
            $daysSpent = round($datediff / (60 * 60 * 24)); // Cancel and Return Age in Days
            $action = 'orderCancellationRequests';
        }

        if (false !== OrderCancelRequest::getCancelRequestById($opDetail['op_id'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_You_have_already_sent_the_cancellation_request_for_this_order', $this->siteLangId));
            // CommonHelper::redirectUserReferer();
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', $action));
        }

        if ($opDetail['cancellation_age'] < $daysSpent && $opDetail['opd_sold_or_rented'] != applicationConstants::ORDER_TYPE_RENT) {
            Message::addErrorMessage(Labels::getLabel('LBL_You_can_not_place_cancel_request_for_this_order_as_per_cancel_policy_the_cancel_Period_for_this_product_is', $this->siteLangId) . ' ' . $msg);
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', $action));
        }

        $frm = $this->getOrderCancelRequestForm($this->siteLangId);
        $frm->fill(array('op_id' => $opDetail['op_id']));
        $this->set('frmOrderCancel', $frm);

        $this->set('orderCancelPenaltyRules', $orderCancelPenaltyRules);
        $this->_template->render(true, true);
    }

    public function orderCancellationReasons()
    {
        $orderCancelReasonsArr = OrderCancelReason::getOrderCancelReasonArr($this->siteLangId);
        $count = 0;
        foreach ($orderCancelReasonsArr as $key => $val) {
            $cancelReasonsArr[$count]['key'] = $key;
            $cancelReasonsArr[$count]['value'] = $val;
            $count++;
        }
        $this->set('data', array('reasons' => $cancelReasonsArr));
        $this->_template->render();
    }

    public function orderReturnRequestsReasons($op_id)
    {
        if (1 > FatUtility::int($op_id)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $user_id = UserAuthentication::getLoggedUserId();
        $orderReturnReasonsArr = OrderReturnReason::getOrderReturnReasonArr($this->siteLangId);
        $count = 0;
        foreach ($orderReturnReasonsArr as $key => $val) {
            $returnReasonsArr[$count]['key'] = $key;
            $returnReasonsArr[$count]['value'] = $val;
            $count++;
        }
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $srch->addCondition('op_id', '=', 'mysql_func_'. $op_id, 'AND', true);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('op_status_id', 'op_id', 'op_qty', 'op_product_type'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $this->set('data', array('reasons' => $returnReasonsArr));
        $this->_template->render();
    }

    public function setupOrderCancelRequest()
    {
        $frm = $this->getOrderCancelRequestForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $op_id = FatUtility::int($post['op_id']);

        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinShopSpecifics();
        $srch->joinSellerProductSpecifics();
        $srch->joinOrderProductSpecifics();
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $srch->addCondition('op_id', '=', 'mysql_func_'. $op_id, 'AND', true);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(
            array(
                'op_status_id', 'op_id', 'op_product_type', 'opd_sold_or_rented', 'order_date_added',
                'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age',
                'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age', 'opd_rental_start_date', 'op_selprod_user_id'
            )
        );

        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!in_array($opDetail["op_status_id"], (array) Orders::getBuyerAllowedOrderCancellationStatuses())) {
            $message = Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && !FatApp::getConfig('CONF_ALLOW_RENTAL_ORDER_CANCEL_FROM_BUYER_END', FatUtility::VAR_INT, 0)) {
            $message = Labels::getLabel('MSG_Order_Cancellation_is_not_activated_for_rental_orders', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $orderRefundAmount = 0;
        $isPenaltyApplicable = 0;
        $hoursForRentStart = 0;
        $datediff = time() - strtotime($opDetail['order_date_added']);
        $daysSpent = 0;
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
            $hoursForRentStart = ceil((strtotime($opDetail['opd_rental_start_date']) - time()) / (60 * 60)); // Cancel and Return Age in Hours
            if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) {
                $orderRefundAmount = OrderCancelRule::getCancelRefundAmountByDuration($hoursForRentStart, $opDetail['op_selprod_user_id']);
                $isPenaltyApplicable = 1;
            }
            $action = 'rentalOrderCancellationRequests';
        } else {
            $msg = $opDetail['cancellation_age'] . ' ' . Labels::getLabel('Lbl_Day(s)', $this->siteLangId);
            $daysSpent = round($datediff / (60 * 60 * 24)); // Cancel and Return Age in Days
            $action = 'orderCancellationRequests';
        }
        if ($opDetail['cancellation_age'] < $daysSpent && $opDetail['opd_sold_or_rented'] != applicationConstants::ORDER_TYPE_RENT) {
            $message = Labels::getLabel('LBL_You_can_not_place_cancel_request_for_this_order_as_per_cancel_policy_the_cancel_Period_for_this_product_is', $this->siteLangId) . ' ' . $msg;
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!in_array($opDetail["op_status_id"], (array) Orders::getBuyerAllowedOrderCancellationStatuses())) {
            $message = Labels::getLabel('MSG_Order_Cancellation_cannot_placed', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $ocRequestSrch = new OrderCancelRequestSearch();
        $ocRequestSrch->doNotCalculateRecords();
        $ocRequestSrch->doNotLimitRecords();
        $ocRequestSrch->addCondition('ocrequest_op_id', '=', 'mysql_func_'. $opDetail['op_id'], 'AND', true);
        $ocRequestRs = $ocRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($ocRequestRs)) {
            $message = Labels::getLabel('MSG_You_have_already_sent_the_cancellation_request_for_this_order', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $dataToSave = array(
            'ocrequest_user_id' => $user_id,
            'ocrequest_op_id' => $opDetail['op_id'],
            'ocrequest_ocreason_id' => FatUtility::int($post['ocrequest_ocreason_id']),
            'ocrequest_message' => $post['ocrequest_message'],
            'ocrequest_date' => date('Y-m-d H:i:s'),
            'ocrequest_status' => OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING,
            'ocrequest_refund_amount' => $orderRefundAmount,
            'ocrequest_hours_before_rental' => $hoursForRentStart,
            'ocrequest_is_penalty_applicable' => $isPenaltyApplicable
        );

        $oCRequestObj = new OrderCancelRequest();
        $oCRequestObj->assignValues($dataToSave);


        if (!$oCRequestObj->save()) {
            Message::addErrorMessage($oCRequestObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $ocrequest_id = $oCRequestObj->getMainTableRecordId();
        if (!$ocrequest_id) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $emailObj = new EmailHandler();
        if (!$emailObj->sendOrderCancellationNotification($ocrequest_id, $this->siteLangId)) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($emailObj->getError());
            }
            Message::addErrorMessage($emailObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $notificationType = Notification::ORDER_CANCELLATION_NOTIFICATION_RENTAL;
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_SALE) {
            $notificationType = Notification::ORDER_CANCELLATION_NOTIFICATION;
        }

        /* send notification to admin */
        $notificationData = array(
            'notification_record_type' => Notification::TYPE_ORDER_CANCELATION,
            'notification_record_id' => $oCRequestObj->getMainTableRecordId(),
            'notification_user_id' => $user_id,
            'notification_label_key' => $notificationType,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel('MSG_NOTIFICATION_COULD_NOT_BE_SENT', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($emailObj->getError());
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $msg = Labels::getLabel('MSG_Your_cancellation_request_submitted', $this->siteLangId);
        if (true === MOBILE_APP_API_CALL) {
            $this->set('msg', $msg);
            $this->_template->render();
        }

        //FatUtility::dieJsonSuccess($msg);
        $this->set('msg', $msg);
        $this->set('action', $action);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function orderCancellationRequests()
    {
        if(!FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) {
            FatUtility::exitWithErrorCode(404);
        }
        
        $frm = $this->getOrderCancellationRequestsSearchForm($this->siteLangId, applicationConstants::ORDER_TYPE_SALE);
        $this->set('frmOrderCancellationRequestsSrch', $frm);
        $this->set('orderType', applicationConstants::ORDER_TYPE_SALE);
        $this->_template->render(true, true);
    }

    public function rentalOrderCancellationRequests()
    {
        $frm = $this->getOrderCancellationRequestsSearchForm($this->siteLangId, applicationConstants::ORDER_TYPE_RENT);
        $this->set('frmOrderCancellationRequestsSrch', $frm);
        $this->set('orderType', applicationConstants::ORDER_TYPE_RENT);
        $this->_template->render(true, true, 'buyer/order-cancellation-requests.php');
    }

    public function orderCancellationRequestSearch()
    {
        $orderType = FatApp::getPostedData('order_product_type', FatUtility::VAR_INT, applicationConstants::ORDER_TYPE_SALE);
        $frm = $this->getOrderCancellationRequestsSearchForm($this->siteLangId, $orderType);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = $this->orderCancellationRequestObj();
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        if (true === MOBILE_APP_API_CALL) {
            $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'selprod_id = op_selprod_id');
            $srch->joinTable(SellerProduct::DB_TBL_LANG, 'INNER JOIN', 'selprod_id = selprodlang_selprod_id AND selprodlang_lang_id = ' . $this->siteLangId);
            $srch->addFld(array('selprod_product_id', 'selprod_title'));
        }
        $srch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. $orderType, 'AND', true);
        $op_invoice_number = $post['op_invoice_number'];
        if (!empty($op_invoice_number)) {
            $srch->addCondition('op_invoice_number', '=', $op_invoice_number);
        }

        $ocrequest_date_from = $post['ocrequest_date_from'];
        if (!empty($ocrequest_date_from)) {
            $srch->addCondition('ocrequest_date', '>=', $ocrequest_date_from . ' 00:00:00');
        }

        $ocrequest_date_to = $post['ocrequest_date_to'];
        if (!empty($ocrequest_date_to)) {
            $srch->addCondition('ocrequest_date', '<=', $ocrequest_date_to . ' 23:59:59');
        }

        /* $ocrequest_status = $post['ocrequest_status'];
          if( !empty( $ocrequest_status ) ){ */
        $ocrequest_status = FatApp::getPostedData('ocrequest_status', null, '-1');
        if ($ocrequest_status > -1) {
            $ocrequest_status = FatUtility::int($ocrequest_status);
            $srch->addCondition('ocrequest_status', '=', 'mysql_func_'. $ocrequest_status, 'AND', true);
        }

        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $this->set('requests', $requests);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('orderType', $orderType);
        $this->set('pageSize', $pagesize);
        $this->set('OrderCancelRequestStatusArr', OrderCancelRequest::getRequestStatusArr($this->siteLangId));
        $this->set('cancelReqStatusClassArr', OrderCancelRequest::getStatusClassArr());
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false);
    }

    private function orderCancellationRequestObj()
    {
        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'IFNULL(sum(opcharge_amount), 0) as shipping_charges'));
        $ocSrch->addCondition('opcharge_type', '=', 'mysql_func_'. OrderProduct::CHARGE_TYPE_SHIPPING, 'AND', true);
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();


        $srch = new OrderCancelRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->addOrderProductCharges();
        $srch->joinOrderCancelReasons();
        $srch->joinOrders();
        $srch->addCondition('ocrequest_user_id', '=', 'mysql_func_'. UserAuthentication::getLoggedUserId(), 'AND', true);
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');

        $srch->addMultipleFields(array('ocrequest_is_penalty_applicable', 'ocrequest_refund_amount', 'ocrequest_hours_before_rental', 'opd_rental_start_date', 'ocrequest_id', 'ocrequest_date', 'ocrequest_status', 'order_id', 'op_invoice_number', 'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'ocrequest_message', 'op_id', 'op_is_batch', 'op_selprod_id', 'order_id', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'op_qty', 'op_unit_price', 'op_rounding_off', 'opd_sold_or_rented', 'opd_rental_security', 'order_net_amount', 'opcc.*'));
        $srch->addOrder('ocrequest_date', 'DESC');
        $srch->addGroupBy('ocrequest_op_id');
        return $srch;
    }

    public function orderReturnRequests()
    {
        if(!FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) {
            FatUtility::exitWithErrorCode(404);
        }
        
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId, applicationConstants::ORDER_TYPE_SALE);
        $this->set('frmOrderReturnRequestsSrch', $frm);
        $this->_template->render(true, true);
    }

    public function rentalOrderReturnRequests()
    {
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId, applicationConstants::ORDER_TYPE_RENT);
        $this->set('frmOrderReturnRequestsSrch', $frm);
        $this->_template->render(true, true, 'buyer/order-return-requests.php');
    }

    public function orderReturnRequestSearch()
    {
        $orderFor = FatApp::getPostedData('order_product_for', FatUtility::VAR_INT, applicationConstants::ORDER_TYPE_SALE);
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId, $orderFor);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $srch = $this->orderReturnRequestObj();
        $srch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. $orderFor, 'AND', true);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(
            array(
                'orrequest_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type', 'orrequest_reference', 'orrequest_date', 'orrequest_status',
                'op_invoice_number', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model'
            )
        );

        if (true === MOBILE_APP_API_CALL) {
            $srch->joinTable(OrderReturnReason::DB_TBL, 'LEFT JOIN', 'orrequest_returnreason_id = orreason_id');
            $srch->joinTable(OrderReturnReason::DB_TBL_LANG, 'LEFT JOIN', 'orreasonlang_orreason_id = orreason_id AND orreasonlang_lang_id  = ' . $this->siteLangId);
            $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'selprod_id = op_selprod_id');
            $srch->joinTable(SellerProduct::DB_TBL_LANG, 'INNER JOIN', 'selprod_id = selprodlang_selprod_id AND selprodlang_lang_id = ' . $this->siteLangId);
            $srch->addFld(array('selprod_product_id', 'selprod_title', 'IFNULL(orreason_title, orreason_identifier) as requestReason'));
        }

        $srch->addOrder('orrequest_date', 'DESC');

        $keyword = $post['keyword'];
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op_invoice_number', '=', $keyword);
            $cnd->attachCondition('op_selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_product_identifier', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_product_name', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_brand_name', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_selprod_options', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_selprod_sku', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_product_model', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('orrequest_reference', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $orrequest_status = FatApp::getPostedData('orrequest_status', null, '-1');
        if ($orrequest_status > -1) {
            $orrequest_status = FatUtility::int($orrequest_status);
            $srch->addCondition('orrequest_status', '=', 'mysql_func_'. $orrequest_status, 'AND', true);
        }

        $orrequest_date_from = $post['orrequest_date_from'];
        if (!empty($orrequest_date_from)) {
            $srch->addCondition('orrequest_date', '>=', $orrequest_date_from . ' 00:00:00');
        }

        $orrequest_date_to = $post['orrequest_date_to'];
        if (!empty($orrequest_date_to)) {
            $srch->addCondition('orrequest_date', '<=', $orrequest_date_to . ' 23:59:59');
        }

        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $this->set('sellerPage', false);
        $this->set('buyerPage', true);

        $this->set('requests', $requests);
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('returnRequestTypeArr', OrderReturnRequest::getRequestTypeArr($this->siteLangId));
        $this->set('OrderReturnRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('OrderRetReqStatusClassArr', OrderReturnRequest::getRequestStatusClassArr());
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function orderReturnRequestObj()
    {
        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->addCondition('orrequest_user_id', '=', 'mysql_func_'. UserAuthentication::getLoggedUserId(), 'AND', true);
        $srch->addMultipleFields(
            array(
                'orrequest_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type', 'orrequest_reference', 'orrequest_date', 'orrequest_status', 'opd_sold_or_rented',
                'op_invoice_number', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_id', 'op_is_batch', 'op_selprod_id', 'order_id'
            )
        );
        $srch->addOrder('orrequest_date', 'DESC');
        return $srch;
    }

    public function viewOrderReturnRequest($orrequest_id, $print = false, $prodType = applicationConstants::PRODUCT_FOR_SALE)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->addCondition('orrequest_id', '=', 'mysql_func_'. $orrequest_id, 'AND', true);
        $srch->addCondition('orrequest_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $srch->joinOrderProducts();
        $srch->joinOrderProductSettings();
        $srch->joinOrders();
        //$srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();
        $srch->addOrderProductCharges();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array(
                'orrequest_id', 'orrequest_op_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type',
                'orrequest_date', 'orrequest_status', 'orrequest_reference', 'op_invoice_number', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_qty',
                'op_unit_price', 'op_selprod_user_id', 'IFNULL(orreason_title, orreason_identifier) as orreason_title',
                'op_shop_id', 'op_shop_name', 'op_shop_owner_name', 'order_tax_charged', 'op_other_charges', 'op_refund_amount', 'op_commission_percentage', 'op_affiliate_commission_percentage', 'op_commission_include_tax', 'op_commission_include_shipping', 'op_free_ship_upto', 'op_actual_shipping_charges', 'op_rounding_off', 'opd_sold_or_rented', 'opd_rental_security', 'op_commission_charged'
            )
        );
        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);
        if (!$request) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'orderReturnRequests'));
        }

        $oObj = new Orders();
        $charges = $oObj->getOrderProductChargesArr($request['orrequest_op_id']);
        $request['charges'] = $charges;

        $sellerUserObj = new User($request['op_selprod_user_id']);
        $vendorReturnAddress = $sellerUserObj->getUserReturnAddress($this->siteLangId);

        $returnRequestMsgsSrchForm = $this->getOrderReturnRequestMessageSearchForm($this->siteLangId);
        $returnRequestMsgsSrchForm->fill(array('orrequest_id' => $request['orrequest_id']));

        $frm = $this->getOrderReturnRequestMessageForm($this->siteLangId);
        $frm->fill(array('orrmsg_orrequest_id' => $request['orrequest_id']));
        $this->set('frmMsg', $frm);

        $canEscalateRequest = false;
        $canWithdrawRequest = false;
        /* if( $request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING ){
          $canEscalateRequest = true;
          } */

        if (($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) || $request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED) {
            $canWithdrawRequest = true;
        }
        if ($attachedFile = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id)) {
            $this->set('attachedFiles', $attachedFile);
        }
        $this->set('canEscalateRequest', $canEscalateRequest);
        $this->set('canWithdrawRequest', $canWithdrawRequest);
        $this->set('returnRequestMsgsSrchForm', $returnRequestMsgsSrchForm);
        $this->set('request', $request);
        $this->set('prodType', $prodType);
        $this->set('vendorReturnAddress', $vendorReturnAddress);
        $this->set('returnRequestTypeArr', OrderReturnRequest::getRequestTypeArr($this->siteLangId));
        $this->set('requestRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('logged_user_name', UserAuthentication::getLoggedUserAttribute('user_name'));
        $this->set('logged_user_id', UserAuthentication::getLoggedUserId());

        if ($print) {
            $print = true;
        }
        $this->set('print', $print);
        $urlParts = array_filter(FatApp::getParameters());
        $this->set('urlParts', $urlParts);

        $this->_template->render();
    }

    public function downloadAttachedFileForReturn($recordId, $recordSubid = 0, $fileId = 0)
    {
        $recordId = FatUtility::int($recordId);

        if (1 > $recordId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $recordId, $recordSubid);

        if ($fileId > 0) {
            $file_row = AttachedFile::getAttributesById($fileId);
        }

        if (false == $file_row) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    public function WithdrawOrderReturnRequest($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();

        $srch->addCondition('orrequest_id', '=', 'mysql_func_'. $orrequest_id, 'AND', true);
        $srch->addCondition('orrequest_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $cnd = $srch->addCondition('orrequest_status', '=', 'mysql_func_'. OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING, 'AND', true);
        $cnd->attachCondition('orrequest_status', '=', 'mysql_func_'. OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED, 'OR', true);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'op_id', 'order_language_id'));
        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);
        if (!$request) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'viewOrderReturnRequest', array($orrequest_id)));
        }

        $orrObj = new OrderReturnRequest();
        if (!$orrObj->withdrawRequest($request['orrequest_id'], $user_id, $this->siteLangId, $request['op_id'], $request['order_language_id'])) {
            $message = Labels::getLabel($orrObj->getError(), $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'viewOrderReturnRequest', array($orrequest_id)));
        }

        /* email notification handling[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($request['orrequest_id'], $this->siteLangId)) {
            $message = Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }
        /* ] */

        //send notification to admin
        $notificationData = array(
            'notification_record_type' => Notification::TYPE_ORDER_RETURN_REQUEST,
            'notification_record_id' => $request['orrequest_id'],
            'notification_user_id' => UserAuthentication::getLoggedUserId(),
            'notification_label_key' => Notification::RETURN_REQUEST_STATUS_CHANGE_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel('MSG_NOTIFICATION_COULD_NOT_BE_SENT', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        Message::addMessage(Labels::getLabel('MSG_Request_Withdrawn', $this->siteLangId));
        FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'viewOrderReturnRequest', array($orrequest_id)));
    }

    /* public function orderReturnRequestMessageSearch(){
      $frm = $this->getOrderReturnRequestMessageSearchForm( $this->siteLangId );
      $post = $frm->getFormDataFromArray( FatApp::getPostedData() );
      $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
      $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
      $user_id = UserAuthentication::getLoggedUserId();

      $orrequest_id = isset($post['orrequest_id']) ? FatUtility::int($post['orrequest_id']) : 0;

      $srch = new OrderReturnRequestMessageSearch( $this->siteLangId );
      $srch->joinOrderReturnRequests();
      $srch->joinMessageUser();
      $srch->addCondition( 'orrmsg_orrequest_id', '=', $orrequest_id );
      //$srch->addCondition( 'orrequest_user_id', '=', $user_id );
      $srch->setPageNumber($page);
      $srch->setPageSize($pageSize);
      $srch->addOrder('orrmsg_id','DESC');
      $srch->addMultipleFields( array( 'orrmsg_from_user_id', 'orrmsg_msg',
      'orrmsg_date', 'msg_user.user_name as msg_user_name', 'orrequest_status' ) );

      $rs = $srch->getResultSet();
      $messagesList = FatApp::getDb()->fetchAll($rs);

      $this->set( 'messagesList', $messagesList );
      $this->set('page', $page);
      $this->set('pageCount', $srch->pages());
      $this->set('postedData', $post);

      $startRecord = ($page-1)*$pageSize + 1 ;
      $endRecord = $page * $pageSize;
      $totalRecords = $srch->recordCount();
      if ($totalRecords < $endRecord) { $endRecord = $totalRecords; }
      $json['totalRecords'] = $totalRecords;
      $json['startRecord'] = $startRecord;
      $json['endRecord'] = $endRecord;
      $json['html'] = $this->_template->render( false, false, 'buyer/order-return-request-messages-list.php', true);
      $json['loadMoreBtnHtml'] = $this->_template->render( false, false, 'buyer/order-return-request-messages-list-load-more-btn.php', true);
      FatUtility::dieJsonSuccess($json);
      } */

    public function setUpReturnOrderRequestMessage()
    {
        $orrmsg_orrequest_id = FatApp::getPostedData('orrmsg_orrequest_id', null, '0');

        $frm = $this->getOrderReturnRequestMessageForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            $message = current($frm->getValidationErrors());
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $orrmsg_orrequest_id = FatUtility::int($orrmsg_orrequest_id);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->addCondition('orrequest_id', '=', 'mysql_func_'. $orrmsg_orrequest_id, 'AND', true);
        $srch->addCondition('orrequest_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $srch->joinOrderProducts();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_status',));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED || $requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_WITHDRAWN) {
            $message = Labels::getLabel('MSG_Message_cannot_be_posted_now,_as_order_is_refunded_or_withdrawn.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save return request message[ */
        $returnRequestMsgDataToSave = array(
            'orrmsg_orrequest_id' => $requestRow['orrequest_id'],
            'orrmsg_from_user_id' => $user_id,
            'orrmsg_msg' => $post['orrmsg_msg'],
            'orrmsg_date' => date('Y-m-d H:i:s'),
        );
        $oReturnRequestMsgObj = new OrderReturnRequestMessage();
        $oReturnRequestMsgObj->assignValues($returnRequestMsgDataToSave);
        if (!$oReturnRequestMsgObj->save()) {
            $message = $oReturnRequestMsgObj->getError();
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if (!$orrmsg_id) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendReturnRequestMessageNotification($orrmsg_id, $this->siteLangId)) {
            $message = $emailNotificationObj->getError();
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        //send notification to admin
        $notificationData = array(
            'notification_record_type' => Notification::TYPE_ORDER_RETURN_REQUEST,
            'notification_record_id' => $requestRow['orrequest_id'],
            'notification_user_id' => UserAuthentication::getLoggedUserId(),
            'notification_label_key' => Notification::ORDER_RETURNED_REQUEST_MESSAGE_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel('MSG_NOTIFICATION_COULD_NOT_BE_SENT', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('orrmsg_orrequest_id', $orrmsg_orrequest_id);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function orderFeedback($opId = 0)
    {
        $opId = FatUtility::int($opId);
        if (1 > $opId) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $userId = UserAuthentication::getLoggedUserId();

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinShippingCharges();
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('op_id', '=', 'mysql_func_'. $opId, 'AND', true);
        $srch->addOrder("op_id", "DESC");
        /* $srch->addMultipleFields( array('op_status_id', 'op_selprod_user_id', 'op_selprod_code','op_order_id','op_selprod_id','op_is_batch') ); */
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail) || !(FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0))) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        if (!in_array($opDetail["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = SelProdReview::getBuyerAllowedOrderReviewStatuses();
            $statusNames = array();

            foreach ($statuses as $status) {
                $statusNames[] = $orderStatuses[$status];
            }

            Message::addErrorMessage(sprintf(Labels::getLabel('MSG_Feedback_can_be_placed_', $this->siteLangId), implode(',', $statusNames)));
            CommonHelper::redirectUserReferer();
        }

        if ($opDetail['op_is_batch']) {
            $selProdIdArr = explode('|', $opDetail['op_batch_selprod_id']);
            $selProdId = array_shift($selProdIdArr);
        } else {
            $selProdId = $opDetail['op_selprod_id'];
        }

        if (1 > FatUtility::int($selProdId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $oFeedbackSrch = new SelProdReviewSearch();
        $oFeedbackSrch->doNotCalculateRecords();
        $oFeedbackSrch->doNotLimitRecords();
        $oFeedbackSrch->addCondition('spreview_postedby_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $oFeedbackSrch->addCondition('spreview_order_id', '=', $opDetail['op_order_id']);
        $oFeedbackSrch->addCondition('spreview_selprod_id', '=', 'mysql_func_'. $selProdId, 'AND', true);
        $oFeedbackRs = $oFeedbackSrch->getResultSet();
        if (FatApp::getDb()->fetch($oFeedbackRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_order_feedback', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $canSubmitFeedback = Orders::canSubmitFeedback($userId, $opDetail['op_order_id'], $selProdId);

        if (!$canSubmitFeedback) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_order_feedback', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }


        $frm = $this->getOrderFeedbackForm($opId, $this->siteLangId, $opDetail['op_product_type'], $opDetail['opshipping_fulfillment_type']);
        $this->set('frm', $frm);
        $this->set('opDetail', $opDetail);
        $this->_template->addJs(array('js/jquery.barrating.min.js'));
        $this->_template->render(true, true);
    }

    public function setupOrderFeedback()
    {
        $opId = FatApp::getPostedData('op_id', FatUtility::VAR_INT, 0);
        if (1 > $opId) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $userId = UserAuthentication::getLoggedUserId();

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinShippingCharges();
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('op_id', '=', 'mysql_func_'. $opId, 'AND', true);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(array('op_status_id', 'op_selprod_user_id', 'op_selprod_code', 'op_order_id', 'op_selprod_id', 'op_is_batch', 'op_batch_selprod_id', 'op_product_type', 'opshipping_fulfillment_type', 'op_selprod_product_id'));
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);

        if (!$opDetail || CommonHelper::isMultidimArray($opDetail) || !(FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0))) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        if ($opDetail['op_is_batch']) {
            $selProdIdArr = explode('|', $opDetail['op_batch_selprod_id']);
            $selProdId = array_shift($selProdIdArr);
        } else {
            $selProdId = $opDetail['op_selprod_id'];
        }

        if (1 > FatUtility::int($selProdId)) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        if (!in_array($opDetail["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = SelProdReview::getBuyerAllowedOrderReviewStatuses();
            $statusNames = array();

            foreach ($statuses as $status) {
                $statusNames[] = $orderStatuses[$status];
            }
            $message = sprintf(Labels::getLabel('MSG_Feedback_can_be_placed_', $this->siteLangId), implode(',', $statusNames));
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }


        /* checking Abusive Words[ */
        $enteredAbusiveWordsArr = array();
        if (!Abusive::validateContent(FatApp::getPostedData('spreview_description', FatUtility::VAR_STRING, ''), $enteredAbusiveWordsArr)) {
            if (!empty($enteredAbusiveWordsArr)) {
                $errStr = Labels::getLabel("LBL_Word_{abusiveword}_is/are_not_allowed_to_post", $this->siteLangId);
                $errStr = str_replace("{abusiveword}", '"' . implode(", ", $enteredAbusiveWordsArr) . '"', $errStr);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($errStr);
                }
                Message::addErrorMessage($errStr);
                CommonHelper::redirectUserReferer();
                //FatUtility::dieWithError( Message::getHtml() );
            }
        }
        /* ] */

        $sellerId = $opDetail['op_selprod_user_id'];

        /* $selProdDetail = SellerProduct::getAttributesById($selProdId);
          $productId = FatUtility::int($selProdDetail['selprod_product_id']); */

        $op_selprod_code = explode('|', $opDetail['op_selprod_code']);
        $selProdCode = array_shift($op_selprod_code);
        $selProdCodeArr = explode('_', $selProdCode);
        $productId = array_shift($selProdCodeArr);


        $canSubmitFeedback = Orders::canSubmitFeedback($userId, $opDetail['op_order_id'], $selProdId);

        if (!$canSubmitFeedback) {
            $message = Labels::getLabel('MSG_Already_submitted_order_feedback', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $frm = $this->getOrderFeedbackForm($opId, $this->siteLangId, $opDetail['op_product_type'], $opDetail['opshipping_fulfillment_type']);
        $post = FatApp::getPostedData();

        if (false === MOBILE_APP_API_CALL) {
            $post = $frm->getFormDataFromArray($post);
            if (false === $post) {
                Message::addErrorMessage($frm->getValidationErrors());
                $this->orderFeedback($opId);
                return true;
            }
        }

        $post['spreview_seller_user_id'] = $sellerId;
        $post['spreview_order_id'] = $opDetail['op_order_id'];
        $post['spreview_product_id'] = $productId;
        $post['spreview_selprod_id'] = $selProdId;
        $post['spreview_selprod_code'] = $selProdCode;
        $post['spreview_postedby_user_id'] = $userId;
        $post['spreview_posted_on'] = date('Y-m-d H:i:s');
        $post['spreview_lang_id'] = $this->siteLangId;
        $defaultStatus = FatApp::getConfig('CONF_DEFAULT_REVIEW_STATUS', FatUtility::VAR_INT, 0);
        $post['spreview_status'] = $defaultStatus;

        $selProdReview = new SelProdReview();
        $selProdReview->assignValues($post);

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!$selProdReview->save()) {
            $db->rollbackTransaction();
            $this->orderFeedback($opId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($selProdReview->getError());
            }
            Message::addErrorMessage($selProdReview->getError());
            return true;
        }
        $spreviewId = $selProdReview->getMainTableRecordId();
        $ratingsPosted = FatApp::getPostedData('review_rating');
        $ratingAspects = SelProdRating::getRatingAspectsArr($this->siteLangId, $opDetail['opshipping_fulfillment_type']);


        foreach ($ratingsPosted as $ratingAspect => $ratingValue) {
            if (isset($ratingAspects[$ratingAspect])) {
                $selProdRating = new SelProdRating();
                $ratingRow = array('sprating_spreview_id' => $spreviewId, 'sprating_rating_type' => $ratingAspect, 'sprating_rating' => $ratingValue);
                $selProdRating->assignValues($ratingRow);
                if (!$selProdRating->save()) {
                    Message::addErrorMessage($selProdRating->getError());
                    $db->rollbackTransaction();
                    $this->orderFeedback($opId);
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($selProdRating->getError());
                    }
                    return true;
                }
            }
        }
        
        $db->commitTransaction();
        if ($defaultStatus == SelProdReview::STATUS_APPROVED) {
            $selprodRatObj = new SelProdRating();
            $ratReviewArr = $selprodRatObj->getSelprodAvgRatingReview($opDetail['op_selprod_product_id'], $opDetail['op_selprod_user_id']);
            $dataToUpdate = [
                'selprod_avg_rating' => (isset($ratReviewArr['prod_rating'])) ? $ratReviewArr['prod_rating'] : 0,
                'selprod_review_count' => (isset($ratReviewArr['totReviews'])) ? $ratReviewArr['totReviews'] : 0,
            ];
            
            if (!FatApp::getDb()->updateFromArray(SellerProduct::DB_TBL, $dataToUpdate, array('smt' => 'selprod_product_id = ? AND selprod_user_id = ? ', 'vals' => [$opDetail['op_selprod_product_id'], $opDetail['op_selprod_user_id']]))) {
                Message::addErrorMessage($selprodRatObj->getError());
                $this->orderFeedback($opId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($selprodRatObj->getError());
                }
                return true;
            }
        }
        
        
        $emailNotificationObj = new EmailHandler();
        if ($post['spreview_status'] == SelProdReview::STATUS_APPROVED) {
            $emailNotificationObj->sendBuyerReviewStatusUpdatedNotification($spreviewId, $this->siteLangId);
        }
        $reviewTitle = $post['spreview_title'];
        $reviewTitleArr = preg_split("/[\s,-]+/", $reviewTitle);
        $reviewDesc = $post['spreview_description'];
        $reviewDescArr = preg_split("/[\s,-]+/", $reviewDesc);

        $abusiveWords = Abusive::getAbusiveWords();
        if (!empty(array_intersect($abusiveWords, $reviewTitleArr)) || !empty(array_intersect($abusiveWords, $reviewDescArr))) {
            $emailNotificationObj->sendAdminAbusiveReviewNotification($spreviewId, $this->siteLangId);

            //send notification to admin
            $notificationData = array(
                'notification_record_type' => Notification::TYPE_PRODUCT_REVIEW,
                'notification_record_id' => $spreviewId,
                'notification_user_id' => UserAuthentication::getLoggedUserId(),
                'notification_label_key' => Notification::ABUSIVE_REVIEW_POSTED_NOTIFICATION,
                'notification_added_on' => date('Y-m-d H:i:s'),
            );

            if (!Notification::saveNotifications($notificationData)) {
                $message = Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId);
                Message::addErrorMessage($message);
                $this->orderFeedback($opId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                return true;
            }
        } else {
            $notificationData = array(
                'notification_record_type' => Notification::TYPE_PRODUCT_REVIEW,
                'notification_record_id' => $spreviewId,
                'notification_user_id' => UserAuthentication::getLoggedUserId(),
                'notification_label_key' => Notification::PRODUCT_REVIEW_NOTIFICATION,
                'notification_added_on' => date('Y-m-d H:i:s'),
            );

            if (!Notification::saveNotifications($notificationData)) {
                $message = Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId);
                Message::addErrorMessage($message);
                $this->orderFeedback($opId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($message);
                }
                return true;
            }
        }
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        Message::addMessage(Labels::getLabel('MSG_Feedback_Submitted_Successfully', $this->siteLangId));
        if (isset($post['referrer']) && !empty($post['referrer'])) {
            FatApp::redirectUser($post['referrer']);
        } else {
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'Orders'));
        }
    }

    public function orderReturnRequest(int $op_id)
    {
        $oCancelRequestSrch = new OrderCancelRequestSearch();
        $oCancelRequestSrch->doNotCalculateRecords();
        $oCancelRequestSrch->doNotLimitRecords();
        $oCancelRequestSrch->addCondition('ocrequest_op_id', '=', 'mysql_func_'. $op_id, 'AND', true);
        $oCancelRequestSrch->addCondition('ocrequest_status', '!=', 'mysql_func_'. OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED, 'AND', true);
        $oCancelRequestRs = $oCancelRequestSrch->getResultSet();

        if (FatApp::getDb()->fetch($oCancelRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_cancel_request', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinShopSpecifics();
        $srch->joinSellerProductSpecifics();
        $srch->joinOrderProductSpecifics();
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_RETURN_EXCHANGE_READY_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $srch->addCondition('op_id', '=', 'mysql_func_'. $op_id, 'AND', true);
        $srch->addCondition('opd_extend_from_op_id', '=', 'mysql_func_0', 'AND', true);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(
            array(
                'op_status_id', 'op_id', 'op_qty', 'op_product_type', 'order_date_added', 'opd_sold_or_rented',
                'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age',
                'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age', 'op_delivery_time', 'order_user_id', 'opd_rental_start_date'
            )
        );
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);

        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_NOT_ELIGIBLE_FOR_REFUND', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'orderReturnRequests'));
        }
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && OrderProduct::checkOrderDeliveredMarkedByBuyer($opDetail['op_id'], $opDetail['order_user_id'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_You_have_marked_this_order_delivered._So_You_can_not_place_refund_request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'rentalOrderReturnRequests'));
        }
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && strtotime($opDetail['opd_rental_start_date']) <= strtotime(date('Y-m-d'))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Rental_Start_You_can_not_refund_this_order_now', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'rentalOrderReturnRequests'));
        }


        $action = ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) ? "rentalOrderReturnRequests" : "orderReturnRequests";

        $datediff = time() - strtotime($opDetail['op_delivery_time']);
        $msg = $opDetail['return_age'] . ' ' . Labels::getLabel('Lbl_Day(s)', $this->siteLangId);
        $daysSpent = round($datediff / (60 * 60 * 24)); // Cancel and Return Age in Days

        $getBuyerAllowedOrderReturnStatuses = (array) Orders::getBuyerAllowedOrderReturnStatuses();
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
            $buyerOrderUpdateStatus = unserialize(FatApp::getConfig('CONF_DELIVERED_MARK_STATUS_FOR_BUYER', FatUtility::VAR_STRING, ''));
            $getBuyerAllowedOrderReturnStatuses = array_merge($getBuyerAllowedOrderReturnStatuses, $buyerOrderUpdateStatus);
        }
        if (!in_array($opDetail["op_status_id"], $getBuyerAllowedOrderReturnStatuses)) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = $getBuyerAllowedOrderReturnStatuses;
            $status_names = array();
            foreach ($statuses as $status) {
                $status_names[] = $orderStatuses[$status];
            }
            Message::addErrorMessage(sprintf(Labels::getLabel('MSG_Return_Refund_cannot_placed', $this->siteLangId), implode(',', $status_names)));
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'orderReturnRequests'));
        }

        $oReturnRequestSrch = new OrderReturnRequestSearch();
        $oReturnRequestSrch->doNotCalculateRecords();
        $oReturnRequestSrch->doNotLimitRecords();
        $oReturnRequestSrch->addCondition('orrequest_op_id', '=', 'mysql_func_'. $opDetail['op_id'], 'AND', true);
        $oReturnRequestRs = $oReturnRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oReturnRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Already_submitted_return_request_order', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', $action));
        }

        if ($opDetail['return_age'] < $daysSpent && $opDetail['opd_sold_or_rented'] != applicationConstants::ORDER_TYPE_RENT) {
            Message::addErrorMessage(Labels::getLabel('MSG_You_Can_not_Submit_Return_Request_for_this_order._as_per_return_policy_the_return_period_for_this_product_is', $this->siteLangId) . ' ' . $msg);
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', $action));
        }

        $frm = $this->getOrderReturnRequestForm($this->siteLangId, $opDetail);
        $fld = $frm->getField('orrequest_qty');
        $frm->fill(array('op_id' => $opDetail['op_id']));
        $this->set('frmOrderReturnRequest', $frm);
        $this->_template->render(true, true);
    }

    public function setupOrderReturnRequest()
    {
        $op_id = FatApp::getPostedData('op_id', null, '0');
        $user_id = UserAuthentication::getLoggedUserId();
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinShopSpecifics();
        $srch->joinSellerProductSpecifics();
        $srch->joinOrderProductSpecifics();
        $srch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT, 'cvd');
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_RETURN_EXCHANGE_READY_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $srch->addCondition('opd_extend_from_op_id', '=', 'mysql_func_0', 'AND', true);
        $srch->addCondition('op_id', '=', 'mysql_func_'. $op_id, 'AND', true);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(
            array(
                'order_language_id', 'op_status_id', 'op_id', 'op_qty', 'op_product_type',
                'op_unit_price', 'opcharge_amount', 'order_date_added', 'opd_sold_or_rented',
                'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age', 'op_delivery_time', 'order_user_id', 'opd_rental_start_date'
            )
        );
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);

        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            $message = Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && OrderProduct::checkOrderDeliveredMarkedByBuyer($opDetail['op_id'], $opDetail['order_user_id'])) {
            $action = 'rentalOrders';
            $message = Labels::getLabel('MSG_You_have_marked_this_order_delivered._So_You_can_not_place_refund_request', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && strtotime($opDetail['opd_rental_start_date']) <= strtotime(date('Y-m-d'))) {
            $action = 'rentalOrders';
            $message = Labels::getLabel('MSG_Rental_Start_You_can_not_refund_this_order_now', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }


        $datediff = time() - strtotime($opDetail['op_delivery_time']);
        $action = ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) ? "rentalOrders" : "Orders";
        $msg = $opDetail['return_age'] . ' ' . Labels::getLabel('Lbl_Day(s)', $this->siteLangId);
        $daysSpent = round($datediff / (60 * 60 * 24)); // Cancel and Return Age in Days
        if ($opDetail['return_age'] < $daysSpent && $opDetail['opd_sold_or_rented'] != applicationConstants::ORDER_TYPE_RENT) {
            $message = Labels::getLabel('MSG_You_Can_not_Submit_Return_Request_for_this_order._as_per_return_policy_the_return_period_for_this_product_is', $this->siteLangId) . ' ' . $msg;
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getOrderReturnRequestForm($this->siteLangId, $opDetail);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (abs($opDetail['opcharge_amount']) > 0) {
            $orrequestQty = FatUtility::int($post['orrequest_qty']);
            $volumeDiscountPerItem = abs($opDetail['opcharge_amount']) / $opDetail['op_qty'];
            $amtChargeBackToBuyer = ($opDetail['op_qty'] - $orrequestQty) * $volumeDiscountPerItem;
            $pricePerItemCharged = $opDetail['op_unit_price'] - $volumeDiscountPerItem;
            if ($amtChargeBackToBuyer > ($opDetail['op_unit_price'] - $volumeDiscountPerItem) * abs($orrequestQty)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Order_not_eligible_for_partial_qty_refund', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $getBuyerAllowedOrderReturnStatuses = (array) Orders::getBuyerAllowedOrderReturnStatuses();
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
            $buyerOrderUpdateStatus = unserialize(FatApp::getConfig('CONF_DELIVERED_MARK_STATUS_FOR_BUYER', FatUtility::VAR_STRING, ''));
            $getBuyerAllowedOrderReturnStatuses = array_merge($getBuyerAllowedOrderReturnStatuses, $buyerOrderUpdateStatus);
        }
        if (!in_array($opDetail["op_status_id"], $getBuyerAllowedOrderReturnStatuses)) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
            $statuses = $getBuyerAllowedOrderReturnStatuses;
            $status_names = array();
            foreach ($statuses as $status) {
                $status_names[] = $orderStatuses[$status];
            }
            $message = sprintf(Labels::getLabel('MSG_Return_Refund_cannot_placed', $this->siteLangId), implode(',', $status_names));
            LibHelper::dieJsonError($message);
        }

        $oReturnRequestSrch = new OrderReturnRequestSearch();
        $oReturnRequestSrch->doNotCalculateRecords();
        $oReturnRequestSrch->doNotLimitRecords();
        $oReturnRequestSrch->addCondition('orrequest_op_id', '=', 'mysql_func_'. $opDetail['op_id'], 'AND', true);
        $oReturnRequestRs = $oReturnRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oReturnRequestRs)) {
            $message = Labels::getLabel('MSG_Already_submitted_return_request_order', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }


        $reference_number = $user_id . '-' . time();
        $returnRequestDataToSave = array(
            'orrequest_user_id' => $user_id,
            'orrequest_reference' => $reference_number,
            'orrequest_op_id' => $opDetail['op_id'],
            'orrequest_qty' => FatUtility::int($post['orrequest_qty']),
            'orrequest_returnreason_id' => FatUtility::int($post['orrequest_returnreason_id']),
            'orrequest_type' => FatUtility::int($post['orrequest_type']),
            'orrequest_date' => date('Y-m-d H:i:s'),
            'orrequest_status' => OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING
        );
        $oReturnRequestObj = new OrderReturnRequest();
        $oReturnRequestObj->assignValues($returnRequestDataToSave);
        if (!$oReturnRequestObj->save()) {
            Message::addErrorMessage($oReturnRequestObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $orrequest_id = $oReturnRequestObj->getMainTableRecordId();
        if (!$orrequest_id) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        /* attach file with request [ */

        if (isset($_FILES['file'])) {
            $uploadedFiles = $_FILES['file']['tmp_name'];
            foreach ($uploadedFiles as $fileIndex => $uploadedFile) {
                //$uploadedFile = $_FILES['file']['tmp_name'];
                if (is_uploaded_file($_FILES['file']['tmp_name'][$fileIndex])) {
                    if (filesize($uploadedFile) > 10240000) {
                        $message = Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId);
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError($message);
                        }
                        Message::addErrorMessage($message);
                        FatUtility::dieJsonError(Message::getHtml());
                    }

                    $uploadedFileExt = pathinfo($uploadedFile, PATHINFO_EXTENSION);
                    if (getimagesize($uploadedFile) === false && in_array($uploadedFileExt, array('.zip'))) {
                        $message = Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId);
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError($message);
                        }
                        Message::addErrorMessage($message);
                        FatUtility::dieJsonError(Message::getHtml());
                    }

                    $fileHandlerObj = new AttachedFile();
                    if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'][$fileIndex], AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id, 0, $_FILES['file']['name'][$fileIndex], -1, false)) {
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError($fileHandlerObj->getError());
                        }
                        Message::addErrorMessage($fileHandlerObj->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
            }
        }

        /* ] */

        /* save return request message[ */
        $returnRequestMsgDataToSave = array(
            'orrmsg_orrequest_id' => $orrequest_id,
            'orrmsg_from_user_id' => $user_id,
            'orrmsg_msg' => $post['orrmsg_msg'],
            'orrmsg_date' => date('Y-m-d H:i:s'),
        );

        $oReturnRequestMsgObj = new OrderReturnRequestMessage();
        $oReturnRequestMsgObj->assignValues($returnRequestMsgDataToSave);
        if (!$oReturnRequestMsgObj->save()) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($oReturnRequestMsgObj->getError());
            }
            Message::addErrorMessage($oReturnRequestMsgObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if (!$orrmsg_id) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        /* ] */

        /* adding child order history[ */
        $orderObj = new Orders();
        $orderObj->addChildProductOrderHistory($opDetail['op_id'], $user_id, $opDetail['order_language_id'], FatApp::getConfig("CONF_RETURN_REQUEST_ORDER_STATUS"), Labels::getLabel('LBL_Buyer_Raised_Return_Request', $opDetail['order_language_id']), 1);
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestNotification($orrmsg_id, $opDetail['order_language_id'])) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($oReturnRequestMsgObj->getError());
            }
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        /* $this->set( 'msg', Labels::getLabel('MSG_Your_return_request_submitted', $this->siteLangId) );
          $this->_template->render( false, false, 'json-success.php' ); */
        $notificationType = Notification::ORDER_RETURNED_REQUEST_NOTIFICATION_RENTAL;
        if ($opDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_SALE) {
            $notificationType = Notification::ORDER_RETURNED_REQUEST_NOTIFICATION;
        }

        //send notification to admin
        $notificationData = array(
            'notification_record_type' => Notification::TYPE_ORDER_RETURN_REQUEST,
            'notification_record_id' => $orrequest_id,
            'notification_user_id' => UserAuthentication::getLoggedUserId(),
            'notification_label_key' => $notificationType,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $message = Labels::getLabel('MSG_NOTIFICATION_COULD_NOT_BE_SENT', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $msg = Labels::getLabel('MSG_Your_return_request_submitted', $this->siteLangId);
        if (true === MOBILE_APP_API_CALL) {
            $this->set('msg', $msg);
            $this->_template->render();
        }
        //Message::addMessage($msg);
        //FatUtility::dieJsonSuccess(Message::getHtml());
        $this->set('msg', $msg);
        $this->set('action', $action);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function rewardPoints($convertReward = '')
    {
        $frm = $this->getRewardPointSearchForm($this->siteLangId);
        $frm->fill(array('convertReward' => $convertReward));
        $this->set('frmSrch', $frm);

        $userId = UserAuthentication::getLoggedUserId();

        /* $srch = new UserRewardSearch;
          $srch->joinUser();
          $srch->addCondition('urp.urp_user_id','=',$userId);
          $cnd = $srch->addCondition('urp.urp_date_expiry','=','0000-00-00');
          $cnd->attachCondition('urp.urp_date_expiry','>=',date('Y-m-d'),'OR');
          $srch->addMultipleFields(array('IFNULL(sum(urp.urp_points),0) as totalRewardPoints'));
          $srch->doNotCalculateRecords();
          $srch->doNotLimitRecords();
          $rs = $srch->getResultSet();
          $records = FatApp::getDb()->fetch($rs);
          $this->set('totalRewardPoints',$records['totalRewardPoints']); */

        $this->set('totalRewardPoints', UserRewardBreakup::rewardPointBalance($userId));
        $this->set('convertReward', $convertReward);
        $this->_template->render(true, true);
    }

    public function rewardPointsSearch()
    {
        $userId = UserAuthentication::getLoggedUserId();

        $frm = $this->getRewardPointSearchForm($this->siteLangId);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $convertReward = $post['convertReward'];

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $srch = new UserRewardSearch();
        $srch->joinUser();
        $srch->addCondition('urp.urp_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addOrder('urp.urp_date_added', 'DESC');
        $srch->addOrder('urp.urp_id', 'DESC');
        $srch->addMultipleFields(array('urp.*', 'uc.credential_username'));

        if ($convertReward == 'coupon') {
            $srch->addCondition('urp.urp_used', '=', 'mysql_func_0', 'AND', true);
            $cond = $srch->addCondition('urp.urp_date_expiry', '=', '0000-00-00');
            $cond->attachCondition('urp.urp_date_expiry', '>=', date('Y-m-d'), 'OR');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pagesize);
        }
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('convertReward', $convertReward);
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function generateCoupon()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();

        if (empty($post['rewardOptions'])) {
            Message::addErrorMessage(Labels::getLabel('ERR_Please_select_options', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rewardOptions = str_replace('|', ',', rtrim($post['rewardOptions'], '|'));

        $srch = new UserRewardSearch();
        $srch->joinUser();
        $srch->addCondition('urp.urp_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('urp_id', 'in', array($rewardOptions));
        $srch->addCondition('urp.urp_used', '=', 'mysql_func_0', 'AND', true);
        $cond = $srch->addCondition('urp.urp_date_expiry', '=', '0000-00-00');
        $cond->attachCondition('urp.urp_date_expiry', '>=', date('Y-m-d'), 'OR');
        $srch->addOrder('urp.urp_date_added', 'DESC');
        $srch->addOrder('urp.urp_id', 'DESC');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('sum(urp_points) as totalRewardPoints', 'min(urp.urp_date_expiry) as expiredOn'));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);

        if (empty($records)) {
            Message::addErrorMessage(Labels::getLabel('ERR_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($records['totalRewardPoints'] < FatApp::getConfig('CONF_MIN_REWARD_POINT') || $records['totalRewardPoints'] > FatApp::getConfig('CONF_MAX_REWARD_POINT')) {
            Message::addErrorMessage(Labels::getLabel('ERR_PLEASE_VERIFY_REWARD_CONVERSION_LIMIT', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        $couponData = array(
            'coupon_type' => DiscountCoupons::TYPE_DISCOUNT,
            'coupon_identifier' => Labels::getLabel('LBL_Generated_From_Reward_Point', $this->siteLangId),
            'coupon_code' => uniqid(),
            'coupon_min_order_value' => 1,
            'coupon_discount_in_percent' => applicationConstants::PERCENTAGE,
            'coupon_discount_value' => CommonHelper::convertRewardPointToCurrency($records['totalRewardPoints']),
            'coupon_max_discount_value' => CommonHelper::convertRewardPointToCurrency($records['totalRewardPoints']),
            'coupon_start_date' => date('Y-m-d'),
            'coupon_end_date' => $records['expiredOn'],
            'coupon_uses_count' => 1,
            'coupon_uses_coustomer' => 1,
            'coupon_active' => applicationConstants::ACTIVE,
        );
        $couponObj = new DiscountCoupons();
        $couponObj->assignValues($couponData);
        if (!$couponObj->save()) {
            $db->rollbackTransaction();
            Message::addErrorMessage($couponObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $couponId = $couponObj->getMainTableRecordId();
        if (1 > $couponId) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('ERR_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new DiscountCoupons();
        if (!$obj->addUpdateCouponUser($couponId, $userId)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rewardOptionsArr = explode(',', $rewardOptions);
        foreach ($rewardOptionsArr as $urp_id) {
            $rewardsRecord = new UserRewards($urp_id);
            $rewardsRecord->assignValues(
                array(
                    'urp_used' => 1,
                )
            );
            if (!$rewardsRecord->save()) {
                $db->rollbackTransaction();
                Message::addErrorMessage(Labels::getLabel($rewardsRecord->getError(), $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $db->commitTransaction();

        $this->set('msg', Labels::getLabel('LBL_Successfully_generated_coupon_from_Rewar_points', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function offers()
    {
        $this->_template->render(true, true, 'buyer/offers.php');
    }

    public function searchOffers()
    {
        $offers = DiscountCoupons::getUserCoupons(UserAuthentication::getLoggedUserId(), $this->siteLangId);

        if ($offers) {
            $this->set('offers', $offers);
        } else {
            if (true === MOBILE_APP_API_CALL) {
                $this->set('offers', array());
            } else {
                $this->set('noRecordsHtml', $this->_template->render(false, false, '_partial/no-record-found.php', true));
            }
        }
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'buyer/search-offers.php');
    }

    public function twitterCallback()
    {
        include_once CONF_INSTALLATION_PATH . 'library/APIs/twitteroauth-master/autoload.php';
        $get = FatApp::getQueryStringData();

        if (!empty($get['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
            try {
                $access_token = $twitteroauth->oauth("oauth/access_token", ["oauth_verifier" => $get['oauth_verifier']]);
            } catch (exception $e) {
                $this->set('errors', $e->getMessage());
                $this->_template->render(false, false, 'buyer/twitter-response.php');
                return;
            }

            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"), $access_token['oauth_token'], $access_token['oauth_token_secret']);

            $info = $twitteroauth->get('account/verify_credentials', array("include_entities" => false));
            $anchor_tag = CommonHelper::referralTrackingUrl(UserAuthentication::getLoggedUserAttribute('user_referral_code'));
            $urlapi = "http://tinyurl.com/api-create.php?url=" . $anchor_tag;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlapi);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $shorturl = curl_exec($ch);
            curl_close($ch);
            $anchor_length = strlen($shorturl);

            //$message = substr($shorturl." Twitter Message will go here ",0,(140-$anchor_length-6));
            $message = substr($shorturl . " " . sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_TWITTER_POST_TITLE" . $this->siteLangId), FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId)), 0, 134 - $anchor_length);

            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, 0, 0, $this->siteLangId);
            $error = false;
            $postMedia = false;
            if (!empty($file_row)) {
                $image_path = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
                $image_path = CONF_UPLOADS_PATH . $image_path;
                if (filesize($image_path) <= (5 * 1000000)) { /* Max 5mb size image can be uploaded by Twitter */
                    $handle = fopen($image_path, 'rb');
                    $image = fread($handle, filesize($image_path));
                    fclose($handle);
                    $twitteroauth->setTimeouts(60, 30);
                    try {
                        $result = $twitteroauth->upload('media/upload', array('media' => $image_path));
                        if ($twitteroauth->getLastHttpCode() == 200) {
                            $parameters = array('Name' => FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId), 'status' => $message, 'media_ids' => $result->media_id_string);
                            try {
                                $post = $twitteroauth->post('statuses/update', $parameters);
                                $postMedia = true;
                            } catch (exception $e) {
                                $error = $e->getMessage();
                            }
                        }
                    } catch (exception $e) {;
                        $error = $e->getMessage();
                    }
                }
            }

            if (!$postMedia) {
                $parameters = array('Name' => FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId), 'status' => $message);
                try {
                    $post = $twitteroauth->post('statuses/update', $parameters, false);
                } catch (exception $e) {
                    $error = $e->getMessage();
                }
            }

            $this->set('errors', isset($post->errors) ? $post->errors : $error);
            $this->_template->render(false, false, 'buyer/twitter-response.php');
        }
    }

    public function twitterCallback_old()
    {
        include_once CONF_INSTALLATION_PATH . 'library/APIs/twitter/twitteroauth.php';
        $get = FatApp::getQueryStringData();

        if (!empty($get['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
            // We've got everything we need
            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
            // Let's request the access token
            $access_token = $twitteroauth->getAccessToken($get['oauth_verifier']);
            // Save it in a session var
            $_SESSION['access_token'] = $access_token;
            // Let's get the user's info
            $twitter_info = $twitteroauth->get('account/verify_credentials');
            //$twitter_info->id
            $anchor_tag = CommonHelper::referralTrackingUrl(UserAuthentication::getLoggedUserAttribute('user_referral_code'));
            $urlapi = "http://tinyurl.com/api-create.php?url=" . $anchor_tag;
            /*             * *
             * activate cURL for URL shortening
             * * */

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlapi);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $shorturl = curl_exec($ch);
            curl_close($ch);
            $anchor_length = strlen($shorturl);
            //$message = substr($shorturl." Twitter Message will go here ",0,(140-$anchor_length-6));
            $message = substr($shorturl . " " . sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_TWITTER_POST_TITLE" . $this->siteLangId), FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId)), 0, 134 - $anchor_length);
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, 0, 0, $this->siteLangId);
            $post = '';
            if (!empty($file_row)) {
                $image_path = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
                $image_path = CONF_UPLOADS_PATH . $image_path;
                $handle = fopen($image_path, 'rb');
                $image = fread($handle, filesize($image_path));
                fclose($handle);
                /* $parameters = array('media[]' => "{$image};type=image/jpeg;filename={$image_path}",'status' => $message);
                  $post = $twitteroauth->post('statuses/update_with_media', $parameters, true); */
                $parameters = array('media_type' => 'image/jpeg', 'media' => $image);
                $post = $twitteroauth->post('media/upload', $parameters, true);
            } else {
                $parameters = array('Name' => FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId), 'status' => $message);
                $post = $twitteroauth->post('statuses/update', $parameters, false);
            }
            $this->set('errors', isset($post->errors) ? $post->errors : '');
            $this->_template->render(false, false, 'buyer/twitter-response.php');
        }
    }

    public function shareEarn()
    {
        if (!FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE", FatUtility::VAR_INT, 1)) {
            Message::addErrorMessage(Labels::getLabel('Msg_INVALID_REQUEST', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        if (empty(UserAuthentication::getLoggedUserAttribute('user_referral_code'))) {
            Message::addErrorMessage(Labels::getLabel('Msg_Referral_Code_is_empty', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $get_twitter_url = $_SESSION["TWITTER_URL"] = UrlHelper::generateFullUrl('Buyer', 'twitterCallback');

        try {
            $twitteroauth = new TwitterOAuth(FatApp::getConfig("CONF_TWITTER_API_KEY"), FatApp::getConfig("CONF_TWITTER_API_SECRET"));

            $request_token = $twitteroauth->oauth('oauth/request_token', array('oauth_callback' => $get_twitter_url));

            $_SESSION['oauth_token'] = $request_token['oauth_token'];
            $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
            $twitterUrl = $twitteroauth->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
            $this->set('twitterUrl', $twitterUrl);
        } catch (\Exception $e) {
            $this->set('twitterUrl', false);
        }

        $this->set('referralTrackingUrl', CommonHelper::referralTrackingUrl(UserAuthentication::getLoggedUserAttribute('user_referral_code')));
        $this->set('sharingFrm', $this->getFriendsSharingForm($this->siteLangId));
        $this->_template->addJs(['js/slick.min.js', 'js/tagify.min.js', 'js/tagify.polyfills.min.js']);
        $this->_template->render(true, true);
    }

    public function sendMailShareEarn()
    {
        $post = FatApp::getPostedData();
        $email = $post["email"];
        if (empty($email)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $email = array_unique(array_column(json_decode($email, true), 'value'));
        if (count($email) && !empty($email)) {
            $personalMessage = empty($post['message']) ? "" : "<b>" . Labels::getLabel('Lbl_Personal_Message_From_Sender', $this->siteLangId) . ":</b> " . nl2br($post['message']);
            $emailNotificationObj = new EmailHandler();
            foreach ($email as $email_id) {
                $email_id = trim($email_id);
                if (!CommonHelper::isValidEmail($email_id)) {
                    continue;
                }
                /* email notification handling[ */
                if (!$emailNotificationObj->sendMailShareEarn(UserAuthentication::getLoggedUserId(), $email_id, $personalMessage, $this->siteLangId)) {
                    Message::addErrorMessage(Labels::getLabel("MSG_UNABLE_TO_SEND_EMAIL",$this->siteLangId));
                    FatUtility::dieWithError(Message::getHtml());
                }
                /* ] */
            }
        }
        $this->set('msg', Labels::getLabel('MSG_INVITATION_EMAILS_SENT_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function sendMailShareEarn()
    {
        $post = FatApp::getPostedData();
        $err = '';
        if (!FatUtility::validateMultipleEmails($post["email"], $err)) {
            Message::addErrorMessage($err);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $email = CommonHelper::multipleExplode(array(",", ";", "\t", "\n"), trim($post["email"], ","));
        $email = array_unique($email);
        if (count($email) && !empty($email)) {
            $email = array_unique($email);
            $personalMessage = empty($post['message']) ? "" : "<b>" . Labels::getLabel('Lbl_Personal_Message_From_Sender', $this->siteLangId) . ":</b> " . nl2br($post['message']);
            $emailNotificationObj = new EmailHandler();
            foreach ($email as $email_id) {
                $email_id = trim($email_id);
                if (!CommonHelper::isValidEmail($email_id)) {
                    continue;
                }
                if (!$emailNotificationObj->sendMailShareEarn(UserAuthentication::getLoggedUserId(), $email_id, $personalMessage, $this->siteLangId)) {
                    Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
                    CommonHelper::redirectUserReferer();
                }
            }
        }
        $this->set('msg', Labels::getLabel('MSG_invitation_emails_sent_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    } */

    private function getFriendsSharingForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmShareEarn');
        $fld = $frm->addTextArea(Labels::getLabel('L_Friends_Email', $langId), 'email');
        /* $fld->htmlAfterField = ' <small>(' . Labels::getLabel('L_Use_commas_separate_emails', $langId) . ')</small>'; */
        $fld->requirements()->setRequired();
        $frm->addTextArea(Labels::getLabel('L_Personal_Message', $langId), 'message');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('L_Invite_Your_Friends', $langId));
        return $frm;
    }

    private function getRewardPointSearchForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmRewardPointSearch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'convertReward');
        /* $frm->addTextBox('','keyword');
          $fldSubmit = $frm->addSubmitButton( '', 'btn_submit', Labels::getLabel('LBL_Search',$langId) );
          $fldCancel = $frm->addButton( "", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick'=>'clearSearch();') ); */
        return $frm;
    }

    private function getOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];

        $frm = new Form('frmOrderSrch');
        $frm->addHiddenField('', 'order_type', applicationConstants::PRODUCT_FOR_SALE);
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId)));
        $frm->addSelectBox('', 'status', Orders::getOrderProductStatusArr($langId, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS"))), '', array(), Labels::getLabel('LBL_Status', $langId));
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $langId), 'readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $langId), 'readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Price_Min', $langId) . ' [' . $currencySymbol . ']'));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Price_Max', $langId) . ' [' . $currencySymbol . ']'));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'orderReportType');
        //$fldSubmit->attachField($fldCancel);
        return $frm;
    }

    private function getOrderCancelRequestForm($langId)
    {
        $frm = new Form('frmOrderCancel');
        $orderCancelReasonsArr = OrderCancelReason::getOrderCancelReasonArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Reason_for_cancellation', $langId), 'ocrequest_ocreason_id', $orderCancelReasonsArr, '', array(), Labels::getLabel('LBL_Select_Reason', $langId))->requirements()->setRequired();
        $frm->addTextArea(Labels::getLabel('LBL_Comments', $langId), 'ocrequest_message')->requirements()->setRequired();
        $frm->addHiddenField('', 'op_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send_Request', $langId));
        return $frm;
    }

    private function getOrderReturnRequestForm($langId, $opDetail = array())
    {
        $returnQtyArr = array();
        if (!empty($opDetail)) {
            $op_qty = isset($opDetail["op_qty"]) ? $opDetail["op_qty"] : 1;
            for ($k = 1; $k <= $op_qty; $k++) {
                $returnQtyArr[$k] = $k;
            }
        }
        $frm = new Form('frmOrderReturnRequest', array('enctype' => "multipart/form-data"));

        if (!empty($opDetail) && $opDetail['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
            $frm->addIntegerField(Labels::getLabel('LBL_Return_Qty', $langId), 'orrequest_qty', $opDetail["op_qty"], array('readonly' => 'true', 'class' => 'disabled-input'));
        } else {
            $frm->addSelectBox(Labels::getLabel('LBL_Return_Qty', $langId), 'orrequest_qty', $returnQtyArr, '', array(), '')->requirements()->setRequired();
        }

        $orderReturnReasonsArr = OrderReturnReason::getOrderReturnReasonArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Reason_for_return', $langId), 'orrequest_returnreason_id', $orderReturnReasonsArr, '', array(), Labels::getLabel('LBL_Select_Reason', $langId))->requirements()->setRequired();

        /* if( $opDetail['op_status_id'] != FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS") ){
          $requestTypeArr = OrderReturnRequest::getRequestTypeArr($langId);
          unset($requestTypeArr[OrderReturnRequest::RETURN_REQUEST_TYPE_REPLACE]);
          $frm->addRadioButtons( Labels::getLabel('LBL_Return_Request_Type', $langId), 'orrequest_type', $requestTypeArr, OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND )->requirements()->setRequired();
          } else {
          $frm->addRadioButtons( Labels::getLabel('LBL_Return_Request_Type', $langId), 'orrequest_type', OrderReturnRequest::getRequestTypeArr($langId), OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND )->requirements()->setRequired();
          } */

        // For now untill $requestTypeArr having single value
        $frm->addTextArea(Labels::getLabel('LBL_Comments', $langId), 'orrmsg_msg')->requirements()->setRequired();

        $frm->addHiddenField('', 'orrequest_type', OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND);

        $fileFld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_Images', $langId), 'file[]', array('accept' => 'image/*,.zip', 'multiple' => 'multiple'));
        $fileFld->htmlBeforeField = '<div class="filefield"><span class="filename"></span>';
        $fileFld->htmlAfterField = '</div><span class="form-text text-muted">' . Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId) . '</span>';

        $frm->addHiddenField('', 'op_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send_Request', $langId));
        return $frm;
    }

    private function getOrderFeedbackForm($op_id, $langId, $productType, $fulfillmentType)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmOrderFeedback');

        $ratingAspects = SelProdRating::getRatingAspectsArr($langId, $fulfillmentType);


        foreach ($ratingAspects as $aspectVal => $aspectLabel) {
            $fld = $frm->addSelectBox($aspectLabel, "review_rating[$aspectVal]", array("1" => "1", "2" => "2", "3" => "3", "4" => "4", "5" => "5"), "", array('class' => "star-rating"), Labels::getLabel('L_Rate', $langId));
            $ratingHtml = '';
            for ($ii = 0; $ii < 5; $ii++) {
                $ratingHtml .= '<li></li>';
            }

            $fld->htmlAfterField = '<div class="product-rating product-rating-inline"><ul>' . $ratingHtml . '</ul></div>';

            $fld->requirements()->setRequired(true);
            $fld->setWrapperAttribute('class', 'rating-f');
        }

        $frm->addRequiredField(Labels::getLabel('LBL_Title', $langId), 'spreview_title');
        $frm->addTextArea(Labels::getLabel('LBL_Description', $langId), 'spreview_description')->requirements()->setRequired();
        $frm->addHiddenField('', 'op_id', $op_id);
        $frm->addHiddenField('', 'referrer', CommonHelper::redirectUserReferer(true));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send_Review', $langId));
        return $frm;
    }

    public function getFbToken()
    {
        $userId = UserAuthentication::getLoggedUserId();
        if (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user'])) {
            $redirectUrl = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user'];
            unset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['redirect_user']);
        } else {
            $redirectUrl = UrlHelper::generateUrl('Buyer', 'ShareEarn');
        }


        include_once CONF_INSTALLATION_PATH . 'library/Fbapi.php';

        $config = array(
            'app_id' => FatApp::getConfig('CONF_FACEBOOK_APP_ID', FatUtility::VAR_STRING, ''),
            'app_secret' => FatApp::getConfig('CONF_FACEBOOK_APP_SECRET', FatUtility::VAR_STRING, ''),
        );
        $fb = new Fbapi($config);
        $fbObj = $fb->getInstance();

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            Message::addErrorMessage($e->getMessage());
            FatApp::redirectUser($redirectUrl);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            Message::addErrorMessage($e->getMessage());
            FatApp::redirectUser($redirectUrl);
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                Message::addErrorMessage($helper->getErrorDescription());
                //Message::addErrorMessage($helper->getErrorReason());
            } else {
                Message::addErrorMessage(Labels::getLabel('Msg_Bad_Request', $this->siteLangId));
            }
        } else {
            // The OAuth 2.0 client handler helps us manage access tokens
            $oAuth2Client = $fbObj->getOAuth2Client();

            if (!$accessToken->isLongLived()) {
                try {
                    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    Message::addErrorMessage($helper->getMessage());
                    FatApp::redirectUser($redirectUrl);
                }
            }

            $fbAccessToken = $accessToken->getValue();

            unset($_SESSION['fb_' . FatApp::getConfig("CONF_FACEBOOK_APP_ID") . '_code']);
            unset($_SESSION['fb_' . FatApp::getConfig("CONF_FACEBOOK_APP_ID") . '_access_token']);
            unset($_SESSION['fb_' . FatApp::getConfig("CONF_FACEBOOK_APP_ID") . '_user_id']);

            $userObj = new User($userId);
            $userData = array('user_fb_access_token' => $fbAccessToken);
            $userObj->assignValues($userData);
            if (!$userObj->save()) {
                Message::addErrorMessage(Labels::getLabel("MSG_Token_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
            }
        }
        FatApp::redirectUser($redirectUrl);
    }

    public function addItemsToCart($orderId)
    {
        if (!$orderId) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            return;
        }

        $userId = UserAuthentication::getLoggedUserId();

        $orderObj = new Orders();
        $orderDetail = $orderObj->getOrderById($orderId, $this->siteLangId);
        if (!$orderDetail || ($orderDetail && $orderDetail['order_user_id'] != $userId)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            return;
        }

        $cartObj = new Cart();
        $cartInfo = json_decode($orderDetail['order_cart_data'], true);
        unset($cartInfo['shopping_cart']);
        $outOfStock = false;
        foreach ($cartInfo as $key => $quantity) {
            $keyDecoded = json_decode(base64_decode($key), true);

            $selprod_id = 0;

            if (strpos($keyDecoded, Cart::CART_KEY_PREFIX_PRODUCT) !== false) {
                $selprod_id = FatUtility::int(str_replace(Cart::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
            }
            $selProdStock = SellerProduct::getAttributesById($selprod_id, 'selprod_stock', false);
            if (!$selProdStock && $selProdStock <= 0) {
                $outOfStock = true;
                continue;
            }
            $cartObj->add($selprod_id, $quantity);
        }

        if ($outOfStock) {
            $message = Labels::getLabel('MSG_Product_not_available_or_out_of_stock_so_removed_from_cart_listing', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                $error['status'] = 0;
                $error['msg'] = strip_tags($message);
                $error['cartItemsCount'] = $this->cartItemsCount;
                FatUtility::dieJsonError($error);
            }
            Message::addErrorMessage($message);
            return false;
        }

        $cartObj->removeUsedRewardPoints();
        $cartObj->removeCartDiscountCoupon();
        $cartObj->removeProductShippingMethod();

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        return;
    }

    public function shareEarnUrl()
    {
        $userId = UserAuthentication::getLoggedUserId();
        if (!FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE")) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_This_module_is_not_enabled', $this->siteLangId));
        }
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_referral_code'), true, true);
        if (empty($userInfo['user_referral_code'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_User', $this->siteLangId));
        }

        $referralTrackingUrl = CommonHelper::referralTrackingUrl($userInfo['user_referral_code']);

        $this->set('data', array('trackingUrl' => $referralTrackingUrl));
        $this->_template->render();
    }

    public function orderReceipt($orderId)
    {
        if (empty($orderId)) {
            $message = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }

        $emailObj = new EmailHandler();
        if (!$emailObj->newOrderBuyerAdmin($orderId, $this->siteLangId, false, false)) {
            $message = Labels::getLabel('MSG_Unable_to_notify_customer', $this->siteLangId);
            LibHelper::dieJsonError($message);
        }
        $this->set('msg', Labels::getLabel('MSG_Email_Sent', $this->siteLangId));
        $this->_template->render();
    }

    public function orderTrackingInfo($trackingNumber, $courier, $orderNumber)
    {
        if (empty($trackingNumber) || empty($courier)) {
            $message = Labels::getLabel('MSG_Invalid_request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $shipmentTracking = new ShipmentTracking();
        if (false === $shipmentTracking->init($this->siteLangId)) {
            $message = $shipmentTracking->getError();
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        $shipmentTracking->createTracking($trackingNumber, $courier, $orderNumber);

        if (false === $shipmentTracking->getTrackingInfo($trackingNumber, $courier)) {
            $message = $shipmentTracking->getError();
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $trackingInfo = $shipmentTracking->getResponse();
        $this->set('trackingInfo', $trackingInfo);
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function updatePayment()
    {
        $frm = $this->getTransferBankForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            FatUtility::dieJsonError(current($frm->getValidationErrors()));
        }
        $orderId = $post['opayment_order_id'];

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if (empty($orderInfo) || 1 >= count(array_filter($post))) {
            $msg = Labels::getLabel("MSG_INVALID_REQUEST", $this->siteLangId);
            FatUtility::dieJsonError($msg);
        }

        if (!$orderPaymentObj->addOrderPayment($post["opayment_method"], $post['opayment_gateway_txn_id'], $post["opayment_amount"], $post["opayment_comments"], '', false, 0, Orders::ORDER_PAYMENT_PENDING)) {
            FatUtility::dieJsonError($orderPaymentObj->getError());
        }

        $msg = Labels::getLabel("MSG_REQUEST_SUBMITTED_SUCCESSFULLY", $this->siteLangId);
        FatUtility::dieJsonSuccess($msg);
    }

    public function orderUpdateForm(int $opId)
    {
        $this->set('op_id', $opId);
        $this->set('opDetails', OrderProduct::getAttributesById($opId));
        $this->_template->render(false, false);
    }

    public function orderUpdateCommentForm(int $opId)
    {
        $frm = $this->getOrderStatusUpdateForm();
        $frm->fill(['op_id' => $opId]);
        $this->set('op_id', $opId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    private function getOrderStatusUpdateForm()
    {
        $frm = new Form('frmOrderReturnRequest', array('enctype' => "multipart/form-data"));

        $frm->addTextArea(Labels::getLabel('LBL_Comments', $this->siteLangId), 'order_comment')->requirements()->setRequired();

        $fileFld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_Images', $this->siteLangId), 'file[]', array('accept' => 'image/*,.zip', 'multiple' => 'multiple'));
        $fileFld->htmlBeforeField = '<div class="filefield"><span class="filename"></span>';
        $fileFld->htmlAfterField = '</div><span class="form-text text-muted">' . Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed._You_Can_Upload_multiple_files_At_same_time', $this->siteLangId) . '</span>';

        $frm->addHiddenField('', 'op_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Mark_as_Delivered', $this->siteLangId));
        return $frm;
    }

    public function updateOrderStatus()
    {
        $opId = FatApp::getPostedData('op_id', FatUtility::VAR_INT, 0);
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinSellerProducts();
        $srch->joinShopSpecifics();
        $srch->joinSellerProductSpecifics();
        $srch->joinOrderProductSpecifics();
        $srch->addStatusCondition(OrderStatus::buyerUpdateAllowedStatus());
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. UserAuthentication::getLoggedUserId(), 'AND', true);
        $srch->addCondition('op_id', '=', 'mysql_func_'. $opId, 'AND', true);
        $srch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. applicationConstants::ORDER_TYPE_RENT, 'AND', true);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(
            array(
                'op_status_id', 'order_is_rfq', 'op_id', 'op_product_type', 'opd_sold_or_rented', 'order_date_added',
                'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age', 'order_language_id',
                'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age', 'op_selprod_user_id', 'opd_rental_end_date', 'op_shop_id', 'opd_rental_type', 'selprod_product_id', 'selprod_type', 'opd_mark_rental_return_date', 'opd_duration_price', 'op_qty', 'selprod_user_id', 'order_user_id'
            )
        );
        $rs = $srch->getResultSet();
        $opDetail = FatApp::getDb()->fetch($rs);
        if (!$opDetail || CommonHelper::isMultidimArray($opDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_ACCESS', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $addressId = FatApp::getPostedData('address_id', FatUtility::VAR_INT, 0);
        $frm = $this->getOrderCommentsForm($opDetail, unserialize(FatApp::getConfig("CONF_DELIVERED_MARK_STATUS_FOR_BUYER")));
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(current($frm->getValidationErrors()));
            }
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (isset($post['op_return_fullfillment_type']) && $post['op_return_fullfillment_type'] == OrderProduct::RENTAL_ORDER_RETURN_TYPE_DROP && 1 > $addressId) {
            $message = Labels::getLabel('MSG_Dropoff_Address_is_required', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($post["op_status_id"] == FatApp::getConfig("CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END") && (isset($post['return_qty']) && $post['return_qty'] != $opDetail['op_qty'])) {
            $message = Labels::getLabel('MSG_You_Can_Not_Return_Partial_Qty_without_extending_Order', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* [ UPDATE LATE CHARGES HISTORY FOR THIS ORDER */
        $db = FatApp::getDb();
        $orderObj = new Orders();
        if ($post["op_status_id"] == FatApp::getConfig("CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END")) {
            if (!$orderObj->updateLateChargesHistory($opDetail['op_id'], $this->siteLangId, date('Y-m-d h:i:s'), 0, BuyerLateChargesHistory::STATUS_PENDING)) {
                $db->rollbackTransaction();
                Message::addErrorMessage(Labels::getLabel('MSG_Unable_to_update_late_charges_history', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        /* ] */

        $fullfilementType = (isset($post['op_return_fullfillment_type'])) ? $post['op_return_fullfillment_type'] : "";
        $trackingNumber = (isset($post['tracking_number'])) ? $post['tracking_number'] : "";
        $trackingCourier = (isset($post['tracking_courier'])) ? $post['tracking_courier'] : "";
        $trackingURL = (isset($post['tracking_url'])) ? $post['tracking_url'] : "";
        $returnQty = (isset($post['return_qty'])) ? $post['return_qty'] : 0;
        $commentId = 0;

        if ($post['op_status_id'] == FatApp::getConfig('CONF_RETURN_REQUEST_ORDER_STATUS')) {  /* Mark Order delivered */
            if (!$orderObj->addChildProductOrderHistory($opId, UserAuthentication::getLoggedUserId(), $opDetail["order_language_id"], FatApp::getConfig('CONF_DEFAULT_DEIVERED_ORDER_STATUS'), '', 0, "", 0, true)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($orderObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        if (!$orderObj->addChildProductOrderHistory($opId, UserAuthentication::getLoggedUserId(), $opDetail["order_language_id"], $post['op_status_id'], $post['comments'], 0, $trackingNumber, 0, true, $trackingCourier, [], $fullfilementType, $addressId, $returnQty, $commentId, $trackingURL)) {
            $db->rollbackTransaction();
            Message::addErrorMessage($orderObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        if ($post['op_status_id'] == FatApp::getConfig('CONF_RETURN_REQUEST_ORDER_STATUS')) {
            $requestId = 0;
            $post['return_qty'] = $opDetail['op_qty'];
            if (!$this->setupRentalOrderReturnRequest($opId, $post, $requestId)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($this->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        /* [ upload files if attached */
        $criteria = [
            'afile_type' => AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE,
            'afile_record_id' => $opId
        ];
        $attachedFiles = AttachedFile::getTempFiles($criteria);
        if (!empty($attachedFiles)) {
            foreach ($attachedFiles as $attachFile) {
                unset($attachFile['afile_id']);
                unset($attachFile['afile_downloaded']);
                unset($attachFile['afile_unique']);
                $attachFile['afile_record_subid'] = $commentId;
                if (!FatApp::getDb()->insertFromArray(AttachedFile::DB_TBL, $attachFile, false, array(), $attachFile)) {
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError(FatApp::getDb()->getError());
                    }
                    Message::addErrorMessage(FatApp::getDb()->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
                if ($post['op_status_id'] == FatApp::getConfig('CONF_RETURN_REQUEST_ORDER_STATUS') && $requestId > 0) {
                    $attachFile['afile_type'] = AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT;
                    $attachFile['afile_record_id'] = $requestId;
                    $attachFile['afile_record_subid'] = 0;
                    if (!FatApp::getDb()->insertFromArray(AttachedFile::DB_TBL, $attachFile, false, array(), $attachFile)) {
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError(FatApp::getDb()->getError());
                        }
                        Message::addErrorMessage(FatApp::getDb()->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
            }
            $whr = ['smt' => 'afile_type = ? and afile_record_id = ?', 'vals' => [AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $opId]];
            FatApp::getDb()->deleteRecords(AttachedFile::DB_TBL_TEMP, $whr);
        }


        /* if (isset($_FILES['file'])) {
            $uploadedFiles = $_FILES['file']['tmp_name'];
            foreach ($uploadedFiles as $fileIndex => $uploadedFile) {
                //$uploadedFile = $_FILES['file']['tmp_name'];
                if (is_uploaded_file($_FILES['file']['tmp_name'][$fileIndex])) {
                    if (filesize($uploadedFile) > 10240000) {
                        $message = Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId);
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError($message);
                        }
                        Message::addErrorMessage($message);
                        FatUtility::dieJsonError(Message::getHtml());
                    }

                    $uploadedFileExt = pathinfo($uploadedFile, PATHINFO_EXTENSION);
                    if (getimagesize($uploadedFile) === false && in_array($uploadedFileExt, array('.zip'))) {
                        $message = Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId);
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError($message);
                        }
                        Message::addErrorMessage($message);
                        FatUtility::dieJsonError(Message::getHtml());
                    }

                    $fileHandlerObj = new AttachedFile();
                    
                    if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'][$fileIndex], AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $opId, $commentId, $_FILES['file']['name'][$fileIndex], -1, false)) {
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError($fileHandlerObj->getError());
                        }
                        Message::addErrorMessage($fileHandlerObj->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                    $attachmentId = $fileHandlerObj->getMainTableRecordId();
                    
                    if ($post['op_status_id'] == FatApp::getConfig('CONF_RETURN_REQUEST_ORDER_STATUS') && $requestId > 0 && $attachmentId > 0) {
                        $fileData = AttachedFile::getAttributesById($attachmentId);
                        if (!empty($fileData)) {
                            unset($fileData['afile_id']);
                            $fileData['afile_type'] = AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT;
                            $fileData['afile_record_id'] = $requestId;
                            $fileData['afile_record_subid'] = 0;
                            FatApp::getDb()->insertFromArray(AttachedFile::DB_TBL, $fileData, false, array(), $fileData = array());
                        }
                    }
                }
            }
        } */
        /* ] */

        /* [ CHECK AND UPDATE STATUS OF ATTACHED SERVICES */
        $addonProductIds = Orders::getAddonsIdsByProduct($opId);
        if (!empty($addonProductIds)) {
            foreach ($addonProductIds as $key => $addonProduct) {
                if (!$orderObj->addChildProductOrderHistory($addonProduct, $this->userParentId, $opDetail["order_language_id"], $post['op_status_id'], $post["comments"], 0, $trackingNumber, 0, true, $trackingCourier, [], $fullfilementType, $addressId)) {
                    $db->rollbackTransaction();
                    Message::addErrorMessage($orderObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        }
        /* ] */
        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function downloadBuyerAtatchedFile($recordId, $recordSubid = 0, $afileId = 0)
    {
        $recordId = FatUtility::int($recordId);

        if (1 > $recordId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('buyer'));
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $recordId, $recordSubid);

        if ($afileId > 0) {
            $file_row = AttachedFile::getAttributesById($afileId);
        }

        if (false == $file_row) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('buyer'));
        }
        if (!file_exists(CONF_UPLOADS_PATH . $file_row['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('buyer'));
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    private function getAttachedServicesDetails(array $serviceOpIds): array
    {
        if (empty($serviceOpIds)) {
            return [];
        }
        $srch = $this->getOrderProductSrchObj();
        $srch->addCondition('op_id', 'IN', $serviceOpIds);
        $srch->addCondition('op_status_id', 'IN', OrderStatus::getStatusForMarkOrderReadyForReturn());
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    private function getOrderProductSrchObj()
    {
        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinSellerProducts();
        $srch->addCondition('order_user_id', '=', 'mysql_func_'. UserAuthentication::getLoggedUserId(), 'AND', true);
        $srch->addCondition('opd_sold_or_rented', '=', 'mysql_func_'. applicationConstants::ORDER_TYPE_RENT, 'AND', true);
        $srch->addOrder("op_id", "DESC");
        $srch->addMultipleFields(
            array('op_status_id', 'op_id', 'opd_rental_end_date', 'opd_rental_type', 'op_shop_id', 'selprod_product_id as product_id', 'selprod_user_id', 'order_user_id', 'opd_duration_price', "order_language_id", "selprod_type", "selprod_price", 'opd_mark_rental_return_date', 'op_qty')
        );

        return $srch;
    }

    public function lateChargesHistory()
    {
        $this->set('frmSearch', $this->getLateChargesSearchForm());
        $this->_template->render();
    }

    public function lateChargesSearchListing()
    {
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $user_id = UserAuthentication::getLoggedUserId();

        $srch = BuyerLateChargesHistory::getSearchObject();
        $srch->joinTable(OrderProduct::DB_TBL, 'INNER JOIN', 'op_id = charge_op_id', 'op');
        $srch->addCondition('charge_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        
        $srch->joinTable(Shop::DB_TBL, 'INNER JOIN', 'shop_user_id = op_selprod_user_id', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shoplang_shop_id = shop_id AND shoplang_lang_id ='. $this->siteLangId, 'shopLng');
        
        $srch->addMultipleFields(['charges.*', 'op_invoice_number', 'op_order_id', 'op_id', 'IFNULL(shop_name, shop_identifier) as shop_name']);
        
        if (isset($post['keyword']) && trim($post['keyword']) != '') {
            $cnd = $srch->addCondition('op_invoice_number', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('op_order_id', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('shop_name', 'LIKE', '%'. trim($post['keyword']) .'%');
        }
        
        $srch->setPageNumber($page);
        $srch->addOrder('charge_status', 'ASC');
        $srch->addOrder('charge_op_id', 'DESC');
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $chargesListing = FatApp::getDb()->fetchAll($rs);

        $this->set('chargesListing', $chargesListing);
        $this->set('chargesSmountType', LateChargesProfile::getAmountType($this->siteLangId));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('rentalDurationType', ProductRental::durationTypeArr($this->siteLangId));
        $this->set('statusArr', BuyerLateChargesHistory::chargesStatusArr($this->siteLangId));
        $this->_template->render(false, false);
    }
    
    private function getLateChargesSearchForm()
    {
        $frm = new Form('frmChargesSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->siteLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $frm->addHiddenField('', 'page');
        
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        
        return $frm;
    }

    public function extendOrderForm(int $opId, int $isCommentData = 0)
    {
        $srch = $this->getOrderProductSrchObj();
        $srch->joinSellerProductData();
        $srch->addCondition('op_id', "=", 'mysql_func_'. $opId, 'AND', true);
        $srch->addMultipleFields(['opd_rental_start_date', "selprod_id", "sprodata_rental_stock", "sprodata_rental_buffer_days", "opd_rental_end_date"]);
        $rs = $srch->getResultSet();
        $orderDetails = FatApp::getDb()->fetch($rs);
        if (empty($orderDetails)) {
            Message::addErrorMessage('Invalid Request');
            CommonHelper::redirectUserReferer();
        }
        $post = FatApp::getPostedData();
        if (empty($post) && $isCommentData) {
            Message::addErrorMessage('Form Data is required');
            CommonHelper::redirectUserReferer();
        }
        /* [ UPDATE COMMEBT FORM DATA IN TEMP TABLE */
        if ($isCommentData) {
            $dataToUpdate = [
                'rentop_op_id' => $opId,
                'rentop_op_fullfillment_type' => $post['op_return_fullfillment_type'],
                'rentop_tracking_number' => $post['tracking_number'],
                'rentop_courier' => $post['tracking_courier'],
                'rentop_address_id' => (isset($post['address_id'])) ? $post['address_id'] : 0,
                'rentop_comment' => $post['comments'],
                'rentop_status_updated_datetime' => date('Y-m-d h:i:s'),
            ];
            if (!FatApp::getDb()->insertFromArray(OrderProduct::DB_TBL_RENTAL_TEMP_DATA, $dataToUpdate, false, array(), $dataToUpdate)) {
                Message::addErrorMessage('Unable to store temp data');
                CommonHelper::redirectUserReferer();
            }
        }
        /* ] */
        $endDate = date('Y-m-d', strtotime('+6 month'));
        $productOrderData = OrderProductData::getProductOrders($orderDetails['selprod_id'], $orderDetails['opd_rental_end_date'], $endDate, $orderDetails['sprodata_rental_buffer_days'], $opId);
        $unavailableDates = [];
        if (!empty($productOrderData)) {
            $unavailableDates = ProductRental::prodDisableDates($productOrderData, $orderDetails['sprodata_rental_stock'], $orderDetails['sprodata_rental_buffer_days'], $opId);
        }
        $this->set('unavailableDates', $unavailableDates);

        $qty = $orderDetails['op_qty'] - ((isset($post['return_qty'])) ?  $post['return_qty'] : 0);
        $frm = $this->getExtendOrderForm();
        $extendStartDate  = date('Y-m-d 00:00:00', strtotime('+1 days', strtotime($orderDetails['opd_rental_end_date'])));

        $frm->fill(['quantity' => $qty, 'selprod_id' => $orderDetails['selprod_id'], "rental_start_date" => $extendStartDate, "extend_order" => $opId, 'product_for' => applicationConstants::PRODUCT_FOR_RENT, 'extend_order_from_detail' => 1]);
        $this->set('frm', $frm);
        $this->set('orderDetails', $orderDetails);
        $this->set('opId', $opId);
        $this->set('qty', $qty);
        $this->_template->render(false, false);
    }

    private function getExtendOrderForm()
    {
        $frm = new Form('frmBuyProduct', array('id' => 'frmBuyProduct'));
        $frm->addHiddenField('', 'product_for');
        $frm->addHiddenField('', 'extend_order');
        $frm->addHiddenField('', 'extend_order_from_detail');

        $startDateFld = $frm->addTextBox(Labels::getLabel('LBL_Rental_Start_Date', $this->siteLangId), 'rental_start_date', '', array('readonly' => 'readonly', 'placeholder' => Labels::getLabel('LBL_Rental_Start_Date', $this->siteLangId), 'class' => "rental_start_datetime", 'disabled' => "disabled"));
        $startDateFld->requirements()->setRequired(true);

        $endDateFld = $frm->addTextBox(Labels::getLabel('LBL_Rental_End_Date', $this->siteLangId), 'rental_end_date', '', array('readonly' => 'readonly', 'placeholder' => Labels::getLabel('LBL_Rental_End_Date', $this->siteLangId), "class" => 'rental_end_datetime'));
        $endDateFld->requirements()->setRequired(true);
        $frm->addHiddenField('', 'quantity');
        $frm->addHTML("", 'rental_price_section', "");
        $frm->addSubmitButton("", 'btnAddToCart', Labels::getLabel('LBL_Extend_Order', $this->siteLangId), array('class' => 'add-to-cart add-to-cart--js btn btn-brand', 'data-producttype' => applicationConstants::PRODUCT_FOR_RENT, 'data-wishlistid' => 0, 'data-fullfillment' => 0, 'data-orderdetail' => 1));

        $frm->addHiddenField('', 'selprod_id');
        return $frm;
    }

    private function setupRentalOrderReturnRequest(int $opId, array $post, &$requestId): bool
    {
        $oReturnRequestSrch = new OrderReturnRequestSearch();
        $oReturnRequestSrch->doNotCalculateRecords();
        $oReturnRequestSrch->doNotLimitRecords();
        $oReturnRequestSrch->addCondition('orrequest_op_id', '=', 'mysql_func_'. $opId, 'AND', true);
        $oReturnRequestRs = $oReturnRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oReturnRequestRs)) {
            $this->error = Labels::getLabel('MSG_Already_submitted_return_request_order', $this->siteLangId);
            return false;
        }

        $reference_number = UserAuthentication::getLoggedUserId() . '-' . time();
        $returnRequestDataToSave = array(
            'orrequest_user_id' => UserAuthentication::getLoggedUserId(),
            'orrequest_reference' => $reference_number,
            'orrequest_op_id' => $opId,
            'orrequest_qty' => $post['return_qty'],
            'orrequest_returnreason_id' => FatUtility::int($post['op_return_reason']),
            'orrequest_type' => OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND,
            'orrequest_date' => date('Y-m-d H:i:s'),
            'orrequest_status' => OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING
        );
        $oReturnRequestObj = new OrderReturnRequest();
        $oReturnRequestObj->assignValues($returnRequestDataToSave);
        if (!$oReturnRequestObj->save()) {
            $this->error = $oReturnRequestObj->getError();
            return false;
        }
        $requestId = $oReturnRequestObj->getMainTableRecordId();
        return true;
    }

    public function downloadDigitalFile(int $shopId, int $aFileId, int $fileType, $isPreview = false, $w = 100, $h = 100)
    {
        if (1 > $aFileId || 1 > $shopId) {
            FatUtility::exitWithErrorCode(404);
        }

        $attachFileRow = AttachedFile::getAttributesById($aFileId);

        /* files path[ */
        $folderName = AttachedFile::FILETYPE_SHOP_AGREEMENT_PATH;
        /* ] */

        if (!file_exists(CONF_UPLOADS_PATH . $folderName . $attachFileRow['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('RequestForQuotes', 'RequestView', array($shopId)));
        }

        if ($isPreview) {
            AttachedFile::displayImage($folderName . $attachFileRow['afile_physical_path'], $w, $h);
        } else {
            AttachedFile::downloadAttachment($folderName . $attachFileRow['afile_physical_path'], $attachFileRow['afile_name']);
        }
    }

    public function sendOrderMessage($op_id)
    {
        UserAuthentication::checkLogin();
        $op_id = FatUtility::int($op_id);
        $loggedUserId = UserAuthentication::getLoggedUserId();

        $thread_id = FatUtility::int(Thread::getThreadByRecordId($op_id, 'thread_id'));
        if ($thread_id > 0) {
            $messageRow = Thread::getMsgThreadByRecordId($op_id, Thread::THREAD_TYPE_ORDER_PRODUCT);
            if (!empty($messageRow)) {
                $redirectUrl = UrlHelper::generateFullUrl('account', 'viewMessages', [$messageRow['thread_id'], $messageRow['message_id']]);
                Message::addErrorMessage(Labels::getLabel('LBL_Thread_Already_Created', $this->siteLangId));
                $json['redirectUrl'] = $redirectUrl;
                FatUtility::dieJsonError($json);
            } else {
                $redirectUrl = UrlHelper::generateFullUrl('home');
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                $json['redirectUrl'] = $redirectUrl;
                FatUtility::dieJsonError($json);
            }
        }

        $orderObj = new Orders();
        $data = $orderObj->getOrderProductsByOpId($op_id, $this->siteLangId);

        if (!$data) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            $json['redirectUrl'] = $redirectUrl;
            FatUtility::dieJsonError($json);
        }

        $frm = $this->getSendMessageForm($this->siteLangId);
        $userObj = new User($loggedUserId);
        $loggedUserData = $userObj->getUserInfo(array('user_id', 'user_name', 'credential_username'));
        $frmData = array('op_id' => $op_id);

        $frm->fill($frmData);
        $this->set('frm', $frm);
        $this->set('loggedUserData', $loggedUserData);
        $this->set('data', $data);
        $this->_template->render(false, false);
    }

    public function setupSendOrderMessage()
    {
        UserAuthentication::checkLogin();
        $frm = $this->getSendMessageForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $loggedUserId = UserAuthentication::getLoggedUserId();
        if (false == $post) {
            LibHelper::dieJsonError(current($frm->getValidationErrors()));
        }

        $op_id = FatUtility::int($post['op_id']);
        $orderObj = new Orders();
        $data = $orderObj->getOrderProductsByOpId($op_id, $this->siteLangId);

        if (!$data) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Home'));
        }

        if ($data['op_selprod_user_id'] == $loggedUserId) {
            $message = Labels::getLabel('LBL_You_are_not_allowed_to_send_message', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        $threadObj = new Thread();
        $threadDataToSave = array(
            'thread_subject' => $post['thread_subject'],
            'thread_started_by' => $loggedUserId,
            'thread_start_date' => date('Y-m-d H:i:s'),
            'thread_type' => Thread::THREAD_TYPE_ORDER_PRODUCT,
            'thread_record_id' => $op_id
        );

        $threadObj->assignValues($threadDataToSave);

        if (!$threadObj->save()) {
            $message = Labels::getLabel($threadObj->getError(), $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }
        $thread_id = $threadObj->getMainTableRecordId();

        $threadMsgDataToSave = array(
            'message_thread_id' => $thread_id,
            'message_from' => $loggedUserId,
            'message_to' => $data['op_selprod_user_id'],
            'message_text' => $post['message_text'],
            'message_date' => date('Y-m-d H:i:s'),
            'message_is_unread' => 1,
            'message_deleted' => 0
        );
        if (!$message_id = $threadObj->addThreadMessages($threadMsgDataToSave)) {
            $message = Labels::getLabel($threadObj->getError(), $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatUtility::dieWithError(Message::getHtml());
        }

        /* attach file with request [ */

        if (isset($_FILES['attached_file'])) {
            $uploadedFiles = $_FILES['attached_file']['tmp_name'];
            foreach ($uploadedFiles as $fileIndex => $uploadedFile) {
                if (is_uploaded_file($_FILES['attached_file']['tmp_name'][$fileIndex])) {
                    if (filesize($uploadedFile) > 10240000) {
                        $message = Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId);
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError($message);
                        }
                        Message::addErrorMessage($message);
                        FatUtility::dieJsonError(Message::getHtml());
                    }

                    $fileHandlerObj = new AttachedFile();
                    if (!$res = $fileHandlerObj->saveAttachment($_FILES['attached_file']['tmp_name'][$fileIndex], AttachedFile::FILETYPE_MESSAGE_ATTACHMENTS, $message_id, 0, $_FILES['attached_file']['name'][$fileIndex], -1, false)) {
                        if (true === MOBILE_APP_API_CALL) {
                            LibHelper::dieJsonError($fileHandlerObj->getError());
                        }
                        Message::addErrorMessage($fileHandlerObj->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
            }
        }

        /* ] */

        if ($message_id) {
            $emailObj = new EmailHandler();
            if (!$emailObj->SendMessageNotification($message_id, $this->siteLangId)) {
                LibHelper::dieJsonError($emailObj->getError());
            }
        }
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getSendMessageForm($langId)
    {
        $frm = new Form('frmSendOrderMessage', array('enctype' => "multipart/form-data"));
        $frm->addHiddenField('', 'op_id');

        $fld = $frm->addHtml(Labels::getLabel('LBL_From', $langId), 'send_message_from', '');
        $frm->addHtml(Labels::getLabel('LBL_To', $langId), 'send_message_to', '');
        $frm->addHtml(Labels::getLabel('LBL_About_Product', $langId), 'about_product', '');
        $frm->addRequiredField(Labels::getLabel('LBL_Subject', $langId), 'thread_subject');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Your_Message', $langId), 'message_text', '', array('id' => 'messagetext'));
        $fld->requirements()->setRequired();
        $frm->addFileUpload(Labels::getLabel('LBL_Attach_file', $this->siteLangId), 'attached_file[]', array('accept' => '/*', 'id' => 'attachedFile[]', 'multiple' => 'multiple'));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $langId));
        return $frm;
    }

    public function getError()
    {
        return $this->error;
    }

    public function neworderview()
    {
        $this->_template->render();
    }

    public function uploadCommentFileTemp()
    {
        $post = FatApp::getPostedData();
        $opId = FatApp::getPostedData('op_id', FatUtility::VAR_INT, 0);
        if (false === $post || 1 > $opId) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            }
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (isset($_FILES['file'])) {
            if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                if (filesize($_FILES['file']['tmp_name']) > 10240000) {
                    $message = Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId);
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($message);
                    }
                    Message::addErrorMessage($message);
                    FatUtility::dieJsonError(Message::getHtml());
                }

                $uploadedFileExt = pathinfo($_FILES['file']['tmp_name'], PATHINFO_EXTENSION);
                if (getimagesize($_FILES['file']['tmp_name']) === false && in_array($uploadedFileExt, array('.zip'))) {
                    $message = Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId);
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($message);
                    }
                    Message::addErrorMessage($message);
                    FatUtility::dieJsonError(Message::getHtml());
                }

                $fileHandlerObj = new AttachedFile();
                if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $opId, $this->userParentId, $_FILES['file']['name'], -1, false, 0, 0, 0, true)) {
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($fileHandlerObj->getError());
                    }
                    Message::addErrorMessage($fileHandlerObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $this->set('fileId', $fileHandlerObj->getMainTableRecordId());
            }
        }

        $this->set('msg', Labels::getLabel('MSG_File_Uploaded_Successfully!', $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeTempFile(int $fileId)
    {
        $criteria = [
            'afile_id' => $fileId,
        ];
        $whr = ['smt' => 'afile_type = ? and afile_id = ? and afile_record_subid = ?', 'vals' => [AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $fileId, $this->userParentId]];
        $this->removeTempFileByCriteria($criteria, $whr);
        $this->set('msg', Labels::getLabel('MSG_File_Removed_Successfully!', $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    private function removeTempFileByCriteria(array $criteria, array $whr)
    {
        if (empty($criteria) || empty($whr)) {
            return false;
        }

        $attachedFiles = AttachedFile::getTempFiles($criteria);
        $attachObj = new AttachedFile();
        if (!empty($attachedFiles)) {
            foreach ($attachedFiles as $filesData) {
                $folderPath = $attachObj->fileLocToSave($filesData['afile_type'], CONF_UPLOADS_PATH);
                if (file_exists($folderPath . $filesData['afile_physical_path'])) {
                    unlink($folderPath . $filesData['afile_physical_path']);
                }
            }

            FatApp::getDb()->deleteRecords(AttachedFile::DB_TBL_TEMP, $whr);
        }
        return true;
    }
}
