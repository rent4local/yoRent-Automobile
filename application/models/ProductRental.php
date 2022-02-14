<?php

class ProductRental extends MyAppModel
{

    public const DB_TBL = 'tbl_seller_products_data';
    public const DB_TBL_PREFIX = 'sprodata_';
    
    public const DB_TBL_BOOKED_STOCK = 'tbl_rental_product_booked_stock';
    public const DB_TBL_RENTAl_STOCK_HOLD = 'tbl_rental_product_stock_hold';
    
    /* public const DURATION_TYPE_HOUR = 1; */
    public const DURATION_TYPE_DAY = 2;
    public const DURATION_TYPE_WEEK = 3;
    public const DURATION_TYPE_MONTH = 4;
    
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'selprod_id', $id);
    }

    public static function getSearchObject(int $langId = 0, bool $isDeleted = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'tspd');
        return $srch;
    }

    public function addUpdateSelProData(array $data): bool
    {
        $db = FatApp::getDb();
        if (!$db->insertFromArray(static::DB_TBL, $data, false, array(), $data)) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    /* [ Product Rental Functionality */

    public function getProductRentalData(): array
    {
        $srch = self::getSearchObject();
        $srch->addCondition('sprodata_selprod_id', '=', $this->mainTableRecordId);
        $srch->addCondition('sprodata_is_for_rent', '=', 1);
        $rs = $srch->getResultSet();
        $prodRentalData = FatApp::getDb()->fetch($rs);
        if (empty($prodRentalData)) {
            $prodRentalData = [];
        }
        return $prodRentalData;
    }

    public function getRentalProductQuantity(string $startDate, string $endDate, int $prodBufferDays = 0, int $extendOpId = 0, int $minShipDuration = 0): int
    {
        $quantity = $unavailableQty = 0;
        /* $processingStatuses = FatApp::getConfig('CONF_PROCESSING_ORDER_STATUS');
        $processingStatuses = unserialize($processingStatuses); */
        
        $processingStatuses = FatApp::getConfig('CONF_PROCESSING_ORDER_STATUS');
        $paymentConfirmStatus = FatApp::getConfig('CONF_DEFAULT_PAID_ORDER_STATUS', FatUtility::VAR_INT, 0);
        $readyForRentalReturn = FatApp::getConfig('CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END', FatUtility::VAR_INT, 0);
        $processingStatuses = unserialize($processingStatuses);
        $processingStatuses = array_merge($processingStatuses, [$paymentConfirmStatus, $readyForRentalReturn]);
        
        $prodstartBufferDays = ($extendOpId > 0) ? 0 : $prodBufferDays;
        $srch = new SearchBase(OrderProduct::DB_TBL, 'op');
        $srch->joinTable(OrderProductData::DB_TBL, 'LEFT JOIN', 'op_id = opd.opd_op_id', 'opd');
        $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', 'op_id = ship.opshipping_op_id', 'ship');
        
        $srch->addCondition('op_selprod_id', '=', $this->mainTableRecordId);
        $srch->addCondition('opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
        
        if ($extendOpId > 0) {
            $srch->addCondition('op_id', '!=', intval($extendOpId));
        }
        $comSrch = clone $srch;
        $srch->addFld('IFNULL(sum(op_qty - op_return_qty), 0) as quantity');
        $srch->addCondition('op_status_id', 'IN', $processingStatuses);
        $srch->addDirectCondition('(("' . $startDate . '" >= opd_rental_start_date AND "' . $startDate . '" <= ((opd_rental_end_date + INTERVAL opshipping_ship_duration DAY) + INTERVAL ' . $prodstartBufferDays . ' DAY)) OR ("' . $endDate . '" >= ((opd_rental_start_date - INTERVAL opshipping_ship_duration DAY) - INTERVAL ' . $prodBufferDays . ' DAY) AND "' . $endDate . '" <= opd_rental_end_date) OR ("' . $startDate . '" <= opd_rental_start_date AND "' . $endDate . '" >=  opd_rental_end_date))');
        
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        
        if (!empty($row) && !empty($row['quantity']) && 0 < $row['quantity']) {
            $quantity = (int) $row['quantity'];
        }
        
        /* $comSrch = clone $srch; */
        $comSrch->joinTable(Orders::DB_TBL_ORDER_STATUS_HISTORY, 'LEFT OUTER JOIN', 'oshistory_op_id = op.op_id', 'opstatus');    
        $comSrch->addCondition('op_status_id', 'IN', [OrderStatus::ORDER_RENTAL_RETURNED, OrderStatus::ORDER_COMPLETED]);
        $comSrch->addDirectCondition('(("' . $startDate . '" >= opd_rental_start_date AND "' . $startDate . '" <= ((oshistory_date_added) + INTERVAL ' . $prodstartBufferDays . ' DAY)) OR ("' . $endDate . '" >= ((opd_rental_start_date - INTERVAL opshipping_ship_duration DAY) - INTERVAL ' . $prodBufferDays . ' DAY) AND "' . $endDate . '" <= opd_rental_end_date) OR ("' . $startDate . '" <= opd_rental_start_date AND "' . $endDate . '" >=  opd_rental_end_date))');
        $comSrch->addMultipleFields(['op_id', 'op_return_qty', 'op_qty']);
        $comSrch->addGroupBy('op_id');
        $comRs = $comSrch->getResultSet();
        $compRows = FatApp::getDb()->fetchAll($comRs);
        
        if (!empty($compRows)) {
            foreach ($compRows as $rowData) {
                $quantity += FatUtility::int($rowData['op_qty'] - $rowData['op_return_qty']);
            }
            /* $quantity = (int) $compRow['quantity']; */
        }
        
        $srch = new SearchBase(SellerRentalProductUnavailableDate::DB_TBL);
        //$srch->addFld('sum(pu_quantity) as quantity');
        $srch->addCondition('pu_selprod_id', '=', intval($this->mainTableRecordId));

        $srch->addDirectCondition('(("' . $startDate . '" >= pu_start_date AND "' . $startDate . '" <= (pu_end_date + INTERVAL ' . $prodBufferDays . ' DAY)) OR ("' . $endDate . '" >= (pu_start_date - INTERVAL ' . $prodBufferDays . ' DAY) AND "' . $endDate . '" <= pu_end_date) OR ("' . $startDate . '" <= pu_start_date AND "' . $endDate . '" >=  pu_end_date))');

        $rs = $srch->getResultSet();
        $unavailableData = FatApp::getDb()->fetchAll($rs);
        if (!empty($unavailableData)) {
            $dates = array();
            foreach ($unavailableData as $anavialdata) {
                if (strtotime($anavialdata['pu_end_date']) < strtotime($endDate)) {
                    $loopEnd = $anavialdata['pu_end_date'];
                } else {
                    $loopEnd = date('Y-m-d', strtotime($endDate));
                }
                $loopEnd = strtotime($loopEnd);
                $loopStart = strtotime($anavialdata['pu_start_date']);
                for ($i = $loopStart; $i <= $loopEnd;) {
                    $date = date('Y-m-d', $i);
                    if (isset($dates[$date])) {
                        $dates[$date] += $anavialdata['pu_quantity'];
                    } else {
                        $dates[$date] = $anavialdata['pu_quantity'];
                    }
                    $i = $i + 86400;
                }
            }
            $unavialQuanitity = 0;
            if (!empty($dates)) {
                $startLoop = strtotime(date('Y-m-d', strtotime($startDate)));
                $endLoop = strtotime(date('Y-m-d', strtotime($endDate)));

                for ($i = $startLoop; $i <= $endLoop;) {
                    $date = date('Y-m-d', $i);
                    if (array_key_exists($date, $dates)) {
                        if ($dates[$date] > $unavialQuanitity) {
                            $unavialQuanitity = $dates[$date];
                        }
                    }
                    $i = $i + 86400;
                }
            }
            $quantity += (int) $unavialQuanitity;
        }
        $tempHoldStock = self::rentalTempHoldStockCount($this->mainTableRecordId, 0, $startDate, $endDate);
        $tempHoldStock = ($tempHoldStock > 0) ? (int) $tempHoldStock : 0;
        $quantity = ($quantity > 0) ? (int) $quantity : 0;
        $alreadyBookedQuantity = $quantity + $tempHoldStock;
        return $alreadyBookedQuantity;
    }

    public function getDurationDiscounts(): array
    {
        $srch = new SellerProductDurationDiscountSearch();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'INNER JOIN', 'spd.sprodata_selprod_id = dd.produr_selprod_id', 'spd');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('produr_rental_duration', 'produr_discount_percent', 'sprodata_duration_type as produr_duration_type', 'produr_id'));
        $srch->addCondition('produr_selprod_id', '=', $this->mainTableRecordId);
        $srch->addOrder('produr_rental_duration', 'ASC');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public static function prodDisableDates(array $productOrders, $availableStock, int $bufferDays = 0, int $oprId = 0, int $minShipDays = 0): array
    {
        $fullyDisableDates = array();
        $disableDatesArray = array();
        $shipBufferDays = $bufferDays + $minShipDays;
        
        if (!empty($productOrders)) {
            foreach ($productOrders as $prodOrder) {
                extract($prodOrder);
                $ship_days = (isset($ship_days)) ? $ship_days : 0;
                if (isset($op_status) && ($op_status == OrderStatus::ORDER_COMPLETED || $op_status == OrderStatus::ORDER_RENTAL_RETURNED)) {
                    $ship_days = 0;
                }
                
                $totalDays = $shipBufferDays + $ship_days;
                $opd_rental_start_date = date("Y-m-d", strtotime($opd_rental_start_date));
                if ((0 < (int) $totalDays) && (($oprId == 0) || ($oprId > 0 && $op_id != $oprId))) {
                    $opd_rental_end_date = date("Y-m-d", strtotime("+" . $totalDays . " day", strtotime($opd_rental_end_date)));
                    $opd_rental_start_date = date("Y-m-d", strtotime("-" . $totalDays . " day", strtotime($opd_rental_start_date)));
                }
                
                while (strtotime($opd_rental_start_date) <= strtotime($opd_rental_end_date)) {
                    if (array_key_exists($opd_rental_start_date, $disableDatesArray)) {
                        $disableDatesArray[$opd_rental_start_date] += $op_qty;
                    } else {
                        $disableDatesArray[$opd_rental_start_date] = $op_qty;
                    }
                    $opd_rental_start_date = date("Y-m-d", strtotime("+1 day", strtotime($opd_rental_start_date)));
                }
            }

            if (!empty($disableDatesArray)) {
                foreach ($disableDatesArray as $key => $disabledate) {
                    if ($disabledate >= $availableStock) {
                        $fullyDisableDates[] = $key;
                    }
                }
            }
        }
        return $fullyDisableDates;
    }

    public static function rentalTempHoldStockCount($selprodId, $userId = 0, $rentalStartDate, $rentalEndDate)
    {
        $selprodId = FatUtility::int($selprodId);
        $rentalStartDate = date('Y-m-d H:i:s', strtotime($rentalStartDate));
        $rentalEndDate = date('Y-m-d H:i:s', strtotime($rentalEndDate));

        $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);

        $srch = new SearchBase(ProductRental::DB_TBL_RENTAl_STOCK_HOLD);
        $srch->doNotCalculateRecords();
        $srch->addOrder('rentpshold_id', 'ASC');
        $srch->addCondition('rentpshold_added_on', '>=', 'mysql_func_DATE_SUB( NOW(), INTERVAL ' . $intervalInMinutes . ' MINUTE )', 'AND', true);
        $srch->addCondition('rentpshold_selprod_id', '=', $selprodId);

        $srch->addDirectCondition('(("' . $rentalStartDate . '" >= rentpshold_rental_start_date AND "' . $rentalStartDate . '" <= rentpshold_rental_end_date) OR ("' . $rentalEndDate . '" >= rentpshold_rental_start_date  AND "' . $rentalEndDate . '" <= rentpshold_rental_end_date) OR ("' . $rentalStartDate . '" <= rentpshold_rental_start_date AND "' . $rentalEndDate . '" >=  rentpshold_rental_end_date))');

        if ($userId > 0) {
            $srch->addCondition('rentpshold_user_id', '=', $userId);
        }
        $srch->addMultipleFields(array('sum(rentpshold_selprod_stock) as stockHold'));
        $srch->setPageNumber(1);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $stockHoldRow = FatApp::getDb()->fetch($rs);
        if ($stockHoldRow == false) {
            return 0;
        }
        return $stockHoldRow['stockHold'];
    }

    public function updateRentalProductStock(int $quantity, string $startDate, string $endDate, bool $decrement = false): bool
    {
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        $currentDate = date('Y-m-d');

        //$allDates = [];
        $db = FatApp::getDb();
        $whr = array('smt' => 'pbs_date < ?', 'vals' => array($currentDate));
        $db->deleteRecords(self::DB_TBL_BOOKED_STOCK, $whr, []); // delete old entries
        while (strtotime($startDate) <= strtotime($endDate)) {
            //$allDates[] = $startDate;
            $query = 'INSERT INTO ' . self::DB_TBL_BOOKED_STOCK . ' (pbs_selprod_id, pbs_date, pbs_quantity) VALUES (' . $this->mainTableRecordId . ', "' . $startDate . '", ' . $quantity . ') ON DUPLICATE KEY UPDATE pbs_quantity = pbs_quantity +' . $quantity . ';';
            if ($decrement) {
                $query = 'INSERT INTO ' . self::DB_TBL_BOOKED_STOCK . ' (pbs_selprod_id, pbs_date,pbs_quantity) VALUES (' . $this->mainTableRecordId . ', "' . $startDate . '", ' . $quantity . ') ON DUPLICATE KEY UPDATE pbs_quantity = pbs_quantity -' . $quantity . ';';
            }
            //echo $query; die();

            if (!$db->query($query)) {
                $this->error = $db->getError();
                return false;
            }
            $startDate = date("Y-m-d", strtotime("+1 day", strtotime($startDate)));
        }
        return true;
    }

    public function getRentalTempHoldByCartKey($userId, string $cartKey): int
    {
        $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);
        $srch = new SearchBase(static::DB_TBL_RENTAl_STOCK_HOLD);
        $srch->doNotCalculateRecords();
        $srch->addOrder('rentpshold_id', 'ASC');
        $srch->addCondition('rentpshold_added_on', '>=', 'mysql_func_DATE_SUB( NOW(), INTERVAL ' . $intervalInMinutes . ' MINUTE )', 'AND', true);
        $srch->addCondition('rentpshold_selprod_id', '=', $this->mainTableRecordId);
        $srch->addCondition('rentpshold_cart_key', '=', $cartKey);
        $srch->addCondition('rentpshold_user_id', '=', $userId);

        $srch->addMultipleFields(array('IFNULL(sum(rentpshold_selprod_stock), 0) as stockHold'));
        $srch->setPageNumber(1);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $stockHoldRow = FatApp::getDb()->fetch($rs);
        if (empty($stockHoldRow)) {
            return 0;
        }
        return $stockHoldRow['stockHold'];
    }

    public static function durationTypeArr(int $langId): array
    {
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
            static::DURATION_TYPE_DAY => Labels::getLabel('LBL_Day(s)', $langId),
            /* static::DURATION_TYPE_HOUR => Labels::getLabel('LBL_Hour(s)', $langId), */
            static::DURATION_TYPE_WEEK => Labels::getLabel('LBL_Week(s)', $langId),
            static::DURATION_TYPE_MONTH => Labels::getLabel('LBL_Month(s)', $langId),
        );
    }

    public static function getLangsData(int $selprodId): array
    {
        $srch = new SearchBase(SellerProduct::DB_TBL_LANG);
        $srch->addCondition('selprodlang_selprod_id', '=', $selprodId);
        $srch->addMultipleFields(array('selprodlang_lang_id', 'selprod_rental_terms'));
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAllAssoc($rs);
    }

    public static function validateDurationDiscountFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredDurationDiscountFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredDurationDiscountFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'produr_rental_duration',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'produr_discount_percent',
            ),
            ImportexportCommon::VALIDATE_FLOAT => array(
                'produr_discount_percent',
            ),
        );
    }

    public static function validateUnavialableDatesFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredUnavialableDatesFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredUnavialableDatesFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'pu_quantity',
            )
        );
    }

}
