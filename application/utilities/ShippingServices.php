<?php

trait ShippingServices
{
    private $keyName;
    private $filename = '';
    private $labelData = '';
    private $trackingNumber = '';
    private $shipmentResponse = '';
    private $error = '';
        
    /**
     * init
     *
     * @return void
     */
    private function init()
    {
        $plugin = new Plugin();
        $this->keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);
        if (false === $this->keyName) {
            FatUtility::dieJsonError($plugin->getError());
        }
        
        $this->shippingService = PluginHelper::callPlugin($this->keyName, [$this->langId], $error, $this->langId);
        if (false === $this->shippingService) {
            FatUtility::dieJsonError($error);
        }

        if (false === $this->shippingService->init()) {
            FatUtility::dieJsonError($this->shippingService->getError());
        }
    }
    
    /**
     * generateLabel - Used for shipstation only.
     *
     * @param  int $opId
     * @return void
     */
    public function generateLabel(int $opId)
    {
        if (false === $this->shippingService->addOrder($opId)) {
            LibHelper::dieJsonError($this->shippingService->getError());
        }
        $order = $this->shippingService->getResponse();
        
        $shipmentApiOrderId = $order['orderId'];
        $requestParam = [
            'orderId' => $order['orderId'],
            'carrierCode' => $order['carrierCode'],
            'serviceCode' => $order['serviceCode'],
            'confirmation' => $order['confirmation'],
            'shipDate' => date('Y-m-d'),// date('Y-m-d', strtotime('+7 day')),
            'weight' => $order['weight'],
            'dimensions' => $order['dimensions'],
        ];

        if (false === $this->shippingService->bindLabel($requestParam)) {
            LibHelper::dieJsonError($this->shippingService->getError());
        }
        
        $response = $this->shippingService->getResponse(false);
        $responseArr = json_decode($response, true);
        $recordCol = ['opship_op_id' => $opId];

        $dataToSave = [
            'opship_orderid' => $shipmentApiOrderId,
            'opship_shipment_id' => $responseArr['shipmentId'],
            'opship_tracking_number' => $responseArr['trackingNumber'],
            'opship_response' => $response,
        ];

        $db = FatApp::getDb();
        if (!$db->insertFromArray(OrderProductShipment::DB_TBL, array_merge($recordCol, $dataToSave), false, array(), $dataToSave)) {
            LibHelper::dieJsonError($db->getError());
        }

        LibHelper::dieJsonSuccess(Labels::getLabel('LBL_SUCCESS', $this->langId));
    }

    /**
     * loadLabelData
     *
     * @param  int $opId
     * @return void
     */
    private function loadLabelData(int $opId): bool
    {
        $orderProductShipmentDetail = OrderProductShipment::getAttributesById($opId);
        if (empty($orderProductShipmentDetail)) {
            $this->error = Labels::getLabel("MSG_NO_LABEL_DATA_FOUND", $this->langId);
            return false;
        }

        $this->shipmentResponse = json_decode($orderProductShipmentDetail['opship_response'], true);
        $this->trackingNumber = $orderProductShipmentDetail['opship_tracking_number'];
        $this->filename = "label-" . $this->trackingNumber . ".pdf";
        $this->labelData = array_key_exists('labelData', $this->shipmentResponse) ? $this->shipmentResponse['labelData'] : $this->shipmentResponse;
        return true;
    }

    /**
     * downloadLabel
     *
     * @param  int $opId
     * @return void
     */
    public function downloadLabel(int $opId)
    {
        if (false === $this->loadLabelData($opId)) {
            LibHelper::dieJsonError($this->error);
        }
        $this->shippingService->downloadLabel($this->labelData, $this->filename);
    }

    /**
     * previewLabel
     *
     * @param  int $opId
     * @return void
     */
    public function previewLabel(int $opId)
    {
        if (false === $this->loadLabelData($opId)) {
            LibHelper::dieJsonError($this->error);
        }
        $this->shippingService->downloadLabel($this->labelData, $this->filename, true);
    }

    /**
     * proceedToShipment
     *
     * @param  int $opId
     * @return void
     */
    public function proceedToShipment(int $opId)
    {
        $db = FatApp::getDb();
        $opSrch = new OrderProductSearch($this->langId, false, true, true);
        $opSrch->joinShippingCharges();
        $opSrch->joinTable(OrderProductShipment::DB_TBL, 'LEFT JOIN', OrderProductShipment::DB_TBL_PREFIX . 'op_id = op.op_id', 'opship');
        $opSrch->addCountsOfOrderedProducts();
        $opSrch->addOrderProductCharges();
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $opSrch->addCondition('op.op_id', '=', $opId);

        $opSrch->addMultipleFields(
            array('opship_orderid', 'opship_tracking_number', 'opshipping_carrier_code', 'opshipping_service_code')
        );

        $opRs = $opSrch->getResultSet();
        $data = $db->fetch($opRs);
        
        if (empty($data["opship_orderid"]) && 'ShipStationShipping' == $this->keyName) {
            $msg = Labels::getLabel("MSG_MUST_GENERATE_LABEL_BEFORE_SHIPMENT", $this->langId);
            LibHelper::dieJsonError($msg);
        }

        if ('ShipStationShipping' == $this->keyName) {
            $opshipmentId = $data["opship_orderid"];
        } else {
            $opshipmentId = $data["opshipping_service_code"];
        }

        if (false === $this->shippingService->loadOrder($opshipmentId)) {
            LibHelper::dieJsonError($this->shippingService->getError());
        }
        $shipmentData = $this->shippingService->getResponse();
        if (array_key_exists('orderStatus', $shipmentData) && ('shipped' == strtolower($shipmentData['orderStatus']) || 'unknown' != strtolower($shipmentData['orderStatus']))) {
            $status = ucwords($shipmentData['orderStatus']);
            $msg = Labels::getLabel("LBL_ALREADY_{STATUS}", $this->langId);
            $msg = CommonHelper::replaceStringData($msg, ['{STATUS}' => $status]);
            LibHelper::dieJsonError($msg);
        }
        
        $requestParam = [
            "orderId" => $data['opship_orderid'],
            "op_id" => $opId,
            "opshipmentId" => $opshipmentId,
            "carrierCode" => $data['opshipping_carrier_code'],
            "shipDate" => date('Y-m-d'),
            "trackingNumber" => $data['opship_tracking_number'],
            "notifyCustomer" => true,
            "notifySalesChannel" => true,
        ];

        if (false === $this->shippingService->proceedToShipment($requestParam)) {
            LibHelper::dieJsonError($this->shippingService->getError());
        }

        $orderInfo = $this->shippingService->getResponse();
        $trackingNumber = ('ShipStationShipping' == $this->keyName) ? $data['opship_tracking_number'] : $orderInfo['tracking_code'];
        $updateData = [
            'opship_op_id' => $opId,
            'opship_order_number' => $orderInfo['orderNumber'],
            "opship_tracking_number" => $trackingNumber,
        ];

        if ('ShipStationShipping' != $this->keyName) {
            $updateData["opship_tracking_url"] = $orderInfo['tracking_url'];
            $updateData["opship_response"] = json_encode($orderInfo);
        }
       
        if (!$db->insertFromArray(OrderProductShipment::DB_TBL, $updateData, false, array(), $updateData)) {
            LibHelper::dieJsonError($db->getError());
        }

        $json = [
            'msg' => Labels::getLabel('LBL_SUCCESS', $this->langId),
            'tracking_number' => $trackingNumber
        ];
  
        LibHelper::dieJsonSuccess($json);
    }
}
