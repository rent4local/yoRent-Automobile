<?php

class AdvertiserController extends AdvertiserBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $this->userPrivilege->canViewPromotions();
        $userId = $this->userParentId;
        $user = new User($userId);

        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'Ad';

        $walletBalance = User::getUserBalance($userId);

        $lowBalWarning = '';
        $errorSet = false;
        /* foreach($promotionList as $promotion){
          if ($promotion["promotion_start_date"]<=date("Y-m-d") && $promotion["promotion_end_date"]>=date("Y-m-d") && ($walletBalance<FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE', FatUtility::VAR_INT, 0) && $errorSet==false)) {
          $errorSet = true;
          Message::addInfo(sprintf(Labels::getLabel('L_Please_maintain_minimum_balance_to_%s', $this->siteLangId), CommonHelper::displaymoneyformat(FatApp::getConfig('CONF_PPC_MIN_WALLET_BALANCE'))));
          }
          } */

        /* Transactions Listing [ */
        $srch = Transactions::getUserTransactionsObj($userId);
        $srch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $srch->getResultSet();
        $transactions = FatApp::getDb()->fetchAll($rs, 'utxn_id');
        /* ] */

        /* Active Promotions [ */
        $activePSrch = $this->getPromotionsSearch(true);
        $rs = $activePSrch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'promotion_id');
        /* ] */

        /* Total Promotions [ */
        $totalPSrch = $this->getPromotionsSearch();
        $rs = $totalPSrch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'promotion_id');
        /* ] */

        $txnObj = new Transactions();
        $txnsSummary = $txnObj->getTransactionSummary($userId, date('Y-m-d'));
        $this->set('txnsSummary', $txnsSummary);
        $this->set('userParentId', $this->userParentId);
        $this->set('userPrivilege', UserPrivilege::getInstance());
        $this->set('totChargedAmount', Promotion::getTotalChargedAmount($userId));
        $this->set('activePromotionChargedAmount', Promotion::getTotalChargedAmount($userId, true));
        $this->set('transactions', $transactions);
        $this->set('txnStatusArr', Transactions::getStatusArr($this->siteLangId));
        $this->set('txnStatusClassArr', Transactions::getStatusClassArr());
        $this->set('activePromotions', $records);
        $this->set('totPromotions', $totalPSrch->recordCount());
        $this->set('totActivePromotions', $activePSrch->recordCount());
        $this->set('lowBalWarning', $lowBalWarning);
        // $this->set('frmRechargeWallet', $this->getRechargeWalletForm($this->siteLangId));
        $this->set('walletBalance', $walletBalance);
        $typeArr = Promotion::getTypeArr($this->siteLangId);
        $this->set('typeArr', $typeArr);
        // $this->set('promotionList', $promotionList);
        // $this->set('promotionCount', $srch->recordCount());
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true);
    }

    public function getPromotionsSearch($active = false)
    {
        $pSrch = $this->searchPromotionsObj();
        $pSrch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
        $pSrch->joinPromotionsLogForCount();
        $pSrch->addMultipleFields(array(
            'pr.promotion_id',
            'ifnull(pr_l.promotion_name,pr.promotion_identifier)as promotion_name',
            'pr.promotion_type',
            'pr.promotion_cpc',
            'pr.promotion_budget',
            'pr.promotion_duration',
            'pr.promotion_start_date',
            'pr.promotion_end_date',
            'pr.promotion_approved',
            'bbl.blocation_promotion_cost',
            'pri.impressions',
            'pri.clicks',
            'pri.orders'
        ));


        if ($active) {
            $pSrch->setDefinedCriteria();
            $pSrch->addCondition('promotion_end_date', '>', date("Y-m-d"));
            $pSrch->addCondition('promotion_approved', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
        } else {
            // $pSrch->addCondition('promotion_deleted', '=', applicationConstants::NO);
        }

        $pSrch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        return $pSrch;
    }

    public function setupPromotion()
    {
        $this->userPrivilege->canEditPromotions();
        $userId = $this->userParentId;
        $frm = $this->getPromotionForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $promotion_record_id = 0;
        $promotionApproved = applicationConstants::NO;
        $bannerData = array();
        $slidesData = array();

        $minBudget = 0;

        switch ($post['promotion_type']) {
            case Promotion::TYPE_SHOP:
                $srch = Shop::getSearchObject(true, $this->siteLangId);
                $srch->addCondition('shop_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
                $srch->setPageSize(1);
                $srch->doNotCalculateRecords();
                $srch->addMultipleFields(array('ifnull(shop_name,shop_identifier) as shop_name', 'shop_id'));
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);
                if (empty($row)) {
                    Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $promotion_record_id = $row['shop_id'];
                $promotionApproved = applicationConstants::YES;
                $minBudget = FatApp::getConfig('CONF_CPC_SHOP', FatUtility::VAR_FLOAT, 0);
                break;
            case Promotion::TYPE_PRODUCT:
                $selProdId = $post['promotion_record_id'];

                $srch = new ProductSearch($this->siteLangId);
                $srch->joinSellerProducts();
                $srch->joinProductToCategory();
                $srch->joinSellerSubscription($this->siteLangId, true);
                $srch->addSubscriptionValidCondition();
                $srch->joinBrands();
                $srch->setPageSize(1);
                $srch->doNotCalculateRecords();
                $srch->addCondition('selprod_id', '=', 'mysql_func_'. $selProdId, 'AND', true);
                $srch->addCondition('selprod_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
                $srch->addMultipleFields(array('selprod_id'));

                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);

                if (empty($row)) {
                    Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $promotion_record_id = $row['selprod_id'];
                $promotionApproved = applicationConstants::YES;
                $minBudget = FatApp::getConfig('CONF_CPC_PRODUCT', FatUtility::VAR_FLOAT, 0);
                break;

            case Promotion::TYPE_BANNER:
                $promotion_record_id = 0;
                $bannerLocationId = Fatutility::int($post['banner_blocation_id']);
                $srch = BannerLocation::getSearchObject($this->siteLangId);
                $srch->doNotCalculateRecords();
                $srch->addMultipleFields(array(
                    'blocation_promotion_cost', 'blocation_promotion_cost_second', 'IFNULL(collection_layout_type, 0) as collection_layout_type', 'blocation_id'
                ));
                $srch->addCondition('blocation_id', '=', 'mysql_func_'. $bannerLocationId, 'AND', true);
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs, 'blocation_id');
                
                $bannerPosition = 0;
                if (!empty($row)) {
                    $minBudget = $row['blocation_promotion_cost'];
                    $bannerPosition = ($row['collection_layout_type'] == Collections::TYPE_BANNER_LAYOUT4) ? $post['banner_position'] : 0;
                    
                    if ($bannerPosition == Collections::BANNER_POSITION_RIGHT) {
                        $minBudget = $row['blocation_promotion_cost_second'];
                    }
                }
                
                $bannerData = array(
                    'banner_blocation_id' => $bannerLocationId,
                    'banner_url' => $post['banner_url'],
                    'banner_target' => applicationConstants::LINK_TARGET_BLANK_WINDOW,
                    'banner_type' => Banner::TYPE_PPC,
                    'banner_active' => applicationConstants::ACTIVE,
                    'banner_position' => $bannerPosition
                );

                break;

            case Promotion::TYPE_SLIDES:
                $promotion_record_id = 0;
                $slidesData = array(
                    'slide_url' => $post['slide_url'],
                    'slide_target' => applicationConstants::LINK_TARGET_BLANK_WINDOW,
                    'slide_type' => Slides::TYPE_PPC,
                    'slide_active' => applicationConstants::ACTIVE
                );
                $minBudget = FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0);
                break;

            default:
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
                break;
        }

        $promotionBudget = Fatutility::float($post['promotion_budget']);
        if ($minBudget > $promotionBudget) {
            Message::addErrorMessage(Labels::getLabel("MSG_Budget_should_be_greater_than_CPC", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $promotionId = $post['promotion_id'];
        if (Promotion::TYPE_PRODUCT == $post['promotion_type'] || $post['promotion_type'] == Promotion::TYPE_SHOP) {
            $srch = Promotion::getSearchObject(0, false);
            $srch->addCondition('promotion_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
            $srch->addCondition('promotion_record_id', '=', 'mysql_func_'. $promotion_record_id, 'AND', true);
            $srch->addCondition('promotion_type', '=', 'mysql_func_'. $post['promotion_type'], 'AND', true);
            $srch->addCondition('promotion_duration', '=', 'mysql_func_'. $post['promotion_duration'], 'AND', true);
            $srch->addCondition('promotion_start_date', '<=', $post['promotion_start_date']);
            $srch->addCondition('promotion_end_date', '>=', $post['promotion_end_date']);
            $srch->addCondition('promotion_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
            /* $srch->addCondition('promotion_end_time','=',$post['promotion_end_time']); */
            $srch->addCondition('promotion_id', '!=', 'mysql_func_'. $promotionId, 'AND', true);
            $rs = $srch->getResultSet();
            /* echo $srch->getQuery();die;  */
            $row = FatApp::getDb()->fetch($rs);
            if (!empty($row)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Promotion_record_with_same_period_already_exists', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        unset($post['banner_id']);
        unset($post['promotion_id']);
        /* unset($post['banner_blocation_id']); */
        unset($post['banner_url']);
        /* unset($post['banner_target']); */
        unset($post['promotion_record_id']);

        $record = new Promotion($promotionId);
        $data = array(
            'promotion_user_id' => $this->userParentId,
            'promotion_added_on' => date('Y-m-d H:i:s'),
            'promotion_active' => applicationConstants::ACTIVE,
            'promotion_record_id' => $promotion_record_id
        );

        if (!$promotionId) {
            $data['promotion_approved'] = $promotionApproved;
        }

        if ($post['promotion_type'] == Promotion::TYPE_SHOP) {
            $data['promotion_cpc'] = $post['promotion_shop_cpc'];
        } elseif ($post['promotion_type'] == Promotion::TYPE_PRODUCT) {
            $data['promotion_cpc'] = $post['promotion_product_cpc'];
        } elseif ($post['promotion_type'] == Promotion::TYPE_SLIDES) {
            $data['promotion_cpc'] = $post['promotion_slides_cpc'];
        } else {
            /* $srch = BannerLocation::getSearchObject($this->siteLangId);
            $srch->addMultipleFields(array(
                'blocation_id',
                'blocation_promotion_cost',
                'ifnull(blocation_name,blocation_identifier) as blocation_name'
            ));
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetchAll($rs, 'blocation_id');
            $data['promotion_cpc'] = $row[$post['banner_blocation_id']]['blocation_promotion_cost']; */
            $data['promotion_cpc'] = $minBudget;
        }
        $data = array_merge($data, $post);
        $record->assignValues($data);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($promotionId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langIdKey => $langName) {
                if ($langIdKey > $newTabLangId) {
                    $newTabLangId = $langIdKey;
                    break;
                }
                /* if(!$row = Promotion::getAttributesByLangId($langId,$promotionId)){
                  $newTabLangId = $langId;
                  break;
                  } */
            }
        } else {
            $promotionId = $record->getMainTableRecordId();
            $newTabLangId = $this->siteLangId;
        }


        switch ($post['promotion_type']) {
            case Promotion::TYPE_BANNER:
                $bannerId = 0;
                $srch = Banner::getSearchObject();
                $srch->addCondition('banner_type', '=', 'mysql_func_'. Banner::TYPE_PPC, 'AND', true);
                $srch->addCondition('banner_record_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
                $srch->addMultipleFields(array('banner_id'));
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);

                if ($row) {
                    $bannerId = $row['banner_id'];
                }

                $bannerRecord = new Banner($bannerId);
                $bannerData['banner_record_id'] = $promotionId;
                $bannerRecord->assignValues($bannerData);

                if (!$bannerRecord->save()) {
                    Message::addErrorMessage($bannerRecord->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
                break;

            case Promotion::TYPE_SLIDES:
                $slideId = 0;
                $srch = Slides::getSearchObject();
                $srch->addCondition('slide_type', '=', 'mysql_func_'. Slides::TYPE_PPC, 'AND', true);
                $srch->addCondition('slide_record_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
                $srch->addMultipleFields(array('slide_id'));
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);
                if ($row) {
                    $slideId = $row['slide_id'];
                }

                $slideRecord = new Slides($slideId);
                $slidesData['slide_record_id'] = $promotionId;
                $slideRecord->assignValues($slidesData);

                if (!$slideRecord->save()) {
                    Message::addErrorMessage($slideRecord->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
                break;
        }

        $notificationData = array(
            'notification_record_type' => Notification::TYPE_PROMOTION,
            'notification_record_id' => $promotionId,
            'notification_user_id' => $this->userParentId,
            'notification_label_key' => Notification::PROMOTION_APPROVAL_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s')
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('promotionId', $promotionId);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_SETUP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupPromotionLang()
    {
        $this->userPrivilege->canEditPromotions();
        $post = FatApp::getPostedData();
        $userId = $this->userParentId;

        $promotionId = $post['promotion_id'];
        $langId = $post['lang_id'];

        if ($promotionId == 0 || $langId == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $promotionData = Promotion::getAttributesById($promotionId, array('promotion_user_id'));
        if (!$promotionData || ($promotionData && $promotionData['promotion_user_id'] != $userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getPromotionLangForm($promotionId, $langId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['promotion_id']);
        unset($post['lang_id']);
        $data = array(
            'promotionlang_lang_id' => $langId,
            'promotionlang_promotion_id' => $promotionId,
            'promotion_name' => $post['promotion_name']
        );

        $obj = new Promotion($promotionId);
        if (!$obj->updateLangData($langId, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Promotion::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($promotionId)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $promotionType = Promotion::getAttributesById($promotionId, array('promotion_type'));
        if ($promotionType['promotion_type'] == Promotion::TYPE_SHOP || $promotionType['promotion_type'] == Promotion::TYPE_PRODUCT) {
            $this->set('noMediaTab', 'noMediaTab');
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langIdKey => $langName) {
            if ($langIdKey > $langId) {
                $newTabLangId = $langIdKey;
                break;
            }
            /* if(!$row = Promotion::getAttributesByLangId($langIdKey,$promotionId)){
              $newTabLangId = $langId;
              break;
              } */
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->siteLangId));
        $this->set('promotionId', $promotionId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function promotionUpload()
    {
        $this->userPrivilege->canEditPromotions();
        $userId = $this->userParentId;
        $post = FatApp::getPostedData();

        $promotionId = FatUtility::int($post['promotion_id']);
        $promotionType = FatUtility::int($post['promotion_type']);
        $langId = FatUtility::int($post['lang_id']);
        $bannerScreen = FatUtility::int($post['banner_screen']);

        $allowedTypeArr = array(
            Promotion::TYPE_BANNER,
            Promotion::TYPE_SLIDES
        );


        if (1 > $promotionId || !in_array($promotionType, $allowedTypeArr)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->siteLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }
        

        $recordId = 0;
        $attachedFileType = 0;

        $srch = new PromotionSearch($this->siteLangId);
        $srch->addCondition('promotion_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
        $srch->addCondition('promotion_user_id', '=', 'mysql_func_'. $userId, 'AND', true);

        switch ($promotionType) {
            case Promotion::TYPE_BANNER:
                $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
                $rs = $srch->getResultSet();
                $promotionDetails = FatApp::getDb()->fetch($rs);
                $recordId = $promotionDetails['banner_id'];
                $attachedFileType = AttachedFile::FILETYPE_BANNER;
                break;
            case Promotion::TYPE_SLIDES:
                $srch->joinSlides();
                $rs = $srch->getResultSet();
                $promotionDetails = FatApp::getDb()->fetch($rs);
                $recordId = $promotionDetails['slide_id'];
                $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
                break;
        }

        if (1 > $recordId || 1 > $attachedFileType) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $db = FatApp::getDb();
        $db->startTransaction();
        $fileHandlerObj = new AttachedFile();

        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], $attachedFileType, $recordId, 0, $_FILES['cropped_image']['name'], -1, true, $langId, '', $bannerScreen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }


        /* if($promotionDetails['promotion_approved']==applicationConstants::YES){ */
        $dataToUpdate = array(
            'promotion_approved' => applicationConstants::NO
        );
        $record = new Promotion($promotionId);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            $db->rollbackTransaction();
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $objEmailHandler = new EmailHandler();
        $objEmailHandler->sendPromotionApprovalRequestAdmin($this->siteLangId, $userId, $promotionDetails);

        $notificationData = array(
            'notification_record_type' => Notification::TYPE_PROMOTION,
            'notification_record_id' => $promotionId,
            'notification_user_id' => $this->userParentId,
            'notification_label_key' => Notification::PROMOTION_APPROVAL_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s')
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* } */
        $db->commitTransaction();

        $fileName = $_FILES['cropped_image']['name'];
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = strlen($fileName) > 10 ? substr($fileName, 0, 10) . '.' . $ext : $fileName;
        Message::addMessage($fileName . " " . Labels::getLabel('MSG_File_uploaded_successfully_and_send_it_for_admin_approval', $this->siteLangId));

        $this->set('promotionId', $promotionId);
        $this->set('file', $_FILES['cropped_image']['name']);
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function searchPromotions()
    {
        $this->userPrivilege->canViewPromotions();
        $userId = $this->userParentId;
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getPromotionSearchForm($this->siteLangId);

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $frmSearch->getFormDataFromArray($data);
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);

        $srch = $this->searchPromotionsObj();

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('pr.promotion_identifier', 'like', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('pr_l.promotion_name', 'like', '%' . $post['keyword'] . '%');
        }

        $type = FatApp::getPostedData('type', FatUtility::VAR_INT, '-1');
        if ($type != '-1') {
            $srch->addCondition('promotion_type', '=', 'mysql_func_'. $type, 'AND', true);
        }

        $active_promotion = FatApp::getPostedData('active_promotion', FatUtility::VAR_INT, '-1');
        if ($active_promotion != '-1') {
            $srch->addCondition('promotion_active', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
            $srch->addCondition('promotion_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
            $srch->addCondition('promotion_end_date', '>', date("Y-m-d"));
            $srch->addCondition('promotion_approved', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
        }

        $dateFrom = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        $dateTo = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');

        if (!empty($dateFrom) || (!empty($dateTo))) {
            $srch->addDateCondition($dateFrom, $dateTo);
        }

        /* if( !empty($dateTo) ) {
          $srch->addDateToCondition($dateTo, $dateFrom);
          } */
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'promotion_id');
        $promotionBudgetDurationArr = Promotion::getPromotionBudgetDurationArr($this->siteLangId);

        $this->set('arrYesNo', applicationConstants::getYesNoArr($this->siteLangId));
        $this->set('arrYesNoClassArr', applicationConstants::getYesNoClassArr());
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->siteLangId));
        $this->set('canEdit', $this->userPrivilege->canEditPromotions(0, true));
        $this->set('promotionBudgetDurationArr', $promotionBudgetDurationArr);
        $this->set('arr_listing', $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('userId', $userId);
        $this->set('typeArr', Promotion::getTypeArr($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function searchPromotionsObj()
    {
        $srch = new PromotionSearch($this->siteLangId);
        $srch->addMultipleFields(array(
            'promotion_id',
            'promotion_budget',
            'promotion_duration',
            'promotion_type',
            'IFNULL(promotion_name,promotion_identifier) as promotion_name',
            'promotion_start_date',
            'promotion_end_date',
            'promotion_start_time',
            'promotion_end_time',
            'promotion_active',
            'promotion_approved',
            'promotion_active'
        ));
        $srch->addCondition('promotion_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        $srch->addCondition('promotion_user_id', '=', 'mysql_func_'. $this->userParentId, 'AND', true);
        $srch->addOrder('promotion_id', 'DESC');
        return $srch;
    }

    public function getTypeData($promotionId, $promotionType = 0)
    {
        $promotionType = FatUtility::int($promotionType);
        $promotionId = FatUtility::int($promotionId);

        $userId = $this->userParentId;

        if (1 > $promotionType) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $label = '';
        $value = 0;
        switch ($promotionType) {
            case Promotion::TYPE_SHOP:
                $srch = Shop::getSearchObject(true, $this->siteLangId);
                $srch->addCondition('shop_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
                $srch->setPageSize(1);
                $srch->doNotCalculateRecords();
                $srch->addMultipleFields(array('ifnull(shop_name,shop_identifier) as shop_name', 'shop_id'));
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);
                if (empty($row)) {
                    Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $label = $row['shop_name'];
                $value = $row['shop_id'];
                break;

            case Promotion::TYPE_PRODUCT:
                if ($promotionId > 0) {
                    $row = Promotion::getAttributesById($promotionId, array(
                                'promotion_record_id'
                    ));

                    $srch = new PromotionSearch($this->siteLangId);
                    $srch->joinProducts();
                    $srch->addCondition('selprod_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
                    $srch->addCondition('selprod_id', '=', 'mysql_func_'. $row['promotion_record_id'], 'AND', true);
                    $srch->setPageSize(1);
                    $srch->doNotCalculateRecords();
                    $srch->addMultipleFields(array(
                        'selprod_id',
                        'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
                        'ifnull(product_name,product_identifier)as product_name'
                    ));
                    $rs = $srch->getResultSet();
                    $row = FatApp::getDb()->fetch($rs);
                    if (!empty($row)) {
                        $variantStr = '';
                        $options = SellerProduct::getSellerProductOptions($row['selprod_id'], true, $this->siteLangId);
                        if (is_array($options) && count($options)) {
                            foreach ($options as $op) {
                                $variantStr .= '(' . $op['option_name'] . ': ' . $op['optionvalue_name'] . ')';
                            }
                        }
                        $label = ($row['selprod_title'] != '') ? $row['selprod_title'] . $variantStr : $row['product_name'] . $variantStr;
                        $value = $row['selprod_id'];
                    }
                }
                break;
        }

        $this->set('promotionType', $promotionType);
        $this->set('label', $label);
        $this->set('value', $value);
        $this->_template->render(false, false, 'json-success.php', false, false);
    }

    public function promotions()
    {
        $this->userPrivilege->canViewPromotions();
        $data = FatApp::getPostedData();
        $frmSearchPromotions = $this->getPromotionSearchForm($this->siteLangId);
        if ($data) {
            $frmSearchPromotions->fill($data);
        }
        $userId = $this->userParentId;
        $srch = new PromotionSearch($this->siteLangId);
        $srch->addMultipleFields(array(
            'promotion_id'
        ));
        $srch->addCondition('promotion_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('promotion_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        $srch->addCondition('promotion_active', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
        $srch->addCondition('promotion_end_date', '>=', date("Y-m-d"));
        $srch->addOrder('promotion_id', 'DESC');

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'promotion_id');

        $this->_template->addJs(array('js/jquery.datetimepicker.js'), false);

        $this->_template->addJs('js/cropper.js');
        $this->_template->addJs('js/cropper-main.js');
        $this->set('canEdit', $this->userPrivilege->canEditPromotions(0, true));
        $this->set("frmSearchPromotions", $frmSearchPromotions);
        $this->set("records", $records);
        $this->_template->render(true, true);
    }

    public function promotionCharges()
    {
        $this->userPrivilege->canViewPromotions();
        $this->_template->render(true, true);
    }

    public function searchPromotionCharges()
    {
        $this->userPrivilege->canViewPromotions();
        $userId = $this->userParentId;
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $prmSrch = new SearchBase(Promotion::DB_TBL_CHARGES, 'tpc');
        $prmSrch->joinTable(Promotion::DB_TBL, 'INNER JOIN', 'pr.' . Promotion::DB_TBL_PREFIX . 'id = tpc.' . Promotion::DB_TBL_CHARGES_PREFIX . 'promotion_id', 'pr');
        $prmSrch->addCondition('pr.promotion_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $prmSrch->addMultipleFields(array(
            'promotion_id',
            'promotion_type',
            'promotion_identifier',
            'sum(pcharge_charged_amount) as totChargedAmount',
            'sum(pcharge_clicks) as totClicks',
            'pcharge_date'
        ));
        $prmSrch->addGroupBy('promotion_id');
        $prmSrch->addOrder('tpc.' . Promotion::DB_TBL_CHARGES_PREFIX . 'id', 'desc');
        $prmSrch->setPageNumber($page);
        $prmSrch->setPageSize($pagesize);
        $rs = $prmSrch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $prmSrch->pages());
        $this->set("recordCount", $prmSrch->recordCount());
        $typeArr = Promotion::getTypeArr($this->siteLangId);
        $this->set('typeArr', $typeArr);
        $this->set("pageSize", $pagesize);
        $this->set("page", $page);
        $this->_template->render(false, false);
    }

    public function promotionForm($promotionId = 0)
    {
        $userId = $this->userParentId;
        $promotionId = FatUtility::int($promotionId);

        $promotionDetails = array();
        $promotionType = 0;
        if ($promotionId) {
            $srch = new PromotionSearch($this->siteLangId);
            $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
            $srch->joinSlides($this->siteLangId);
            if (User::isSeller()) {
                $srch->joinShops($this->siteLangId, false, false);
                $srch->addFld(array(
                    'ifnull(shop_name,shop_identifier) as promotion_shop'
                ));
            }
            $srch->addCondition('promotion_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
            $srch->addCondition('promotion_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
            $srch->addMultipleFields(array(
                'promotion_id',
                'promotion_identifier',
                'promotion_user_id',
                'promotion_type',
                'promotion_budget',
                'promotion_cpc',
                'promotion_duration',
                'promotion_start_date',
                'promotion_end_date',
                'promotion_start_time',
                'promotion_end_time',
                'promotion_active',
                'promotion_approved',
                'banner_url',
                'banner_target',
                'banner_blocation_id',
                'slide_url',
                'slide_target',
                'banner_position'
            ));
            $rs = $srch->getResultSet();
            $promotionDetails = FatApp::getDb()->fetch($rs);
            $promotionType = $promotionDetails['promotion_type'];
            if ($promotionDetails) {
                $promotionDetails['promotion_start_time'] = date('H:i', strtotime($promotionDetails['promotion_start_time']));
                $promotionDetails['promotion_end_time'] = date('H:i', strtotime($promotionDetails['promotion_end_time']));
                if ($promotionDetails['promotion_type'] == Promotion::TYPE_SHOP) {
                    $promotionDetails['promotion_shop_cpc'] = $promotionDetails['promotion_cpc'];
                } elseif ($promotionDetails['promotion_type'] == Promotion::TYPE_PRODUCT) {
                    $promotionDetails['promotion_product_cpc'] = $promotionDetails['promotion_cpc'];
                } elseif ($promotionDetails['promotion_type'] == Promotion::TYPE_SLIDES) {
                    $promotionDetails['promotion_slides_cpc'] = $promotionDetails['promotion_cpc'];
                }
            }
        }
        
        $srch = BannerLocation::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(array(
            'blocation_id',
            'IFNULL(collection_layout_type, 0) as collection_layout_type'
        ));
        $rs = $srch->getResultSet();
        $bannerLayoutData = FatApp::getDb()->fetchAll($rs, 'blocation_id');
        
        $frm = $this->getPromotionForm($promotionId);
        $frm->fill($promotionDetails);

        $this->set('frm', $frm);
        $this->set('promotionId', $promotionId);
        $this->set('promotionType', $promotionType);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->set('bannerLayoutData', $bannerLayoutData);
        $this->_template->render(false, false);
    }

    public function promotionLangForm($promotionId = 0, $langId = 0, $autoFillLangData = 0)
    {
        $promotionId = FatUtility::int($promotionId);
        $langId = FatUtility::int($langId);

        if ($promotionId == 0 || $langId == 0) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }

        $langFrm = $this->getPromotionLangForm($promotionId, $langId);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Promotion::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($promotionId, $langId);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = Promotion::getAttributesByLangId($langId, $promotionId);
        }

        if ($langData) {
            $langFrm->fill($langData);
        }

        $promotionType = 0;
        $row = Promotion::getAttributesById($promotionId, array('promotion_type'));
        if (!empty($row)) {
            $promotionType = $row['promotion_type'];
        }

        $this->set('languages', Language::getAllNames());
        $this->set('promotionId', $promotionId);
        $this->set('promotion_lang_id', $langId);
        $this->set('promotionType', $promotionType);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false);
    }

    public function promotionMediaForm($promotionId = 0)
    {
        $userId = $this->userParentId;
        $promotionId = FatUtility::int($promotionId);

        if (1 > $promotionId) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }

        $promotionType = 0;

        $srch = new PromotionSearch($this->siteLangId);
        $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
        $srch->joinSlides();
        $srch->addCondition('promotion_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
        $srch->addCondition('promotion_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addMultipleFields(array(
            'promotion_id',
            'promotion_type',
            'banner_id',
            'blocation_banner_width',
            'blocation_banner_height',
            'slide_id',
            'IFNULL(collection_layout_type, 0) as collection_layout_type',
            'IFNULL(banner_position, 0) as banner_position'
        ));
        $rs = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetch($rs);
        if (empty($promotionDetails)) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }
        $promotionType = $promotionDetails['promotion_type'];

        $recordId = 0;
        $attachedFileType = 0;
        $activeTheme = applicationConstants::getActiveTheme();
        switch ($promotionType) {
            case Promotion::TYPE_BANNER:
                $bannerSizeArr = imagesSizes::getBannersDimensions();
                $bannerSizeArr = (isset($bannerSizeArr[$activeTheme])) ? $bannerSizeArr[$activeTheme] : $bannerSizeArr[imagesSizes::THEME_DEFAULT];
                $bannerSizeArr = isset($bannerSizeArr[$promotionDetails['collection_layout_type']]) ? $bannerSizeArr[$promotionDetails['collection_layout_type']]:[];
                
                if ($promotionDetails['collection_layout_type'] == Collections::TYPE_BANNER_LAYOUT4) {
                    $bannerSizeArr = $bannerSizeArr[$promotionDetails['banner_position']];
                }
                
                $imgDetail = Banner::getAttributesById($promotionDetails['banner_id']);
                $attachedFileType = AttachedFile::FILETYPE_BANNER;
                $recordId = $promotionDetails['banner_id'];
                break;
            case Promotion::TYPE_SLIDES:
                $bannerSizeArr = imagesSizes::heroSlideImageSizeArr();
                $bannerSizeArr = (isset($bannerSizeArr[$activeTheme])) ? $bannerSizeArr[$activeTheme] : $bannerSizeArr[imagesSizes::THEME_DEFAULT];
            
                $imgDetail = Slides::getAttributesById($promotionDetails['slide_id']);
                $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
                $recordId = $promotionDetails['slide_id'];
                break;
        }

        $mediaFrm = $this->getPromotionMediaForm($promotionId, $promotionType);
        /* $bannerWidth = '1200';
        $bannerHeight = '360';
        if ($promotionType == Promotion::TYPE_BANNER) {
            $bannerWidth = FatUtility::convertToType($promotionDetails['blocation_banner_width'], FatUtility::VAR_FLOAT);
            $bannerHeight = FatUtility::convertToType($promotionDetails['blocation_banner_height'], FatUtility::VAR_FLOAT);
        } */

        $this->set('bannerSizeArr', $bannerSizeArr);
        $this->set('promotionType', $promotionType);
        $this->set('bannerTypeArr', applicationConstants::bannerTypeArr());
        $this->set('screenTypeArr', array(
            0 => ''
                ) + applicationConstants::getDisplaysArr($this->siteLangId));
        $this->set('promotionId', $promotionId);
        $this->set('languages', Language::getAllNames());
        $this->set('mediaFrm', $mediaFrm);
        $this->_template->render(false, false);
    }

    public function images($promotionId = 0, $langId = 0, $screen = 0)
    {
        $this->userPrivilege->canViewPromotions();
        $userId = $this->userParentId;
        $promotionId = FatUtility::int($promotionId);

        if (1 > $promotionId) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }

        $promotionType = 0;

        $srch = new PromotionSearch($this->siteLangId);
        $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b');
        $srch->joinSlides();
        $srch->addCondition('promotion_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
        $srch->addCondition('promotion_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addMultipleFields(array(
            'promotion_id',
            'promotion_type',
            'banner_id',
            'blocation_banner_width',
            'blocation_banner_height',
            'slide_id'
        ));
        $rs = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetch($rs);
        if (empty($promotionDetails)) {
            FatUtility::dieWithError(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
        }
        $promotionType = $promotionDetails['promotion_type'];

        $recordId = 0;
        $attachedFileType = 0;

        switch ($promotionType) {
            case Promotion::TYPE_BANNER:
                $imgDetail = Banner::getAttributesById($promotionDetails['banner_id']);
                $attachedFileType = AttachedFile::FILETYPE_BANNER;
                $recordId = $promotionDetails['banner_id'];
                break;
            case Promotion::TYPE_SLIDES:
                $imgDetail = Slides::getAttributesById($promotionDetails['slide_id']);
                $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
                $recordId = $promotionDetails['slide_id'];
                break;
        }

        if (!false == $imgDetail) {
            $bannerImgArr = AttachedFile::getMultipleAttachments($attachedFileType, $recordId, 0, $langId, false, $screen);
            /* CommonHelper::printArray($bannerImgArr);die; */
            $this->set('images', $bannerImgArr);
        }

        $this->set('promotionType', $promotionType);
        $this->set('bannerTypeArr', applicationConstants::bannerTypeArr());
        $this->set('screenTypeArr', array(
            0 => ''
                ) + applicationConstants::getDisplaysArr($this->siteLangId));
        $this->set('promotionId', $promotionId);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function removePromotionBanner()
    {
        $this->userPrivilege->canEditPromotions();
        $promotionId = FatApp::getPostedData('promotionId', FatUtility::VAR_INT, 0);
        $bannerId = FatApp::getPostedData('bannerId', FatUtility::VAR_INT, 0);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $screen = FatApp::getPostedData('screen', FatUtility::VAR_INT, 0);

        $data = Promotion::getAttributesById($promotionId, array(
                    'promotion_id',
                    'promotion_type',
                    'promotion_user_id'
        ));
        if (!$data || $data['promotion_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        $attachedFileType = 0;
        switch ($data['promotion_type']) {
            case Promotion::TYPE_BANNER:
                $attachedFileType = AttachedFile::FILETYPE_BANNER;
                break;

            case Promotion::TYPE_SLIDES:
                $attachedFileType = AttachedFile::FILETYPE_HOME_PAGE_BANNER;
                break;
        }

        if (1 > $attachedFileType) {
            Message::addErrorMessage(Labels::getLabel('Lbl_Invalid_request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$fileHandlerObj->deleteFile($attachedFileType, $bannerId, 0, 0, $langId, $screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function deletePromotionRecord(){
      $promotionId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);

      if(1 > $promotionId){
      Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID',$this->siteLangId));
      FatUtility::dieJsonError( Message::getHtml() );
      }

      $data = Promotion::getAttributesById($promotionId,array('promotion_id','promotion_user_id'));
      if(!$data || $data['promotion_user_id']!= $this->userParentId){
      Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID',$this->siteLangId));
      FatUtility::dieJsonError( Message::getHtml() );
      }

      $obj = new Promotion($promotionId);
      $obj->assignValues(array(Promotion::tblFld('deleted') => 1));
      if(!$obj->save()){
      Message::addErrorMessage($obj->getError());
      FatUtility::dieJsonError( Message::getHtml() );
      }

      FatUtility::dieJsonSuccess(Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY',$this->siteLangId));
      } */

    public function autoCompleteSelprods()
    {
        $userId = $this->userParentId;
        $db = FatApp::getDb();

        $srch = new ProductSearch($this->siteLangId);
        $srch->joinSellerProducts();
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription($this->siteLangId, true);
        $srch->addSubscriptionValidCondition();

        $post = FatApp::getPostedData();
        $srch->addCondition('selprod_id', '>', 'mysql_func_0', 'AND', true);
        $srch->addCondition('selprod_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        if (!empty($post['keyword'])) {
            /* $srch->addCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
              $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%','OR');
              $srch->addCondition('product_identifier', 'LIKE', '%' . $post['keyword'] . '%','OR'); */
            $srch->addDirectCondition("(selprod_title like " . $db->quoteVariable($post['keyword']) . " or selprod_title like " . $db->quoteVariable('%' . $post['keyword'] . '%') . " or product_name LIKE " . $db->quoteVariable('%' . $post['keyword'] . '%') . " or product_identifier LIKE " . $db->quoteVariable('%' . $post['keyword'] . '%') . ")", 'and');
        }
        $srch->setPageSize(FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));

        $srch->addMultipleFields(array(
            'selprod_id',
            'IFNULL(product_name,product_identifier) as product_name, IFNULL(selprod_title,product_identifier) as selprod_title'
        ));
        $rs = $srch->getResultSet();

        $products = $db->fetchAll($rs, 'selprod_id');
        $json = array();
        foreach ($products as $key => $product) {
            $variantStr = '';
            $options = SellerProduct::getSellerProductOptions($product['selprod_id'], true, $this->siteLangId);
            if (is_array($options) && count($options)) {
                foreach ($options as $op) {
                    $variantStr .= '(' . $op['option_name'] . ': ' . $op['optionvalue_name'] . ')';
                }
            }
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode(($product['selprod_title'] != '') ? $product['selprod_title'] . $variantStr : $product['product_name'] . $variantStr, ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function analytics($promotionId = 0)
    {
        $this->userPrivilege->canViewPromotions();
        $userId = $this->userParentId;
        $searchForm = $this->getPPCAnalyticsSearchForm($this->siteLangId);
        $searchForm->fill(array(
            'promotion_id' => $promotionId
        ));

        $srch = new PromotionSearch($this->siteLangId);
        $srch->addCondition('promotion_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
        $srch->addCondition('promotion_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addMultipleFields(array(
            'promotion_id',
            'promotion_type',
            'ifnull(promotion_name,promotion_identifier)as promotion_name'
        ));
        $rs = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetch($rs);

        if (empty($promotionDetails)) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $this->set('searchForm', $searchForm);
        $this->set('promotionDetails', $promotionDetails);

        $this->_template->render(true, true);
    }

    public function searchAnalyticsData()
    {
        $this->userPrivilege->canViewPromotions();
        $userId = $this->userParentId;
        $data = FatApp::getPostedData();
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $promotionId = FatUtility::int($data['promotion_id']);

        if ($promotionId < 1) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('advertiser'));
        }
        $promotionDetails = Promotion::getAttributesById($promotionId);
        if ($promotionDetails['promotion_user_id'] != $userId) {
            Message::addErrorMessage(Labels::getLabel('Msg_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $promotionType = 0;

        $frmSearch = $this->getPPCAnalyticsSearchForm($this->siteLangId);
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $frmSearch->getFormDataFromArray($data);
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);

        $fromDate = $post['date_from'];
        $toDate = $post['date_to'];

        $srch = new SearchBase(Promotion::DB_TBL_LOGS, 'i');
        $srch->addMultipleFields(array(
            'i.plog_promotion_id',
            'sum(i.plog_impressions) as impressions',
            'sum(i.plog_clicks) as clicks',
            'sum(i.plog_orders) as orders',
            'plog_date'
        ));

        $srch->addGroupBy('plog_date');
        $srch->addOrder('plog_date', 'DESC');
        if ($fromDate != '') {
            $srch->addCondition('i.plog_date', '>=', $fromDate . ' 00:00:00');
        }
        if ($toDate != '') {
            $srch->addCondition('i.plog_date', '<=', $toDate . ' 23:59:59');
        }
        if ($promotionId != '') {
            $srch->addCondition('i.plog_promotion_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
        }


        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        $rs = $srch->getResultSet();
        $promotionDetails = FatApp::getDb()->fetchAll($rs);

        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('arr_listing', $promotionDetails);
        $this->set('promotion_id', $promotionId);
        $this->set('page', $page);
        $this->_template->render(false, false);
    }

    private function getPromotionForm($promotionId = 0)
    {
        $frm = new Form('frmPromotion');
        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addHiddenField('', 'promotion_record_id', '');
        $frm->addRequiredField(Labels::getLabel('Lbl_Identifier', $this->siteLangId), 'promotion_identifier');

        $linkTargetsArr = applicationConstants::getLinkTargetsArr($this->siteLangId);

        $userId = $this->userParentId;
        $shopSrch = Shop::getSearchObject(true, $this->siteLangId);
        $shopSrch->addCondition('shop_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $shopSrch->setPageSize(1);
        $shopSrch->doNotCalculateRecords();
        $shopSrch->addMultipleFields(array(
            'shop_id'
        ));
        $rs = $shopSrch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        $displayAdvertiserOnly = false;
        if (empty($row)) {
            $displayAdvertiserOnly = true;
        }

        if ($promotionId > 0) {
            $srch = new PromotionSearch($this->siteLangId);
            $srch->addCondition('promotion_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
            $srch->addMultipleFields(array(
                'promotion_type'
            ));
            $rs = $srch->getResultSet();
            $promotioType = FatApp::getDb()->fetch($rs);
            $promotionTypeArr = Promotion::getTypeArr($this->siteLangId, $displayAdvertiserOnly);
            $promotioTypeValue = $promotionTypeArr[$promotioType['promotion_type']];
            $promotioTypeArr = array(
                $promotioType['promotion_type'] => $promotioTypeValue
            );
        } else {
            $promotioTypeArr = Promotion::getTypeArr($this->siteLangId, $displayAdvertiserOnly);
            if (!User::isSeller()) {
                unset($promotioTypeArr[Promotion::TYPE_SHOP]);
                unset($promotioTypeArr[Promotion::TYPE_PRODUCT]);
            }
        }

        $pTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->siteLangId), 'promotion_type', $promotioTypeArr, '', array(), '');

        if (User::isSeller()) {
            /* Shop [ */
            $frm->addTextBox(Labels::getLabel('LBL_Shop', $this->siteLangId), 'promotion_shop', '', array(
                'readonly' => true
            ))->requirements()->setRequired(true);
            $shopUnReqObj = new FormFieldRequirement('promotion_shop', Labels::getLabel('LBL_Shop', $this->siteLangId));
            $shopUnReqObj->setRequired(false);

            $shopReqObj = new FormFieldRequirement('promotion_shop', Labels::getLabel('LBL_Shop', $this->siteLangId));
            $shopReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_CPC' . '_[' . commonHelper::getDefaultCurrencySymbol() . ']', $this->siteLangId), 'promotion_shop_cpc', FatApp::getConfig('CONF_CPC_SHOP', FatUtility::VAR_FLOAT, 0), array(
                'readonly' => true
            ));
            /* ] */

            /* Product [ */
            $frm->addTextBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'promotion_product')->requirements()->setRequired(true);
            $prodUnReqObj = new FormFieldRequirement('promotion_product', Labels::getLabel('LBL_Product', $this->siteLangId));
            $prodUnReqObj->setRequired(false);

            $prodReqObj = new FormFieldRequirement('promotion_product', Labels::getLabel('LBL_Product', $this->siteLangId));
            $prodReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_CPC' . '_[' . CommonHelper::getDefaultCurrencySymbol() . ']', $this->siteLangId), 'promotion_product_cpc', FatApp::getConfig('CONF_CPC_PRODUCT', FatUtility::VAR_FLOAT, 0), array(
                'readonly' => true
            ));
            /* ] */

            /* Banner Url [ */
            $frm->addTextBox(Labels::getLabel('LBL_Url', $this->siteLangId), 'banner_url')->requirements()->setRequired(true);
            $urlUnReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlUnReqObj->setRequired(false);

            $urlReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlReqObj->setRequired(true);
            /* ] */

            /* Slide Url [ */
            $frm->addTextBox(Labels::getLabel('LBL_Url', $this->siteLangId), 'slide_url')->requirements()->setRequired(true);
            $urlSlideUnReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlSlideUnReqObj->setRequired(false);

            $urlSlideReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlSlideReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_CPC', $this->siteLangId), 'promotion_slides_cpc', FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0), array(
                'readonly' => true
            ));

            /* $frm->addSelectBox(Labels::getLabel('LBL_Open_In',$this->siteLangId), 'slide_target', $linkTargetsArr, '',array(),'');     */
            /* ] */

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'banner_url', $urlUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'banner_url', $urlUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'banner_url', $urlReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'banner_url', $urlUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'promotion_product', $prodUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'promotion_product', $prodUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'promotion_product', $prodReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'promotion_product', $prodUnReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'promotion_shop', $shopUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'promotion_shop', $shopReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'promotion_shop', $shopUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'promotion_shop', $shopUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'slide_url', $urlSlideUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'slide_url', $urlSlideUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'slide_url', $urlSlideUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'slide_url', $urlSlideReqObj);
        } else {
            /* $frm->addHiddenField('','promotion_type',Promotion::TYPE_BANNER);
              $frm->addTextBox(Labels::getLabel('LBL_Url',$this->siteLangId), 'banner_url')->requirements()->setRequired(true); */

            /* Banner Url [ */
            $frm->addTextBox(Labels::getLabel('LBL_Url', $this->siteLangId), 'banner_url')->requirements()->setRequired(true);
            $urlUnReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlUnReqObj->setRequired(false);

            $urlReqObj = new FormFieldRequirement('banner_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlReqObj->setRequired(true);
            /* ] */

            /* Slide Url [ */
            $frm->addTextBox(Labels::getLabel('LBL_Url', $this->siteLangId), 'slide_url')->requirements()->setRequired(true);
            $urlSlideUnReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlSlideUnReqObj->setRequired(false);

            $urlSlideReqObj = new FormFieldRequirement('slide_url', Labels::getLabel('LBL_Url', $this->siteLangId));
            $urlSlideReqObj->setRequired(true);

            $frm->addTextBox(Labels::getLabel('LBL_CPC' . '_[' . commonHelper::getDefaultCurrencySymbol() . ']', $this->siteLangId), 'promotion_slides_cpc', FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0), array(
                'readonly' => true
            ));

            /* $frm->addSelectBox(Labels::getLabel('LBL_Open_In',$this->siteLangId), 'slide_target', $linkTargetsArr, '',array(),''); */
            /* ] */

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'banner_url', $urlReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'banner_url', $urlUnReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'slide_url', $urlSlideUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'slide_url', $urlSlideReqObj);
        }

        //$frm->addTextBox(Labels::getLabel('LBL_Url',$this->siteLangId), 'banner_url')->requirements()->setRequired(true);


        /* $frm->addSelectBox(Labels::getLabel('LBL_Open_In',$this->siteLangId), 'banner_target', $linkTargetsArr, '',array(),'');
         */

        $srch = BannerLocation::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(array(
            'blocation_id',
            'blocation_promotion_cost',
            'ifnull(blocation_name, blocation_identifier) as blocation_name', 'collection_layout_type', 'blocation_promotion_cost_second'
        ));
        $srch->addOrder('collection_display_order', 'ASC');
        
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs, 'blocation_id');
        $locationArr = array();
        if (!empty($row)) {
            $posIndex = 1;
            foreach ($row as $key => $val) {
                if ($val['collection_layout_type'] == Collections::TYPE_BANNER_LAYOUT4) {
                   $locationArr[$key] = $val['blocation_name'] . ' ( '. Labels::getLabel('LBL_Left', $this->siteLangId). ' : ' . CommonHelper::displayMoneyFormat($val['blocation_promotion_cost']) . ', '. Labels::getLabel('LBL_Right', $this->siteLangId). ' : '. CommonHelper::displayMoneyFormat($val['blocation_promotion_cost_second']) .' )';
                   /* $locationArr[$key] = Labels::getLabel('LBL_Position', $this->siteLangId). ' - '. $posIndex . ' ( '. Labels::getLabel('LBL_Left', $this->siteLangId). ' : ' . CommonHelper::displayMoneyFormat($val['blocation_promotion_cost']) . ', '. Labels::getLabel('LBL_Right', $this->siteLangId). ' : '. CommonHelper::displayMoneyFormat($val['blocation_promotion_cost_second']) .' )'; */
                } else {
                    $locationArr[$key] = $val['blocation_name'] . ' ( ' . CommonHelper::displayMoneyFormat($val['blocation_promotion_cost']) . ' )';
                    /* $locationArr[$key] = Labels::getLabel('LBL_Position', $this->siteLangId). ' - '. $posIndex  . ' ( ' . CommonHelper::displayMoneyFormat($val['blocation_promotion_cost']) . ' )'; */
                }
                $posIndex++;
            }
        }

        $fld = $frm->addTextBox(Labels::getLabel('LbL_Budget' . '_[' . commonHelper::getDefaultCurrencySymbol() . ']', $this->siteLangId), 'promotion_budget');
        $fld->requirements()->setRequired();
        $fld->requirements()->setFloatPositive(true);

        $locIdFld = $frm->addSelectBox(Labels::getLabel('LBL_Location', $this->siteLangId), 'banner_blocation_id', $locationArr, '', array(), Labels::getLabel('LBL_Select', $this->siteLangId))->requirements()->setRequired(true);
        $locIdFldUnReqObj = new FormFieldRequirement('banner_blocation_id', Labels::getLabel('LBL_Location', $this->siteLangId));
        $locIdFldUnReqObj->setRequired(false);

        $locIdFldReqObj = new FormFieldRequirement('banner_blocation_id', Labels::getLabel('LBL_Location', $this->siteLangId));
        $locIdFldReqObj->setRequired(true);

        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_BANNER, 'eq', 'banner_blocation_id', $locIdFldReqObj);
        $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SLIDES, 'eq', 'banner_blocation_id', $locIdFldUnReqObj);

        if (User::isSeller()) {
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_SHOP, 'eq', 'banner_blocation_id', $locIdFldUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Promotion::TYPE_PRODUCT, 'eq', 'banner_blocation_id', $locIdFldUnReqObj);
        }

        $bannerPosArr = Collections::getBannerPositionType($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Banner_Position', $this->siteLangId), 'banner_position', $bannerPosArr, '', array(), '');
        

        $fldDuration = $frm->addSelectBox(Labels::getLabel('LBL_Duration', $this->siteLangId), 'promotion_duration', Promotion::getPromotionBudgetDurationArr($this->siteLangId), '', array('id' => 'promotion_duration'))->requirements()->setRequired();

        $frm->addDateField(Labels::getLabel('LBL_Start_Date', $this->siteLangId), 'promotion_start_date', '', array(
            'placeholder' => Labels::getLabel('LBL_Date_From', $this->siteLangId),
            'readonly' => 'readonly', 'class' => 'field--calender'
        ))->requirements()->setRequired();
        $frm->addDateField(Labels::getLabel('LBL_End_Date', $this->siteLangId), 'promotion_end_date', '', array(
            'placeholder' => Labels::getLabel('LBL_Date_To', $this->siteLangId),
            'readonly' => 'readonly', 'class' => 'field--calender'
        ))->requirements()->setRequired();

        $fld = $frm->addRequiredField(Labels::getLabel('LBL_promotion_start_time', $this->siteLangId), 'promotion_start_time', '', array(
            'class' => 'time',
            'readonly' => 'readonly'
        ));
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_promotion_end_time', $this->siteLangId), 'promotion_end_time', '', array(
            'class' => 'time',
            'readonly' => 'readonly'
        ));
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'promotion_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getPromotionLangForm($promotionId, $langId)
    {
        $frm = new Form('frmPromotionLang');
        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $langId, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_promotion_name', $langId), 'promotion_name');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $langId == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    public function imgCropper()
    {
        $this->_template->render(false, false, 'cropper/index.php');
    }

    private function getPromotionMediaForm($promotionId = 0, $promotionType = 0)
    {
        $promotionId = FatUtility::int($promotionId);
        $frm = new Form('frmPromotionMedia');

        $frm->addHiddenField('', 'promotion_id', $promotionId);
        $frm->addHiddenField('', 'promotion_type', $promotionType);

        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->siteLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->siteLangId), 'banner_screen', $screenArr, '', array(), '');
        $frm->addHiddenField('', 'banner_min_width');
        $frm->addHiddenField('', 'banner_min_height');
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->siteLangId), 'banner_image', array('accept' => 'image/*'));

        return $frm;
    }

    private function getPromotionSearchForm($langId)
    {
        $langId = FatUtility::int($langId);

        $frm = new Form('frmPromotionSearch');
        $frm->addTextBox('', 'keyword', '', array(
            'placeholder' => Labels::getLabel('LBL_keyword', $langId)
        ));

        $typeArr = Promotion::getTypeArr($langId);
        if (!User::isSeller()) {
            unset($typeArr[Promotion::TYPE_SHOP]);
            unset($typeArr[Promotion::TYPE_PRODUCT]);
        }
        $frm->addSelectBox('', 'active_promotion', array(
            '-1' => Labels::getLabel('LBL_All', $langId),
            '1' => Labels::getLabel('LBL_Active_Promotions', $langId)
                ), '', array(), '');
        $frm->addSelectBox('', 'type', array(
            '-1' => Labels::getLabel('LBL_All_Type', $langId)
                ) + $typeArr, '', array(), '');

        $frm->addDateField('', 'date_from', '', array(
            'readonly' => 'readonly',
            'class' => 'field--calender',
            'placeholder' => Labels::getLabel('LBL_Date_From', $langId)
        ));
        $frm->addDateField('', 'date_to', '', array(
            'readonly' => 'readonly',
            'class' => 'field--calender',
            'placeholder' => Labels::getLabel('LBL_Date_To', $langId)
        ));

        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldClear = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array(
            'onclick' => 'clearPromotionSearch();'
        ));
        /* $fldSubmit->attachField($fldClear); */
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getPPCAnalyticsSearchForm($langId)
    {
        $langId = FatUtility::int($langId);

        $frm = new Form('frmPromotionAnalyticsSearch');


        $frm->addDateField('', 'date_from', '', array(
            'readonly' => 'readonly',
            'class' => 'field--calender',
            'placeholder' => Labels::getLabel('LBL_Date_From', $langId)
        ));
        $frm->addDateField('', 'date_to', '', array(
            'readonly' => 'readonly',
            'class' => 'field--calender',
            'placeholder' => Labels::getLabel('LBL_Date_To', $langId)
        ));

        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldClear = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array(
            'onclick' => 'clearPromotionSearch();'
        ));

        $frm->addHiddenField('', 'page');

        $frm->addHiddenField('', 'promotion_id');
        return $frm;
    }

    private function getRechargeWalletForm($langId)
    {
        $frm = new Form('frmRechargeWallet');
        $fld = $frm->addFloatField('', 'amount');
        //$fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Add_Money_to_wallet', $langId));
        return $frm;
    }

    public function checkValidPromotionBudget()
    {
        $post = FatApp::getPostedData();
        $promotionType = Fatutility::int($post['promotion_type']);
        $promotionBudget = Fatutility::float($post['promotion_budget']);
        $bannerPosition = FatApp::getPostedData('banner_position', FatUtility::VAR_INT, 0);

        $minBudget = 0;

        switch ($promotionType) {
            case Promotion::TYPE_SHOP:
                $minBudget = FatApp::getConfig('CONF_CPC_SHOP', FatUtility::VAR_FLOAT, 0);
                break;
            case Promotion::TYPE_PRODUCT:
                $minBudget = FatApp::getConfig('CONF_CPC_PRODUCT', FatUtility::VAR_FLOAT, 0);
                break;
            case Promotion::TYPE_BANNER:
                $bannerLocationId = Fatutility::int($post['banner_blocation_id']);
                $srch = BannerLocation::getSearchObject($this->siteLangId);
                $srch->addMultipleFields(array('blocation_promotion_cost', 'blocation_promotion_cost_second', 'collection_layout_type'));
                $srch->addCondition('blocation_id', '=', 'mysql_func_'. $bannerLocationId, 'AND', true);
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs, 'blocation_id');
                if (!empty($row)) {
                    $minBudget = $row['blocation_promotion_cost'];
                    if ($bannerPosition == Collections::BANNER_POSITION_RIGHT && $row['collection_layout_type'] == Collections::TYPE_BANNER_LAYOUT4) {
                        $minBudget = $row['blocation_promotion_cost_second'];
                    }
                }
                break;
            case Promotion::TYPE_SLIDES:
                $minBudget = FatApp::getConfig('CONF_CPC_SLIDES', FatUtility::VAR_FLOAT, 0);
                break;
        }

        if ($minBudget > $promotionBudget) {
            Message::addErrorMessage(Labels::getLabel("MSG_Budget_should_be_greater_than_CPC", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function getBannerLocationDimensions($promotionId, $deviceType)
    {
        $srch = new PromotionSearch($this->siteLangId);
        $srch->joinBannersAndLocation($this->siteLangId, Promotion::TYPE_BANNER, 'b', $deviceType);
        $srch->addCondition('promotion_id', '=', 'mysql_func_'. $promotionId, 'AND', true);
        $srch->addMultipleFields(array(
            'blocation_banner_width',
            'blocation_banner_height'
        ));
        $rs = $srch->getResultSet();
        $bannerDimensions = FatApp::getDb()->fetch($rs);
        $this->set('bannerWidth', $bannerDimensions['blocation_banner_width']);
        $this->set('bannerHeight', $bannerDimensions['blocation_banner_height']);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changePromotionStatus()
    {
        $this->userPrivilege->canEditPromotions();
        $promotionId = FatApp::getPostedData('promotionId', FatUtility::VAR_INT, 0);
        $userId = $this->userParentId;

        $promotionData = Promotion::getAttributesById($promotionId, array('promotion_user_id', 'promotion_active'));
        if (!$promotionData || ($promotionData && $promotionData['promotion_user_id'] != $userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $status = ($promotionData['promotion_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updatePromotionStatus($promotionId, $status);

        $this->set('msg', Labels::getLabel('MSG_Status_changed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updatePromotionStatus($promotionId, $status)
    {
        $this->userPrivilege->canEditPromotions();
        $promotionId = FatUtility::int($promotionId);
        $status = FatUtility::int($status);
        if (1 > $promotionId || -1 == $status) {
            FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
        }
        $promotion = new Promotion($promotionId);
        if (!$promotion->changeStatus($status)) {
            Message::addErrorMessage($promotion->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }
}
