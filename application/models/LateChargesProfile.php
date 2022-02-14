<?php

class LateChargesProfile extends MyAppModel {

    public const DB_TBL = 'tbl_late_charges_profile';
    public const DB_TBL_PREFIX = 'lcp_';
    public const AMOUNT_TYPE_PERCENTAGE = 1;
    public const AMOUNT_TYPE_FIXED = 2;

    public function __construct($id = 0) {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getAmountType($langId) {
        return [
            static::AMOUNT_TYPE_PERCENTAGE => Labels::getLabel('LBL_Percentage', $langId),
            static::AMOUNT_TYPE_FIXED => Labels::getLabel('LBL_Fixed_Amount', $langId),
        ];
    }

    public static function getSearchObject($isActive = false) {
        $srch = new SearchBase(static::DB_TBL, 'lcpro');
        if ($isActive == true) {
            $srch->addCondition('lcpro.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function setDefaultProfile(int $userId) {
        /// [ CREATE DEFAULT PROFILE
        $dataToInsert = array(
            'lcp_user_id' => $userId,
            'lcp_identifier' => Labels::getLabel('LBL_GLOBAL_LATE_CHARGES', CommonHelper::getLangId()),
            'lcp_active' => 1,
            'lcp_is_default' => 1
        );

        $profileObj = new LateChargesProfile();
        $profileObj->assignValues($dataToInsert);

        if (!$profileObj->save()) {
            Message::addErrorMessage($profileObj->getError());
        }
        return $profileObj->getMainTableRecordId();
    }

    public static function getDefaultProfileId(int $userId, int $profileId = 0) {
        $srch = self::getSearchObject();
        $srch->addCondition('lcp_user_id', '=', $userId);
        $srch->addCondition('lcp_is_default', '=', 1);
        $srch->addFld('lcp_id');
        if (0 < $profileId) {
            $srch->addCondition('lcp_id', '=', $profileId);
        }

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (empty($row)) {
            return 0;
        }

        return $row['lcp_id'];
    }

    public static function getProfileDeatilsByProductId(int $productId, int $userId, int $type = SellerProduct::PRODUCT_TYPE_PRODUCT) {
        $srch = self::getSearchObject();
        $srch->joinTable(LateChargesProfileProduct::DB_TBL, 'INNER JOIN', 'lcp_id = lcptp_lcp_id AND lcptp_product_id = ' . $productId. ' AND lcptp_product_type='. $type);
        $srch->addMultipleFields(['lcpro.*']);
        $srch->addCondition('lcp_user_id', '=', $userId);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            return $row;
        }
        $defaultProfileId = static::getDefaultProfileId($userId);
        return static::getAttributesById($defaultProfileId);
    }
    
    public static function checkAndUpdateProfile(int $productId, int $userId, int $type = SellerProduct::PRODUCT_TYPE_PRODUCT) : bool
    {
        $profileDetails = self::getProfileDeatilsByProductId($productId, $userId, $type);
        if (empty($profileDetails)) {
            return false;
        }
        $data = array(
            'lcptp_user_id' => $userId,
            'lcptp_lcp_id' => $profileDetails['lcp_id'],
            'lcptp_product_id' => $productId,
            'lcptp_product_type' => $type
        );

        $spObj = new LateChargesProfileProduct();
        if (!$spObj->addProduct($data)) {
            return false;
        }
        return true;
    }

}
