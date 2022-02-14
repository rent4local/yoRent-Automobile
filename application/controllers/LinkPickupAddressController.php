<?php

class LinkPickupAddressController extends SellerBaseController
{
    public function index()
    {
        $this->userPrivilege->canViewLinkPickupSection(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        $fulFillmentType = Shop::getAttributesByUserId($this->userParentId, 'shop_fulfillment_type', false);
        if($fulFillmentType == Shipping::FULFILMENT_SHIP) {
            Message::addInfo(Labels::getLabel("MSG_Pickup_option_is_not_available_for_this_shop", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
        }

        $this->set("canEdit", $this->userPrivilege->canEditLinkPickupSection(UserAuthentication::getLoggedUserId(), true));
        
        $addresses = $this->getFormattedPickupAddresses();
        $linkAddFrm = $this->getLinkPickupAddressForm($addresses);
        
        $this->_template->addCss(array('css/custom-tagify.css'));
        $this->set("linkAddFrm", $linkAddFrm);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function searchLinkedAddresses()
    {
        $this->userPrivilege->canViewLinkPickupSection(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $addrId = FatApp::getPostedData('addr_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $prodSrch = SellerProduct::searchLinkedPickupAddresses($this->siteLangId);
       
        // if ($keyword != '') {
        //     $cnd = $prodSrch->addCondition('product_name', 'like', "%$keyword%");
        //     $cnd->attachCondition('product_identifier', 'LIKE', '%' . $keyword . '%', 'OR');
        // }

        $prodSrch->addCondition('selprod_user_id', '=', $userId);
        $prodSrch->setPageNumber($page);
        $prodSrch->setPageSize($pagesize);
        $prodSrch->addGroupBy('addr_id');
        $rs = $prodSrch->getResultSet();
        $db = FatApp::getDb();
        $data = $db->fetchAll($rs);
        
        $arrListing = array();
        foreach ($data as $val) {
            $addressId = $val['addr_id'];
            $srch = SellerProduct::searchLinkedPickupAddresses($this->siteLangId);
            $srch->addCondition('addr_id', '=', $addressId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $arrListing[$addressId] = $db->fetchAll($rs);
        }
        
        $this->set("arrListing", $arrListing);

        $this->set('page', $page);
        $this->set('pageCount', $prodSrch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $prodSrch->recordCount());
        $this->set('canEdit', $this->userPrivilege->canEditLinkPickupSection(UserAuthentication::getLoggedUserId(), true));
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    public function linkPickupAddresses()
    {
        $this->userPrivilege->canEditLinkPickupSection(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
    
        $address_id = FatUtility::int($post['pickup_address']);

        if ($address_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_Valid_Address', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $productIds = (!empty($post['product_names'])) ? $post['product_names'] : array();

        if (count($productIds) < 1) {
            Message::addErrorMessage(Labels::getLabel("MSG_You_need_to_add_atleast_one_verification_field", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($post['pickup_address']);
        $sellerProdObj = new SellerProduct();
        if (!$sellerProdObj->addUpdatePickupAddressToSelprod($address_id,$productIds)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Products_linked_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteLinkedProduct($addr_id, $selprod_id)
    {
        $addr_id = FatUtility::int($addr_id);
        $selprod_id = FatUtility::int($selprod_id);
        if (!$addr_id || !$selprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS, array('smt' => 'sptpa_addr_id = ? AND sptpa_selprod_id = ?', 'vals' => array($addr_id, $selprod_id)))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->set('addr_id', $addr_id);
        $this->set('msg', Labels::getLabel('LBL_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getLinkSelprodList($addr_id)
    {
        $addr_id = FatUtility::int($addr_id);
        $srch = SellerProduct::searchLinkedPickupAddresses($this->siteLangId);
        $srch->addCondition('sptpa_addr_id', '=', $addr_id);
        // $srch->addCondition(SellerProduct::DB_TBL_PRODUCT_TO_VERIFICATION_FLD_PREFIX . 'product_id', '=', $product_id);
        // $srch->addOrder('ptvf_vflds_id', 'DESC');
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetchAll($rs);

        foreach ($data as $key => $value) {
            $data[$key]['name'] = strip_tags(html_entity_decode($value['selprod_title'], ENT_QUOTES, 'UTF-8'));
            $data[$key]['id'] = strip_tags(html_entity_decode($value['selprod_id'], ENT_QUOTES, 'UTF-8'));
        }

        $json = array(
            'linkedProducts' => $data
        );
        FatUtility::dieJsonSuccess($json);
    }

    private function getLinkPickupAddressForm($addresses = [])
    {
        $frm = new Form('frmLinkPickupAddFrm');

        $frm->addHiddenField('', 'addr_id', 0);

        $frm->addSelectBox(Labels::getLabel('LBL_Pickup_Addresses', $this->siteLangId), 'pickup_address', $addresses, '',array('id' => 'pickupAddress'));

        $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'product_names', [], '', array('placeholder' => Labels::getLabel('LBL_Select_Product', $this->siteLangId)));

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $this->siteLangId));
        return $frm;
    }

    private function getFormattedPickupAddresses()
    {
        $shopDetails = Shop::getAttributesByUserId($this->userParentId, null, false);
        $address = new Address(0, $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_SHOP_PICKUP, $shopDetails['shop_id']);
        
        $addArr = [];
        foreach($addresses as $val) {
            $addArr[$val['addr_id']] = $val['addr_name'] . ', ' . $val['addr_address1'] . ', ' . $val['addr_address2'] . ', ' . $val['addr_city'] . ', ' . $val['state_name'] . ', ' . $val['country_name'];
        }

        return $addArr;
    }
}
