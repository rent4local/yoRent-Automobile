<?php

class UsersController extends AdminBaseController
{
    public function __construct($action)
    {
        $ajaxCallArray = array();
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewUsers($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditUsers($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewUsers();
        $frmSearch = $this->getUserSearchForm();
        $data = FatApp::getPostedData();
        if (is_array($data) && isset($data['id'])) {
            $data['user_id'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->_template->addJs(['js/import-export.js', 'js/intlTelInput.min.js']);
        $this->_template->addCss(['css/intlTelInput.css']);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewUsers();
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getUserSearchForm();

        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }

        $userObj = new User();
        $srch = $userObj->getUserSearchObj(null, true);
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'user_id = shop.shop_user_id OR user_parent = shop.shop_user_id', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = ' . $this->adminLangId, 's_l');
        $srch->addOrder('u.user_id', 'DESC');
        $srch->addOrder('credential_active', 'DESC');

        $user_id = FatApp::getPostedData('user_id', FatUtility::VAR_INT, -1);
        if ($user_id > 0) {
            $srch->addCondition('user_id', '=', $user_id);
        } else {
            $keyword = trim(FatApp::getPostedData('keyword', null, ''));
            if (!empty($keyword)) {
                $cond = $srch->addCondition('uc.credential_username', 'like', '%' . $keyword . '%');
                $cond->attachCondition('uc.credential_email', 'like', '%' . $keyword . '%', 'OR');
                $cond->attachCondition('u.user_phone', 'like', '%' . $keyword . '%', 'OR');
                $cond->attachCondition('u.user_name', 'like', '%' . $keyword . '%');
            }
        }
        $shop_id = FatApp::getPostedData('shop_id', FatUtility::VAR_INT, -1);
        if ($shop_id > 0) {
            $srch->addCondition('shop_id', '=', $shop_id);
        } else {
            $shop_name = trim(FatApp::getPostedData('shop_name', null, ''));
            if (!empty($shop_name)) {
                $cond = $srch->addCondition('shop.shop_identifier', 'like', '%' . $shop_name . '%');
                $cond->attachCondition('s_l.shop_name', 'like', '%' . $shop_name . '%', 'OR');
            }
        }

        $user_active = FatApp::getPostedData('user_active', FatUtility::VAR_INT, -1);
        if ($user_active > -1) {
            $srch->addCondition('uc.credential_active', '=', $user_active);
        }

        $user_verified = FatApp::getPostedData('user_verified', FatUtility::VAR_INT, -1);
        if ($user_verified > -1) {
            $srch->addCondition('uc.credential_verified', '=', $user_verified);
        }

        $type = FatApp::getPostedData('type', FatUtility::VAR_STRING, 0);

        switch ($type) {
            case User::USER_TYPE_SELLER:
                $srch->addCondition('u.user_is_supplier', '=', applicationConstants::YES);
                $srch->addCondition('u.user_parent', '=', 0);
                break;
            case User::USER_TYPE_BUYER:
                $srch->addCondition('u.user_is_buyer', '=', applicationConstants::YES);
                break;
            case User::USER_TYPE_ADVERTISER:
                $srch->addCondition('u.user_is_advertiser', '=', applicationConstants::YES);
                $srch->addCondition('u.user_parent', '=', 0);
                break;
            case User::USER_TYPE_AFFILIATE:
                $srch->addCondition('u.user_is_affiliate', '=', applicationConstants::YES);
                break;
            case User::USER_TYPE_SUB_USER:
                $srch->addCondition('u.user_parent', '>', 0);
                break;
            case User::USER_TYPE_BUYER_SELLER:
                $srch->addCondition('u.user_is_supplier', '=', applicationConstants::YES);
                $srch->addCondition('u.user_is_buyer', '=', applicationConstants::YES);
                break;
        }

        $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::NO);

        $user_regdate_from = FatApp::getPostedData('user_regdate_from', FatUtility::VAR_DATE, '');
        if (!empty($user_regdate_from)) {
            $srch->addCondition('user_regdate', '>=', $user_regdate_from . ' 00:00:00');
        }

        $user_regdate_to = FatApp::getPostedData('user_regdate_to', FatUtility::VAR_DATE, '');
        if (!empty($user_regdate_to)) {
            $srch->addCondition('user_regdate', '<=', $user_regdate_to . ' 23:59:59');
        }

        $srch->addFld(array('user_is_buyer', 'user_is_supplier', 'user_is_advertiser', 'user_is_affiliate', 'user_registered_initially_for'));

        $srch->addMultipleFields(array('user_id', 'user_name', 'user_phone', 'user_profile_info', 'user_regdate', 'user_is_buyer', 'user_parent', 'credential_username', 'credential_email', 'credential_active', 'credential_verified', 'shop_id', 'shop_user_id', 'IFNULL(shop_name, shop_identifier) as shop_name'));

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        //echo $srch->getQuery();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'user_id');

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('canVerify', $this->objPrivilege->canVerifyUsers($this->admin_id, true));
        $this->set('canViewShops', $this->objPrivilege->canViewShops($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function login($userId)
    {
        $this->objPrivilege->canEditUsers();
        $userObj = new User($userId);
        $user = $userObj->getUserInfo(array('credential_username', 'credential_password', 'user_preferred_dashboard'), false, false);
        if (!$user) {
            Message::addErrorMessage($this->str_invalid_request);
            FatApp::redirectUser(UrlHelper::generateUrl('Users'));
        }
        $userAuthObj = new UserAuthentication();
        if (!$userAuthObj->login($user['credential_username'], $user['credential_password'], $_SERVER['REMOTE_ADDR'], false, true) === true) {
            Message::addErrorMessage($userObj->getError());
            FatApp::redirectUser(UrlHelper::generateUrl('Users'));
        }

        FatApp::redirectUser(UrlHelper::generateUrl('account', '', array(), CONF_WEBROOT_FRONT_URL));
    }

    public function setup()
    {
        $this->objPrivilege->canEditUsers();
        $post = FatApp::getPostedData();
        $user_id = FatUtility::int($post['user_id']);
        $frm = $this->getForm($user_id);

        $user_state_id = FatUtility::int($post['user_state_id']);
        $post = $frm->getFormDataFromArray($post);
        $post['user_state_id'] = $user_state_id;

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        unset($post['user_id']);
        $credential_email = $post['credential_email'];
        if(0 < $user_id){
            unset($post['credential_username']);  
            unset($post['credential_email']);
            if ($post['user_dob'] == "0000-00-00" || $post['user_dob'] == "" || strtotime($post['user_dob']) == 0) {
                unset($post['user_dob']);
            }    
        }

        /* [ new user    */        
        if (1 > $user_id) {
            $post['user_verify'] = FatApp::getConfig('CONF_EMAIL_VERIFICATION_REGISTRATION', FatUtility::VAR_INT, 1) ? 0 : 1;
            if ($post['user_type'] == User::USER_TYPE_BUYER) {
                $post['user_is_buyer'] = 1;
                $post['user_preferred_dashboard'] = User::USER_BUYER_DASHBOARD;
                $post['user_registered_initially_for'] = User::USER_TYPE_BUYER;
                $post['user_is_supplier'] = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0 : 1;
                $post['user_is_advertiser'] = (FatApp::getConfig("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION", FatUtility::VAR_INT, 1) || FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) ? 0 : 1;
            } elseif ($post['user_type'] == User::USER_TYPE_SELLER) {
                $post['user_is_buyer'] = 1;
                $post['user_is_supplier'] = 1;
                $post['user_preferred_dashboard'] = User::USER_SELLER_DASHBOARD;
                $post['user_registered_initially_for'] = User::USER_TYPE_SELLER;
                if (FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) {
                    $post['user_is_buyer'] = 0;
                }
            } elseif ($post['user_type'] == User::USER_TYPE_AFFILIATE) {
                $post['user_is_affiliate'] = 1;
                $post['user_registered_initially_for'] = User::USER_TYPE_AFFILIATE;
                $post['user_preferred_dashboard'] = User::USER_AFFILIATE_DASHBOARD;
            } elseif ($post['user_type'] == User::USER_TYPE_ADVERTISER) {
                $post['user_is_advertiser'] = 1;
                $post['user_registered_initially_for'] = User::USER_TYPE_ADVERTISER;
                $post['user_preferred_dashboard'] = User::USER_ADVERTISER_DASHBOARD;
            }
        }
        /* new user ]   */

        $db = FatApp::getDb();
        $db->startTransaction();
        $userObj = new User($user_id);
        $userObj->assignValues($post);
        if (!$userObj->save()) {
            $db->rollbackTransaction();
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if($user_id > 0) {
            $userInfo= $userObj->getUserInfo('credential_email',false,false,true);
            if(empty($userInfo['credential_email'])) {
                if (!$userObj->changeEmail($credential_email)) {
                    Message::addErrorMessage(Labels::getLabel("MSG_UPDATED_EMAIL_COULD_NOT_BE_SET", $this->siteLangId) . $userObj->getError());
                    FatApp::redirectUser(UrlHelper::generateUrl('GuestUser', 'loginForm'));
                }
            }
        }

         /* [ new user    */
         if (1 > $user_id) {
            if (!$userObj->setLoginCredentials($post['credential_username'], $post['credential_email'], null, applicationConstants::ACTIVE, $post['user_verify'])) {
                $db->rollbackTransaction();
                return false;
            }
            
            $userData = [
                'user_name' => $post['user_name'],
                'user_email' => $post['credential_email'],
                'user_id' => $userObj->getMainTableRecordId(),
                'account_type' => User::getUserTypesArr($this->adminLangId)[$post['user_type']]
            ];
            
            if (!$userObj->sendAdminNewUserCreationEmail($userData, $this->admin_id)) {
                $db->rollbackTransaction();
                $message = Labels::getLabel("ERR_ERROR_IN_SENDING_WELCOME_EMAIL", $this->admin_id);
                return false;
            }
            
        }
        /*  new user ] */
        
        $countryIso = FatApp::getPostedData('user_country_iso', FatUtility::VAR_STRING, '');
        if (!empty($countryIso)) {
            if (false === $userObj->updateUserMeta('user_country_iso', $countryIso)) {
                Message::addErrorMessage($userObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        
        $db->commitTransaction();
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function form($user_id = 0)
    {
        $this->objPrivilege->canEditUsers();
        $user_id = FatUtility::int($user_id);
        $frmUser = $this->getForm($user_id);
        $userParent = 0;
        $stateId = 0;
        if (0 < $user_id) {
            $userObj = new User($user_id);
            $srch = $userObj->getUserSearchObj();
            $srch->addMultipleFields(array('u.*'));
            $rs = $srch->getResultSet();

            if (!$rs) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            $data = FatApp::getDb()->fetch($rs, 'user_id');

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            /* if(isset($data['credential_username'])){
            $data['credential_username'] = htmlentities($data['credential_username']);
            } */
            $stateId = $data['user_state_id'];
            $frmUser->fill($data);
            $userParent = $data['user_parent'];
            $this->set('data', $data);
        }
        $this->set('countryIso', User::getUserMeta($user_id, 'user_country_iso'));
        $this->set('userParent', $userParent);
        $this->set('user_id', $user_id);
        $this->set('stateId', $stateId);
        $this->set('frmUser', $frmUser);
        $this->_template->render(false, false);
    }

    public function rewards($userId = 0)
    {
        $this->objPrivilege->canViewUsers();

        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : $post['page'];
        $page = (empty($page) || $page <= 0) ? 1 : FatUtility::int($page);

        $srch = new UserRewardSearch();
        $srch->addCondition('urp.urp_user_id', '=', $userId);

        $srch->addMultipleFields(array('urp.*'));

        $srch->addOrder('urp_id', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('userId', $userId);
        $this->_template->render(false, false);
    }

    public function addUserRewardPoints($userId = 0)
    {
        $this->objPrivilege->canViewUsers();
        $userId = FatUtility::int($userId);

        if (1 > $userId) {
            FatUtility::dieWithError($this->str_invalid_request_id);
        }

        $frm = $this->addUserRewardPointsForm($this->adminLangId);
        $frm->fill(array('urp_user_id' => $userId));

        $this->set('userId', $userId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setupUserRewardPoints()
    {
        $this->objPrivilege->canEditUsers();
        $frm = $this->addUserRewardPointsForm($this->adminLangId);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userId = FatUtility::int($post['urp_user_id']);
        if (1 > $userId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($userId);
        $user = $userObj->getUserInfo(array('user_parent'), false, false);
        if (!$user || 0 < $user['user_parent']) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new UserRewards();
        $post['urp_date_added'] = date('Y-m-d H:i:s');
        if (!empty($post['validity']) && $validity = FatUtility::int($post['validity'])) {
            $post['urp_date_expiry'] = date('Y-m-d H:i:s', strtotime("+$validity days"));
        }
        $obj->assignValues($post);
        if (!$obj->save($post)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* send email to user[ */
        $urpId = $obj->getMainTableRecordId();
        $emailObj = new EmailHandler();
        $emailObj->sendRewardPointsNotification($this->adminLangId, $urpId);
        /* ] */

        $this->set('userId', $userId);
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function transaction($userId = 0)
    {
        $this->objPrivilege->canViewUsers();

        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : $post['page'];
        $page = (empty($page) || $page <= 0) ? 1 : FatUtility::int($page);

        $srch = Transactions::getSearchObject();
        $srch->addCondition('utxn.utxn_user_id', '=', $userId);

        $balSrch = Transactions::getSearchObject();
        $balSrch->doNotCalculateRecords();
        $balSrch->doNotLimitRecords();
        $balSrch->addMultipleFields(array('utxn.*', "utxn_credit - utxn_debit as bal"));
        $balSrch->addCondition('utxn_user_id', '=', $userId);
        $balSrch->addCondition('utxn_status', '=', 1);
        $qryUserPointsBalance = $balSrch->getQuery();

        $srch->joinTable('(' . $qryUserPointsBalance . ')', 'JOIN', 'tqupb.utxn_id <= utxn.utxn_id', 'tqupb');

        $srch->addMultipleFields(array('utxn.*', "SUM(tqupb.bal) balance"));

        $srch->addOrder('utxn_id', 'DESC');
        $srch->addGroupBy('utxn.utxn_id');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('userId', $userId);
        $this->set('statusArr', Transactions::getStatusArr($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function addUserTransaction($userId = 0)
    {
        $this->objPrivilege->canViewUsers();
        $userId = FatUtility::int($userId);

        if (1 > $userId) {
            FatUtility::dieWithError($this->str_invalid_request_id);
        }

        $frm = $this->addUserTransactionForm($this->adminLangId);
        $frm->fill(array('user_id' => $userId));

        $this->set('userId', $userId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setupUserTransaction()
    {
        $this->objPrivilege->canEditUsers();
        $frm = $this->addUserTransactionForm($this->adminLangId);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userId = FatUtility::int($post['user_id']);
        if (1 > $userId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($userId);
        $user = $userObj->getUserInfo(array('user_parent'), false, false);

        if (!$user || 0 < $user['user_parent']) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $tObj = new Transactions();
        $data = array(
        'utxn_user_id' => $userId,
        'utxn_date' => date('Y-m-d H:i:s'),
        'utxn_comments' => $post['description'],
        'utxn_status' => Transactions::STATUS_COMPLETED
        );

        if ($post['type'] == Transactions::CREDIT_TYPE) {
            $data['utxn_credit'] = $post['amount'];
        }

        if ($post['type'] == Transactions::DEBIT_TYPE) {
            $data['utxn_debit'] = $post['amount'];
        }

        if (!$tObj->addTransaction($data)) {
            Message::addErrorMessage($tObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* send email to user[ */
        $emailNotificationObj = new EmailHandler();
        $emailNotificationObj->sendTxnNotification($tObj->getMainTableRecordId(), $this->adminLangId);
        /* ] */

        $this->set('userId', $userId);
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function bankInfoForm($user_id)
    {
        $this->objPrivilege->canViewUsers();
        $user_id = FatUtility::int($user_id);

        if (1 > $user_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $frm = $this->getBankInfoForm();

        $userObj = new User($user_id);
        $data = $userObj->getUserBankInfo();

        $data['user_id'] = $user_id;

        if ($data != false) {
            $frm->fill($data);
        }

        $this->set('frm', $frm);
        $this->set('user_id', $user_id);
        $this->_template->render(false, false);
    }

    public function setupBankInfo()
    {
        $this->objPrivilege->canEditUsers();
        $frm = $this->getBankInfoForm();

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $user_id = FatUtility::int($post['user_id']);
        unset($post['user_id']);

        if (1 > $user_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($user_id);
        $srch = $userObj->getUserSearchObj(array('user_parent'));
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs, 'user_id');

        if ($data === false || 0 < $data['user_parent']) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if (!$userObj->updateBankInfo($post)) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('userId', $user_id);
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function addresses($userId)
    {
        $this->objPrivilege->canViewUsers();
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $this->set('user_id', $userId);
        $address =  new Address(0, $this->adminLangId);
        $addresses = $address->getData(Address::TYPE_USER, $userId);
        $this->set('addresses', $addresses);
        $this->_template->render(false, false);
    }

    public function addressForm($userId, $addr_id = 0)
    {
        $this->objPrivilege->canViewUsers();
        $userId = FatUtility::int($userId);
        $addr_id = FatUtility::int($addr_id);

        if (1 > $userId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $addressFrm = $this->getUserAddressForm($this->adminLangId);

        $stateId = 0;
        if ($addr_id > 0) {
            $address =  new Address($addr_id, $this->adminLangId);
            $data = $address->getData(Address::TYPE_USER, $userId);
            if (empty($data)) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $stateId = $data['addr_state_id'];
            $addressFrm->fill($data);
        } else {
            $addressFrm->fill(array('addr_record_id' => $userId));
        }

        $this->set('addressFrm', $addressFrm);
        $this->set('stateId', $stateId);
        $this->set('user_id', $userId);
        $this->_template->render(false, false);
    }

    public function setupAddress()
    {
        $frm = $this->getUserAddressForm($this->adminLangId);
        $post = FatApp::getPostedData();

        if ($post == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $addr_state_id = FatUtility::int($post['addr_state_id']);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $post['addr_state_id'] = $addr_state_id;

        $user_id = FatUtility::int($post['addr_record_id']);
        $addr_id = FatUtility::int($post['addr_id']);

        if (1 > $user_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $userObj = new User($user_id);
        $srch = $userObj->getUserSearchObj(array('user_parent'));
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs, 'user_id');

        if ($data === false || 0 < $data['user_parent']) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $addressObj = new Address($addr_id);

        $data_to_be_save = $post;
        $data_to_be_save['addr_record_id'] = $user_id;
        $data_to_be_save['addr_type'] = Address::TYPE_USER;
        $data_to_be_save['addr_lang_id'] = $this->adminLangId;
        $addressObj->assignValues($data_to_be_save, true);
        if (!$addressObj->save()) {
            Message::addErrorMessage($addressObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('userId', $user_id);
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteAddress()
    {
        $post = FatApp::getPostedData();
        if ($post == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $addrId = FatUtility::int($post['id']);
        $user_id = FatUtility::int($post['user_id']);

        if (1 > $addrId || 1 > $user_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $address = new Address($addrId, $this->adminLangId);
        $data = $address->getData(Address::TYPE_USER, $user_id);
        if (empty($data)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        // if (!$address->deleteRecord()) {
        //     Message::addErrorMessage($address->getError());
        //     FatUtility::dieJsonError(Message::getHtml());
        // }
        $db = FatApp::getDb();
        $updateArray = array('addr_deleted' => applicationConstants::YES);
        $whr = array('smt' => 'addr_record_id = ? AND addr_id = ?', 'vals' => array($data['addr_record_id'], $addrId));
        if (!$db->updateFromArray(Address::DB_TBL, $updateArray, $whr)) {
            LibHelper::dieJsonError($db->getError());
        }

        $this->set('userId', $user_id);
        $this->set('msg', Labels::getLabel('LBL_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteAccount()
    {
        $this->objPrivilege->canEditUsers();
        $post = FatApp::getPostedData();
        if ($post == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $user_id = FatUtility::int($post['user_id']);
        if (1 > $user_id) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->markAsDeleted($user_id);
        $shopId = Shop::getAttributesByUserId($user_id, 'shop_id');
        if (0 < $shopId) {
            Product::updateMinPrices(0, $shopId);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditUsers();
        $userIdsArr = FatUtility::int(FatApp::getPostedData('user_ids'));

        if (empty($userIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($userIdsArr as $user_id) {
            if (1 > $user_id) {
                continue;
            }
            $this->markAsDeleted($user_id);
        }
        Product::updateMinPrices();
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($user_id)
    {
        $user_id = FatUtility::int($user_id);
        if (1 > $user_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $userObj = new User($user_id);
        $userObj->assignValues(array('user_deleted' => applicationConstants::YES));
        if (!$userObj->save()) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function changePasswordForm($user_id)
    {
        $this->objPrivilege->canEditUsers();
        $user_id = FatUtility::int($user_id);
        $frm = $this->getChangePasswordForm($user_id);

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function updatePassword()
    {
        $pwdFrm = $this->getChangePasswordForm();
        $post = $pwdFrm->getFormDataFromArray(FatApp::getPostedData());

        if (!$pwdFrm->validate($post)) {
            Message::addErrorMessage($pwdFrm->getValidationErrors());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($post['new_password'] != $post['conf_new_password']) {
            Message::addErrorMessage(Labels::getLabel('LBL_New_Password_and_Confirm_new_password_does_not_match', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!ValidateElement::password($post['new_password'])) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_PASSWORD_MUST_BE_EIGHT_CHARACTERS_LONG_AND_ALPHANUMERIC', $this->adminLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        $user_id = FatUtility::int($post['user_id']);
        if ($user_id < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($user_id);
        $srch = $userObj->getUserSearchObj(array('user_id'));
        $rs = $srch->getResultSet();

        if (!$rs) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = FatApp::getDb()->fetch($rs, 'user_id');

        if ($data === false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$userObj->setLoginPassword($post['new_password'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Password_could_not_be_set ', $this->adminLangId) . ' ' . $userObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        // TODo:: Can send change password notification using configuration

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function sellerApprovalRequests()
    {
        $this->objPrivilege->canViewSellerApprovalRequests();

        $frmSearch = $this->supplierRequestSearchForm();
        $this->set('frmSearch', $frmSearch);

        $this->_template->render();
    }

    public function sellerApprovalRequestSearch()
    {
        $this->objPrivilege->canViewSellerApprovalRequests();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srchForm = $this->supplierRequestSearchForm();

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $post = $srchForm->getFormDataFromArray($data);

        $userObj = new User();
        $srch = $userObj->getUserSupplierRequestsObj();
        $srch->addFld('tusr.*');
        $srch->addOrder('usuprequest_id', 'desc');

        if (!empty($post['keyword'])) {
            $keyword = trim($post['keyword']);
            $cond = $srch->addCondition('tusr.usuprequest_reference', '=', '%' . $keyword . '%', 'AND');
            $cond->attachCondition('u.user_name', 'like', '%' . $keyword . '%', 'OR');
            $cond->attachCondition('uc.credential_email', 'like', '%' . $keyword . '%', 'OR');
            $cond->attachCondition('uc.credential_username', 'like', '%' . $keyword . '%', 'OR');
            $cond->attachCondition('tusr.usuprequest_reference', 'like', '%' . $keyword . '%', 'OR');
        }

        if (!empty($post['date_from'])) {
            $srch->addCondition('tusr.usuprequest_date', '>=', $post['date_from'] . ' 00:00:00');
        }

        if ($post['status'] > -1) {
            $srch->addCondition('tusr.usuprequest_status', '=', $post['status']);
        }

        if (!empty($post['date_to'])) {
            $srch->addCondition('tusr.usuprequest_date', '<=', $post['date_to'] . ' 23:59:59');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('reqStatusArr', User::getSupplierReqStatusArr($this->adminLangId));
        $this->set('canViewSellerApprovalRequests', $this->objPrivilege->canViewSellerApprovalRequests($this->admin_id, true));
        $this->set('canEditSellerApprovalRequests', $this->objPrivilege->canEditSellerApprovalRequests($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function viewSellerRequest($requestId)
    {
        $this->objPrivilege->canViewSellerApprovalRequests();
        $requestId = FatUtility::int($requestId);

        if (1 > $requestId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $userObj = new User();
        $srch = $userObj->getUserSupplierRequestsObj($requestId);
        $srch->addFld('tusr.*');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if (!$rs) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $supplierRequest = FatApp::getDb()->fetch($rs);
        if ($supplierRequest == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $supplierRequest["field_values"] = $userObj->getSupplierRequestFieldsValueArr($requestId, $this->adminLangId);

        $this->set('reqStatusArr', User::getSupplierReqStatusArr($this->adminLangId));
        $this->set('supplierRequest', $supplierRequest);
        $this->_template->render(false, false);
    }

    /* public function updateSellerRequestForm($requestId)
    {
        $this->objPrivilege->canEditSellerApprovalRequests();
        $requestId = FatUtility::int($requestId);

        if (1 > $requestId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = array('requestId' => $requestId);
        $frm = $this->supplierRequestForm();
        $frm->fill($data);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    } */

    public function updateSellerRequest()
    {
        $this->objPrivilege->canEditSellerApprovalRequests();
        $post = FatApp::getPostedData();

        if (1 > FatUtility::int($post['requestId']) || 1 > FatUtility::int($post['status'])) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $srequest_id = $post['requestId'];
        unset($post['requestId']);

        $userObj = new User();
        $srch = $userObj->getUserSupplierRequestsObj($srequest_id);
        $srch->addFld('tusr.*');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);

        $rs = $srch->getResultSet();
        if (!$rs) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $supplierRequest = FatApp::getDb()->fetch($rs);

        if ($supplierRequest == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $statusArr = array(User::SUPPLIER_REQUEST_APPROVED, User::SUPPLIER_REQUEST_CANCELLED);

        if (!in_array($post['status'], $statusArr)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Status_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (in_array($post['status'], $statusArr) && in_array($supplierRequest['usuprequest_status'], $statusArr)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Status_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!in_array($supplierRequest['usuprequest_status'], $statusArr) && in_array($post['status'], $statusArr)) {
            $post['request_id'] = $supplierRequest['usuprequest_id'];
            if (!$userObj->updateSupplierRequest($post)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($userObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        if ($post['status'] == User::SUPPLIER_REQUEST_APPROVED && $supplierRequest['usuprequest_status'] != User::SUPPLIER_REQUEST_APPROVED) {
            $userObj->setMainTableRecordId($supplierRequest['usuprequest_user_id']);
            if (!$userObj->activateSupplier(applicationConstants::ACTIVE)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($userObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $email = new EmailHandler();
        $supplierRequest['usuprequest_status'] = $post['status'];

        if (!$email->sendSupplierRequestStatusChangeNotification($this->adminLangId, $supplierRequest)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('LBL_Email_Could_Not_Be_Sent', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('LBL_Status_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function updateSellerRequest()
    {
        $this->objPrivilege->canEditSellerApprovalRequests();

        $frm = $this->supplierRequestForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srequest_id = $post['requestId'];
        unset($post['requestId']);

        $userObj = new User();
        $srch = $userObj->getUserSupplierRequestsObj($srequest_id);
        $srch->addFld('tusr.*');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);

        $rs = $srch->getResultSet();
        if (!$rs) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $supplierRequest = FatApp::getDb()->fetch($rs);

        if ($supplierRequest == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $statusArr = array(User::SUPPLIER_REQUEST_APPROVED, User::SUPPLIER_REQUEST_CANCELLED);

        if (!in_array($post['status'], $statusArr)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Status_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (in_array($post['status'], $statusArr) && in_array($supplierRequest['usuprequest_status'], $statusArr)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Status_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!in_array($supplierRequest['usuprequest_status'], $statusArr) && in_array($post['status'], $statusArr)) {
            $post['request_id'] = $supplierRequest['usuprequest_id'];
            if (!$userObj->updateSupplierRequest($post)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($userObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        if ($post['status'] == User::SUPPLIER_REQUEST_APPROVED && $supplierRequest['usuprequest_status'] != User::SUPPLIER_REQUEST_APPROVED) {
            $userObj->setMainTableRecordId($supplierRequest['usuprequest_user_id']);
            if (!$userObj->activateSupplier(applicationConstants::ACTIVE)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($userObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $email = new EmailHandler();
        $supplierRequest['usuprequest_status'] = $post['status'];
        $supplierRequest['usuprequest_comments'] = $post['comments'];

        if (!$email->sendSupplierRequestStatusChangeNotification($this->adminLangId, $supplierRequest)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('LBL_Email_Could_Not_Be_Sent', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('LBL_Status_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    } */

    public function downloadAttachment($recordId, $recordSubid)
    {
        $this->objPrivilege->canViewSellerApprovalRequests();

        $recordId = FatUtility::int($recordId);
        $recordSubid = FatUtility::int($recordSubid);

        if (1 > $recordId || 1 > $recordSubid) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SELLER_APPROVAL_FILE, $recordId, $recordSubid);

        if (false == $file_row) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($image_name, $file_row['afile_name']);
    }

    public function sellerCatalogRequestMsgForm($requestId = 0)
    {
        $this->objPrivilege->canEditSellerApprovalRequests();
        $requestId = FatUtility::int($requestId);
        $frm = $this->getCatalogRequestMessageForm($requestId);

        if (0 >= $requestId) {
            FatUtility::dieWithError($this->str_invalid_request_id);
        }
        $userObj = new User();
        $srch = $userObj->getUserSupplierRequestsObj($requestId);
        $srch->addFld('tusr.*');

        $rs = $srch->getResultSet();

        if (!$rs || FatApp::getDb()->fetch($rs) === false) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $this->set('requestId', $requestId);

        $this->set('frm', $frm);



        $searchFrm = $this->getCatalogRequestMessageSearchForm();
        $searchFrm->getField('requestId')->value = $requestId;
        $this->set('searchFrm', $searchFrm);

        $this->_template->render(false, false);
    }

    public function setUpCatalogRequestMessage()
    {
        $this->objPrivilege->canEditSellerCatalogRequests();
        $requestId = FatApp::getPostedData('requestId', null, '0');

        $frm = $this->getCatalogRequestMessageForm($requestId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $requestId = FatUtility::int($requestId);
        $admin_id = AdminAuthentication::getLoggedAdminId();

        $srch = new CatalogRequestSearch($this->adminLangId);
        $srch->addCondition('scatrequest_id', '=', $requestId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('scatrequest_id', 'scatrequest_status'));
        // die( $srch->getQuery());
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save catalog request message[ */
        $dataToSave = array(
        'scatrequestmsg_scatrequest_id' => $requestRow['scatrequest_id'],
        'scatrequestmsg_from_user_id' => 0,
        'scatrequestmsg_from_admin_id' => $admin_id,
        'scatrequestmsg_msg' => $post['message'],
        'scatrequestmsg_date' => date('Y-m-d H:i:s'),
        );
        $catRequestMsgObj = new CatalogRequestMessage();
        $catRequestMsgObj->assignValues($dataToSave, true);
        if (!$catRequestMsgObj->save()) {
            Message::addErrorMessage($catRequestMsgObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $scatrequestmsg_id = $catRequestMsgObj->getMainTableRecordId();
        if (!$scatrequestmsg_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_Technical_team', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendCatalogRequestMessageNotification($scatrequestmsg_id, $this->adminLangId)) {
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $this->set('scatrequestmsg_scatrequest_id', $requestId);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function catalogRequestMessageSearch()
    {
        $frm = $this->getCatalogRequestMessageSearchForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = 1;//FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $requestId = isset($post['requestId']) ? FatUtility::int($post['requestId']) : 0;

        $srch = new CatalogRequestMessageSearch();
        $srch->joinCatalogRequests();
        $srch->joinMessageUser();
        $srch->joinMessageAdmin();
        $srch->addCondition('scatrequestmsg_scatrequest_id', '=', $requestId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('scatrequestmsg_id', 'DESC');
        $srch->addMultipleFields(
            array( 'scatrequestmsg_id', 'scatrequestmsg_from_user_id', 'scatrequestmsg_from_admin_id',
            'admin_name', 'admin_username', 'admin_email', 'scatrequestmsg_msg',
            'scatrequestmsg_date', 'msg_user.user_name as msg_user_name', 'msg_user_cred.credential_username as msg_username',
            'msg_user_cred.credential_email as msg_user_email',
            'scatrequest_status' )
        );

        //echo $srch->getQuery();die;
        $rs = $srch->getResultSet();
        $messagesList = FatApp::getDb()->fetchAll($rs, 'scatrequestmsg_id');
        ksort($messagesList);



        $this->set('messagesList', $messagesList);
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);

        $startRecord = ($page - 1) * $pageSize + 1;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $json['totalRecords'] = $totalRecords;
        $json['startRecord'] = $startRecord;
        $json['endRecord'] = $endRecord;

        $json['html'] = $this->_template->render(false, false, 'users/catalog-request-messages-list.php', true);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'users/catalog-request-messages-list-load-more-btn.php', true);
        FatUtility::dieJsonSuccess($json);
    }

    public function sellerFormFieldsList()
    {
        $this->objPrivilege->canViewSellerApprovalForm();

        $obj = new User();
        $records = $obj->getSupplierFormFields($this->adminLangId);

        $this->set("arr_listing", $records);
        $this->set("yesNoArr", applicationConstants::getYesNoArr($this->adminLangId));
        $this->set("fieldTypeArr", User::getFieldTypes($this->adminLangId));
        $this->set("canEdit", $this->objPrivilege->canEditSellerApprovalForm($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function setupSellerForm()
    {
        $this->objPrivilege->canEditSellerApprovalForm();

        $frm = $this->getSupplierApprovalForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = SupplierFormFields::getSearchObject();
        $srch->addCondition('sf.sformfield_identifier', '=', $post['sformfield_identifier']);
        if (0 < $post['sformfield_id']) {
            $srch->addCondition('sf.sformfield_id', '!=', $post['sformfield_id']);
        }
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_choose_unique_identifier', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $sformfield_id = $post['sformfield_id'];
        unset($post['sformfield_id']);

        $record = new SupplierFormFields($sformfield_id);

        if ($sformfield_id == 0) {
            $display_order = $record->getMaxOrder();
            $post['sformfield_display_order'] = $display_order;
        }

        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($sformfield_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = SupplierFormFields::getAttributesByLangId($langId, $sformfield_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $sformfield_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('sformfieldId', $sformfield_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function sellerForm()
    {
        $this->objPrivilege->canViewSellerApprovalForm();

        $this->set("canEditSellerApprovalForm", $this->objPrivilege->canEditSellerApprovalForm($this->admin_id, true));
        $this->_template->render();
    }

    public function sellerApprovalForm($sformfield_id = 0)
    {
        $this->objPrivilege->canEditSellerApprovalForm();

        $sformfield_id = FatUtility::int($sformfield_id);

        $frm = $this->getSupplierApprovalForm();

        if (0 < $sformfield_id) {
            $attr = array('sformfield_id', 'sformfield_identifier', 'sformfield_type', 'sformfield_required');
            $data = SupplierFormFields::getAttributesById($sformfield_id, $attr);

            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('frm', $frm);
        $this->set('sformfield_id', $sformfield_id);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setupSellerLangForm()
    {
        $this->objPrivilege->canEditSellerApprovalForm();
        $post = FatApp::getPostedData();

        $sformfield_id = $post['sformfield_id'];
        $lang_id = $post['sformfieldlang_lang_id'];

        if ($sformfield_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getSupplierApprovalLangForm($sformfield_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['sformfield_id']);
        unset($post['lang_id']);
        $data = array(
        'sformfieldlang_lang_id' => $lang_id,
        'sformfieldlang_sformfield_id' => $sformfield_id,
        'sformfield_caption' => $post['sformfield_caption'],
        'sformfield_comment' => $post['sformfield_comment'],
        );

        $obj = new SupplierFormFields($sformfield_id);
        if (!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(SupplierFormFields::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($sformfield_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = SupplierFormFields::getAttributesByLangId($langId, $sformfield_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('sformfieldId', $sformfield_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langSellerApprovalForm($sformfield_id = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $this->objPrivilege->canEditSellerApprovalForm();

        $sformfield_id = FatUtility::int($sformfield_id);
        $lang_id = FatUtility::int($lang_id);

        if ($sformfield_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getSupplierApprovalLangForm($sformfield_id, $lang_id);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(SupplierFormFields::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($sformfield_id, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = SupplierFormFields::getAttributesByLangId($lang_id, $sformfield_id);
        }
        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('sformfield_id', $sformfield_id);
        $this->set('sformfield_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function sellerCatalogRequests()
    {
        $this->objPrivilege->canViewSellerCatalogRequests();

        $frmSearch = $this->catalogRequestSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function sellerCatalogRequestSearch()
    {
        $this->objPrivilege->canViewSellerCatalogRequests();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srchForm = $this->catalogRequestSearchForm();

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $post = $srchForm->getFormDataFromArray($data);

        $userObj = new User();
        $srch = $userObj->getUserCatalogRequestsObj();
        $srch->addFld('tucr.*');
        $srch->addOrder('scatrequest_date', 'desc');

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('tucr.scatrequest_reference', '=', $post['keyword'], 'AND');
            $cond = $srch->addCondition('tucr.scatrequest_title', '=', '%' . $post['keyword'] . '%', 'OR');
            $cond->attachCondition('u.user_name', 'like', '%' . $post['keyword'] . '%', 'OR');
            $cond->attachCondition('uc.credential_email', 'like', '%' . $post['keyword'] . '%', 'OR');
            $cond->attachCondition('uc.credential_username', 'like', '%' . $post['keyword'] . '%', 'OR');
        }

        if (!empty($post['date_from'])) {
            $srch->addCondition('tucr.scatrequest_date', '>=', $post['date_from'] . ' 00:00:00');
        }

        if ($post['status'] > -1) {
            $srch->addCondition('tucr.scatrequest_status', '=', $post['status']);
        }

        if (!empty($post['date_to'])) {
            $srch->addCondition('tucr.scatrequest_date', '<=', $post['date_to'] . ' 23:59:59');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('reqStatusArr', User::getCatalogReqStatusArr($this->adminLangId));
        $this->set('reqStatusClassArr', User::getCatalogRequestClassArr());
        $this->set('canViewSellerCatalogRequests', $this->objPrivilege->canViewSellerCatalogRequests($this->admin_id, true));
        $this->set('canEditSellerCatalogRequests', $this->objPrivilege->canEditSellerCatalogRequests($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function downloadAttachedFileForCatalogRequest($recordId, $recordSubid = 0)
    {
        $recordId = FatUtility::int($recordId);

        if (1 > $recordId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SELLER_CATALOG_REQUEST, $recordId, $recordSubid);

        if (false == $file_row) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    public function viewCatalogRequest($requestId)
    {
        $this->objPrivilege->canViewSellerCatalogRequests();
        $requestId = FatUtility::int($requestId);

        if (1 > $requestId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $userObj = new User();
        $srch = $userObj->getUserCatalogRequestsObj($requestId);
        $srch->addFld('tucr.*');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        if (!$rs) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $catalogRequest = FatApp::getDb()->fetch($rs);
        if ($catalogRequest == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($attachedFile = AttachedFile::getAttachment(AttachedFile::FILETYPE_SELLER_CATALOG_REQUEST, $requestId)) {
            $this->set('attachedFile', $attachedFile['afile_name']);
        }

        $this->set('reqStatusArr', User::getCatalogReqStatusArr($this->adminLangId));
        $this->set('catalogRequest', $catalogRequest);
        $this->_template->render(false, false);
    }

    public function updateCatalogRequestForm($requestId)
    {
        $this->objPrivilege->canViewSellerCatalogRequests();
        $requestId = FatUtility::int($requestId);

        if (1 > $requestId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = array('requestId' => $requestId);
        $frm = $this->catalogRequestForm();
        $frm->fill($data);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function updateCatalogRequest()
    {
        $this->objPrivilege->canEditSellerCatalogRequests();
        $frm = $this->catalogRequestForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $scatrequest_id = $post['requestId'];
        unset($post['requestId']);

        $userObj = new User();
        $srch = $userObj->getUserCatalogRequestsObj($scatrequest_id);
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', Shop::DB_TBL_PREFIX . 'user_id = tucr.' . User::DB_TBL_USR_CATALOG_REQ_PREFIX . 'user_id', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = ' . $this->adminLangId, 's_l');
        $srch->addFld('tucr.*');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);

        $rs = $srch->getResultSet();
        if (!$rs) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $catalogRequest = FatApp::getDb()->fetch($rs);

        if ($catalogRequest == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $statusArr = array(User::CATALOG_REQUEST_APPROVED, User::CATALOG_REQUEST_CANCELLED);

        if (!in_array($post['status'], $statusArr)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Status_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (in_array($post['status'], $statusArr) && in_array($catalogRequest['scatrequest_status'], $statusArr)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Status_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        if (!in_array($catalogRequest['scatrequest_status'], $statusArr) && in_array($post['status'], $statusArr)) {
            $post['request_id'] = $catalogRequest['scatrequest_id'];
            if (!$userObj->updateCatalogRequest($post)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($userObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $email = new EmailHandler();
        $catalogRequest['scatrequest_status'] = $post['status'];
        $catalogRequest['scatrequest_comments'] = $post['comments'];

        if (!$email->sendCatalogRequestStatusChangeNotification($this->adminLangId, $catalogRequest)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('LBL_Email_Could_Not_Be_Sent', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('LBL_Status_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteFormField()
    {
        $this->objPrivilege->canEditSellerApprovalForm();

        $sformfield_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($sformfield_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new SupplierFormFields($sformfield_id);
        if (!$obj->canDeleteRecord()) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function setFieldsOrder()
    {
        $this->objPrivilege->canEditSellerApprovalForm();

        $post = FatApp::getPostedData();

        if (!empty($post)) {
            $obj = new SupplierFormFields();
            if (!$obj->updateOrder($post['formFields'])) {
                Message::addErrorMessage($obj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }

            $this->set('msg', Labels::getLabel('LBL_Order_Updated_Successfully', $this->adminLangId));
            $this->_template->render(false, false, 'json-success.php');
        }
    }

    public function autoComplete()
    {
        $this->objPrivilege->canViewUsers();
        $userObj = new User();
        $srch = $userObj->getUserSearchObj(array( 'u.user_name', 'u.user_id', 'credential_username', 'credential_email'));

        $post = FatApp::getPostedData();
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('u.user_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('uc.credential_username', 'LIKE', '%' . $post['keyword'] . '%');
            /* $cnd->attachCondition('uc.credential_email', 'LIKE', '%' . $post['keyword'] . '%'); */
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $this->set('data', $db->fetchAll($rs, 'user_id'));
        $this->_template->render(false, false);
    }

    public function autoCompleteJson()
    {
        $pagesize = 20;
        $post = FatApp::getPostedData();
        $this->objPrivilege->canViewUsers();

        $skipDeletedUser = true;
        if (isset($post['deletedUser']) && $post['deletedUser'] == true) {
            $skipDeletedUser = false;
        }

        $userObj = new User();
        $srch = $userObj->getUserSearchObj(array( 'u.user_name', 'u.user_id', 'credential_username', 'credential_email'), true, $skipDeletedUser);
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'shop_user_id = if(u.user_parent > 0, user_parent, u.user_id)', 'shop');
        if (!$skipDeletedUser) {
            $srch->addCondition('user_deleted', '=', applicationConstants::YES);
        }
        $srch->addOrder('credential_email', 'ASC');

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cond = $srch->addCondition('uc.credential_username', 'like', '%' . $keyword . '%');
            $cond->attachCondition('uc.credential_email', 'like', '%' . $keyword . '%', 'OR');
            $cond->attachCondition('u.user_name', 'like', '%' . $keyword . '%');
        }

        if (!empty($post['user_is_buyer'])) {
            $user_is_buyer = FatUtility::int($post['user_is_buyer']);
            $cnd = $srch->addCondition('u.user_is_buyer', '=', $user_is_buyer);
        }

        if (!empty($post['user_is_supplier'])) {
            $user_is_supplier = FatUtility::int($post['user_is_supplier']);
            if (!empty($post['user_is_buyer'])) {
                $cnd->attachCondition('u.' . User::DB_TBL_PREFIX . 'is_supplier', '=', $user_is_supplier);
            } else {
                $srch->addCondition('u.' . User::DB_TBL_PREFIX . 'is_supplier', '=', $user_is_supplier);
            }
        }

        if (isset($post['user_is_affiliate'])) {
            $user_is_affiliate = FatUtility::int($post['user_is_affiliate']);
            $srch->addCondition('u.user_is_affiliate', '=', $user_is_affiliate);
        }

        if (isset($post['credential_active'])) {
            $credential_active = $post['credential_active'];
            $srch->addCondition('uc.credential_active', '=', $credential_active);
        }

        if (isset($post['credential_verified'])) {
            $credential_verified = $post['credential_verified'];
            $srch->addCondition('uc.credential_verified', '=', $credential_verified);
        }

        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $users = $db->fetchAll($rs, 'user_id');

        $json = array();
        foreach ($users as $key => $user) {
            $json[] = array(
            'id' => $key,
            'name' => strip_tags(html_entity_decode($user['user_name'], ENT_QUOTES, 'UTF-8')),
            'username' => strip_tags(html_entity_decode($user['credential_username'], ENT_QUOTES, 'UTF-8')),
            'credential_email' => strip_tags(html_entity_decode($user['credential_email'], ENT_QUOTES, 'UTF-8')),
            );
        }

        die(json_encode($json));
    }

    public function autoCompleteShop()
    {
        $pagesize = 20;
        $post = FatApp::getPostedData();
        $this->objPrivilege->canViewUsers();

        $skipDeletedUser = true;
        if (isset($post['deletedUser']) && $post['deletedUser'] == true) {
            $skipDeletedUser = false;
        }

        $userObj = new User();
        $srch = $userObj->getUserSearchObj(array( 'u.user_id'), true, $skipDeletedUser);
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'shop_user_id = if(u.user_parent > 0, user_parent, u.user_id)', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = ' . $this->adminLangId, 's_l');
        
        if (!$skipDeletedUser) {
            $srch->addCondition('user_deleted', '=', applicationConstants::YES);
        }
        $srch->addOrder('credential_email', 'ASC');

        $shop_name = FatApp::getPostedData('shopname', null, '');
        if (!empty($shop_name)) {
            $cond = $srch->addCondition('shop.shop_identifier', 'like', '%' . $shop_name . '%');
            $cond->attachCondition('s_l.shop_name', 'like', '%' . $shop_name . '%', 'OR');
        }

        if (!empty($post['user_is_buyer'])) {
            $user_is_buyer = FatUtility::int($post['user_is_buyer']);
            $cnd = $srch->addCondition('u.user_is_buyer', '=', $user_is_buyer);
        }

        if (!empty($post['user_is_supplier'])) {
            $user_is_supplier = FatUtility::int($post['user_is_supplier']);
            if (!empty($post['user_is_buyer'])) {
                $cnd->attachCondition('u.' . User::DB_TBL_PREFIX . 'is_supplier', '=', $user_is_supplier);
            } else {
                $srch->addCondition('u.' . User::DB_TBL_PREFIX . 'is_supplier', '=', $user_is_supplier);
            }
        }

        if (isset($post['user_is_affiliate'])) {
            $user_is_affiliate = FatUtility::int($post['user_is_affiliate']);
            $srch->addCondition('u.user_is_affiliate', '=', $user_is_affiliate);
        }

        if (isset($post['credential_active'])) {
            $credential_active = $post['credential_active'];
            $srch->addCondition('uc.credential_active', '=', $credential_active);
        }

        if (isset($post['credential_verified'])) {
            $credential_verified = $post['credential_verified'];
            $srch->addCondition('uc.credential_verified', '=', $credential_verified);
        }
        $srch->addMultipleFields(array('shop_id', 'shop_user_id', 'IFNULL(shop_name, shop_identifier) as shop_name'));

        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $shops = $db->fetchAll($rs, 'shop_id');

        $json = array();
        foreach ($shops as $key => $shop) {
            $json[] = array(
            'id' => $key,
            'name' => strip_tags(html_entity_decode($shop['shop_name'], ENT_QUOTES, 'UTF-8')),
            );
        }

        die(json_encode($json));
    }

    public function verify()
    {
        $this->objPrivilege->canVerifyUsers();

        $userId = FatApp::getPostedData('userId', FatUtility::VAR_INT);
        $v = FatApp::getPostedData('v', FatUtility::VAR_INT);

        $userObj = new User($userId);
        if (!$userObj->verifyAccount($v)) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', ((1 == $v) ? Labels::getLabel('MSG_Account_Unverified', $this->adminLangId) : Labels::getLabel('MSG_Account_Verified', $this->adminLangId)));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditUsers();
        $userId = FatApp::getPostedData('userId', FatUtility::VAR_INT, 0);
        if (0 == $userId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $userObj = new User($userId);
        $srch = $userObj->getUserSearchObj();
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs);
        $status = ($data['credential_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateUserStatus($userId, $status);
        $shopId = Shop::getAttributesByUserId($userId, 'shop_id');
        if (0 < $shopId) {
            Product::updateMinPrices(0, $shopId);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditUsers();

        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $userIdsArr = FatUtility::int(FatApp::getPostedData('user_ids'));
        if (empty($userIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($userIdsArr as $userId) {
            if (1 > $userId) {
                continue;
            }

            $this->updateUserStatus($userId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateUserStatus($userId, $status)
    {
        $status = FatUtility::int($status);
        $userId = FatUtility::int($userId);
        if (1 > $userId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $userObj = new User($userId);

        if (!$userObj->activateAccount($status)) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function activate()
    {
        $this->objPrivilege->canEditUsers();

        $userId = FatApp::getPostedData('userId', FatUtility::VAR_INT);
        $v = FatApp::getPostedData('v', FatUtility::VAR_INT);

        $userObj = new User($userId);

        if (!$userObj->activateAccount($v)) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', ((1 == $v) ? Labels::getLabel('MSG_Account_Deactivated', $this->adminLangId) : Labels::getLabel('MSG_Account_Activated', $this->adminLangId)));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function sendMailForm($user_id)
    {
        $this->objPrivilege->canEditUsers();
        $user_id = FatUtility::int($user_id);
        $userObj = new User($user_id);
        $user = $userObj->getUserInfo(null, false, false);
        if (!$user) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $frm = $this->getSendMailForm($user_id);

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function sendMail()
    {
        $this->objPrivilege->canEditUsers();
        $frm = $this->getSendMailForm();

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $user_id = FatUtility::int($post['user_id']);
        $userObj = new User($user_id);
        $user = $userObj->getUserInfo(null, false, false);
        if (!$user || empty($user['credential_email'])) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $data = array(
            'user_name' => trim($user['user_name']),
            'mail_subject' => trim($post['mail_subject']),
            'mail_message' => nl2br($post["mail_message"]),
            'credential_email' => $user['credential_email'],
            'user_phone' => $user['user_phone']
        );
        
        $email = new EmailHandler();
        if (!$email->sendEmailToUser($this->adminLangId, $data)) {
            Message::addErrorMessage($email->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $this->set('msg', Labels::getLabel('LBL_Your_Message_Sent_To', $this->adminLangId) . ' - ' . $user["credential_email"]);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function markSellerAsBuyer()
    {
        $this->objPrivilege->canEditUsers();
        $userId = FatApp::getPostedData('userId', FatUtility::VAR_INT, 0);
        if (1 > $userId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $userObj = new User($userId);
        $user = $userObj->getUserInfo(null, false, false);
        if (!$user) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $userObj->assignValues(['user_is_buyer' => User::USER_TYPE_BUYER]);
        if (!$userObj->save()) {
            Message::addErrorMessage($userObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function image($userId, $sizeType = '', $afile_id = 0){
    $default_image = 'user_deafult_image.jpg';
    $userId = FatUtility::int($userId);
    $afile_id = FatUtility::int($afile_id);
    if( $afile_id > 0 ){
    $res = AttachedFile::getAttributesById($afile_id);
    if(!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE){
                $file_row = $res;
    }
    } else {
    $file_row = AttachedFile::getAttachment( AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE, $userId );
    }
    $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

    switch( strtoupper($sizeType) ){
    case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage( $image_name, $w, $h, $default_image );
    break;
    default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage( $image_name, $w, $h, $default_image );
    break;
    }
    } */

    private function getCatalogRequestMessageSearchForm()
    {
        $frm = new Form('frmCatalogRequestMsgsSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'requestId');
        return $frm;
    }

    private function supplierRequestForm()
    {
        $frm = new Form('supplierRequestForm');

        $statusArr = User::getSupplierReqStatusArr($this->adminLangId);
        unset($statusArr[User::SUPPLIER_REQUEST_PENDING]);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'status', $statusArr, '')->requirements()->setRequired();
        $frm->addHiddenField('', 'requestId', 0);
        $frm->addTextArea('', 'comments', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getCatalogRequestMessageForm($requestId)
    {
        $frm = new Form('catalogRequestMsgForm');

        $frm->addHiddenField('', 'requestId', $requestId);
        $frm->addTextArea(Labels::getLabel('LBL_Message', $this->adminLangId), 'message')->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $this->adminLangId));
        return $frm;
    }

    private function supplierRequestSearchForm()
    {
        $frm = new Form('frmSupplierReqSrch', array('id' => 'frmSupplierReqSrch'));
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');

        $statusArr = array('-1' => Labels::getLabel('LBL_All', $this->adminLangId)) + User::getSupplierReqStatusArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'status', $statusArr, '', array(), '');
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array( 'readonly' => 'readonly', 'class' => 'field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array( 'readonly' => 'readonly', 'class' => 'field--calender' ));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getSupplierApprovalForm()
    {
        $frm = new Form('frmSuppiler');
        $frm->addHiddenField('', 'sformfield_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'sformfield_identifier');
        $frm->addSelectBox(Labels::getLabel('LBL_Required', $this->adminLangId), 'sformfield_required', applicationConstants::getYesNoArr($this->adminLangId), -1, array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_Field_Type', $this->adminLangId), 'sformfield_type', User::getFieldTypes($this->adminLangId), -1, array(), '');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getSupplierApprovalLangForm($sformfield_id, $lang_id)
    {
        if (0 < $sformfield_id) {
            $attr = array('sformfield_id', 'sformfield_type');
            $data = SupplierFormFields::getAttributesById($sformfield_id, $attr);
        }

        $frm = new Form('frmSuppilerLang');
        $frm->addHiddenField('', 'sformfield_id', $sformfield_id);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'sformfieldlang_lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField('Caption', 'sformfield_caption');

        $frm->addTextarea(Labels::getLabel('LBL_Comments', $this->adminLangId), 'sformfield_comment');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    /* private function getUserSearchForm() {
    $frm = new Form('frmUserSearch');
    $keyword = $frm->addTextBox(Labels::getLabel('LBL_Name_Or_Email',$this->adminLangId), 'keyword','',array('id'=>'keyword','autocomplete'=>'off'));
    $keyword->setFieldTagAttribute('onKeyUp','usersAutocomplete(this)');

    $arr_options = array('-1'=>Labels::getLabel('LBL_Does_Not_Matter',$this->adminLangId))+applicationConstants::getActiveInactiveArr($this->adminLangId);
    $arr_options1 = array('-1'=>Labels::getLabel('LBL_Does_Not_Matter',$this->adminLangId))+applicationConstants::getYesNoArr($this->adminLangId);
    $arr_options2 = array('-1'=>Labels::getLabel('LBL_Does_Not_Matter',$this->adminLangId))+User::getUserTypesArr($this->adminLangId);

    $frm->addSelectBox(Labels::getLabel('LBL_Active_Users',$this->adminLangId), 'user_active', $arr_options, -1, array(),'');
    $frm->addSelectBox(Labels::getLabel('LBL_Email_Verified',$this->adminLangId), 'user_verified', $arr_options1, -1, array(), '');
    $frm->addSelectBox(Labels::getLabel('LBL_User_Type',$this->adminLangId), 'type', $arr_options2, -1, array(),'');

    $frm->addDateField(Labels::getLabel('LBL_Reg._Date_From',$this->adminLangId), 'user_regdate_from');
    $frm->addDateField(Labels::getLabel('LBL_Reg._Date_To',$this->adminLangId), 'user_regdate_to');

    $frm->addHiddenField('','page',1);
    $fld_submit=$frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search',$this->adminLangId));
    $fld_cancel = $frm->addButton("","btn_clear",Labels::getLabel('LBL_Clear_Search',$this->adminLangId));
    $fld_submit->attachField($fld_cancel);
    return $frm;
    } */

    private function getForm($user_id = 0)
    {
        $user_id = FatUtility::int($user_id);
        $frm = new Form('frmUser', array('id' => 'frmUser'));
        $frm->addHiddenField('', 'user_id', $user_id);
        if(1 > $user_id){            
            $userTypesArr = User::getUserTypesArr($this->adminLangId);  
            unset($userTypesArr[User::USER_TYPE_AFFILIATE]);
            $fld = $frm->addSelectBox(Labels::getLabel('LBL_User_Type', $this->adminLangId), 'user_type', $userTypesArr, [], [], Labels::getLabel('LBL_Select', $this->adminLangId));
            $fld->requirement->setRequired(true);
        }
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Username', $this->adminLangId), 'credential_username', '');
        if (1 > $user_id) {
            $fld->setUnique('tbl_user_credentials', 'credential_username', 'credential_user_id', 'user_id', 'user_id');
            $fld->requirements()->setRequired();
            $fld->requirements()->setUsername();
        }
        $frm->addRequiredField(Labels::getLabel('LBL_Customer_Name', $this->adminLangId), 'user_name');
        $frm->addDateField(Labels::getLabel('LBL_Date_Of_Birth', $this->adminLangId), 'user_dob', '', array('readonly' => 'readonly'));

        $phnFld = $frm->addTextBox(Labels::getLabel('LBL_Phone', $this->adminLangId), 'user_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        
        $fld = $frm->addEmailField(Labels::getLabel('LBL_Email', $this->adminLangId), 'credential_email', '');
        if (1 > $user_id) {
            $fld->setUnique('tbl_user_credentials', 'credential_email', 'credential_user_id', 'user_id', 'user_id');
            $fld->requirements()->setRequired();
        }

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->adminLangId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'user_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 223));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->adminLangId), 'user_state_id', array())->requirement->setRequired(true);
        $frm->addTextBox(Labels::getLabel('LBL_City', $this->adminLangId), 'user_city');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getChangePasswordForm($user_id = 0)
    {
        $user_id = FatUtility::int($user_id);
        $frm = new Form('changePwdFrm');
        $frm->addHiddenField('', 'user_id', $user_id);

        $newPwd = $frm->addPasswordField(
            Labels::getLabel('LBL_New_Password', $this->adminLangId),
            'new_password',
            '',
            array('id' => 'new_password')
        );
        $newPwd->requirements()->setRequired();

        $conNewPwd = $frm->addPasswordField(
            Labels::getLabel('LBL_Confirm_New_Password', $this->adminLangId),
            'conf_new_password',
            '',
            array('id' => 'conf_new_password')
        );
        $conNewPwdReq = $conNewPwd->requirements();
        $conNewPwdReq->setRequired();
        $conNewPwdReq->setCompareWith('new_password', 'eq');
        $conNewPwdReq->setCustomErrorMessage(Labels::getLabel('LBL_Confirm_Password_Not_Matched', $this->adminLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId), array('id' => 'btn_submit'));
        return $frm;
    }

    private function getSendMailForm($user_id = 0)
    {
        $user_id = FatUtility::int($user_id);
        $frm = new Form('sendMailFrm');
        $frm->addHiddenField('', 'user_id', $user_id);

        $frm->addTextBox(Labels::getLabel('LBL_Subject', $this->adminLangId), 'mail_subject')->requirements()->setRequired(true);
        $frm->addTextArea(Labels::getLabel('LBL_Message', $this->adminLangId), 'mail_message')->requirements()->setRequired(true);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $this->adminLangId), array('id' => 'btn_submit'));
        return $frm;
    }

    private function catalogRequestSearchForm()
    {
        $frm = new Form('frmCatalogReqSrch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '');

        $statusArr = array('-1' => Labels::getLabel('LBL_All', $this->adminLangId)) + User::getCatalogReqStatusArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'status', $statusArr, '', array(), '');
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array( 'readonly' => 'readonly', 'class' => 'field--calender' ));
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array( 'readonly' => 'readonly', 'class' => 'field--calender' ));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function catalogRequestForm()
    {
        $frm = new Form('catalogRequestForm');

        $statusArr = User::getCatalogReqStatusArr($this->adminLangId);
        unset($statusArr[User::CATALOG_REQUEST_PENDING]);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'status', $statusArr, '')->requirements()->setRequired();
        $frm->addHiddenField('', 'requestId', 0);
        $frm->addTextArea('', 'comments', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getBankInfoForm()
    {
        $frm = new Form('frmBankInfo');
        $frm->addRequiredField(Labels::getLabel('LBL_Bank_Name', $this->adminLangId), 'ub_bank_name', '');
        $frm->addRequiredField(Labels::getLabel('LBL_Account_Holder_Name', $this->adminLangId), 'ub_account_holder_name', '');
        $frm->addRequiredField(Labels::getLabel('LBL_Account_Number', $this->adminLangId), 'ub_account_number', '');
        $frm->addRequiredField(Labels::getLabel('LBL_IFSC_Swift_Code', $this->adminLangId), 'ub_ifsc_swift_code', '');
        $frm->addTextArea(Labels::getLabel('LBL_Bank_Address', $this->adminLangId), 'ub_bank_address', '');
        $frm->addHiddenField('', 'user_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function addUserTransactionForm($langId)
    {
        $frm = new Form('frmUserTransaction');
        $frm->addHiddenField('', 'user_id');
        $typeArr = Transactions::getCreditDebitTypeArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'type', $typeArr)->requirements()->setRequired(true);
        $frm->addRequiredField(Labels::getLabel('LBL_Amount', $this->adminLangId), 'amount')->requirements()->setFloatPositive();
        $frm->addTextArea(Labels::getLabel('LBL_Description', $this->adminLangId), 'description')->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function addUserRewardPointsForm($langId)
    {
        $frm = new Form('frmUserRewardPoints');
        $frm->addHiddenField('', 'urp_user_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Points', $this->adminLangId), 'urp_points')->requirements()->setIntPositive();
        $frm->addTextArea(Labels::getLabel('LBL_Comments', $this->adminLangId), 'urp_comments')->requirements()->setRequired();
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Validity', $this->adminLangId), 'validity');
        $fld->requirements()->setIntPositive();
        $fld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Leave_this_field_empty_ever_valid_reward_points.', $this->adminLangId) . '</small>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getUserAddressForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmAddress');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Address_Label', $this->adminLangId), 'addr_title');
        $fld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_E.g:_My_Office_Address', $langId));
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $this->adminLangId), 'addr_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Address_Line_1', $this->adminLangId), 'addr_address1');
        $frm->addTextBox(Labels::getLabel('LBL_Address_Line_2', $this->adminLangId), 'addr_address2');
        $frm->addRequiredField(Labels::getLabel('LBL_City', $this->adminLangId), 'addr_city');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($langId);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->adminLangId), 'addr_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY'));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->adminLangId), 'addr_state_id', array())->requirement->setRequired(true);
        $frm->addTextBox(Labels::getLabel('LBL_Postal_Code', $this->adminLangId), 'addr_zip');
        $phnFld = $frm->addTextBox(Labels::getLabel('LBL_Phone', $this->adminLangId), 'addr_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $frm->addHiddenField('', 'addr_record_id');
        $frm->addHiddenField('', 'addr_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }
}
