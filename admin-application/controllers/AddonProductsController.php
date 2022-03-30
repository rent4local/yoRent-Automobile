<?php

class AddonProductsController extends AdminBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $searchForm = $this->searchForm();
        $this->set('searchForm', $searchForm);
        $this->_template->render(true, true);
    }

    public function search()
    {
        $searchForm = $this->searchForm();
        $post = $searchForm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = new searchBase(SellerProduct::DB_TBL_SELLER_PROD_ADDON, 'spa');
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'spa.spa_addon_product_id = sp.selprod_id', 'sp');
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'ts.shop_user_id = sp.selprod_user_id' , 'ts');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'ts_l.shoplang_shop_id = ts.shop_id and ts_l.shoplang_lang_id = ' . $this->adminLangId, 'ts_l');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_user_id = u.user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->adminLangId, 'sp_l');
        $keyword = trim(FatApp::getPostedData('keyword', null, ''));
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('selprod_title', 'like', '%' . $keyword . '%');
        }
        $shop_name = trim(FatApp::getPostedData('shop_name', null, ''));
        if (!empty($shop_name)) {
            $cnd = $srch->addCondition('ts_l.shop_name', 'like', '%' . $shop_name . '%');
            $cnd->attachCondition('user_name', 'like', '%' . $shop_name . '%', 'OR');
        }
        $srch->addMultipleFields(array('selprod_id', 'selprod_title', 'user_name','IFNULL(ts_l.shop_name,ts.shop_identifier) as shop_name, shop_id, shop_user_id'));
        $srch->addGroupBy('spa_addon_product_id');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs, 'selprod_id');
        $attachedProductsData = [];
        if (!empty($records)) {
            $addonProdIds = array_column($records, 'selprod_id');
            $attachedProducts = $this->getAttachedSellerProducts($addonProdIds);
            if (!empty($attachedProducts)) {
                foreach ($attachedProducts as $product) {
                    $options = SellerProduct::getSellerProductOptions($product['selprod_id'], true, $this->adminLangId);
                    if (!empty($options)) {
                        $optionvalueNames = array_column($options, 'optionvalue_name');
                        $optionName = implode('|', $optionvalueNames);
                        $product['selprod_title'] = $product['selprod_title'] . ' - ' . $optionName;
                    }
                    $attachedProductsData[$product['spa_addon_product_id']][] = $product;
                }
            }
        }

        $this->set('arrListing', $records);
        $this->set('attachedProductsData', $attachedProductsData);
        $this->set('adminLangId', $this->adminLangId);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function listing() 
    {
        $searchForm = $this->getListingSearchForm();
        $this->set('searchForm', $searchForm);
        $this->_template->render(true, true);
    }

    public function productListing()
    {
        $searchForm = $this->getListingSearchForm();
        $post = $searchForm->getFormDataFromArray(FatApp::getPostedData());

        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = SellerProduct::getSearchObject($this->adminLangId);
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'ts.shop_user_id = sp.selprod_user_id' , 'ts');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'ts_l.shoplang_shop_id = ts.shop_id and ts_l.shoplang_lang_id = ' . $this->adminLangId, 'ts_l');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_user_id = u.user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        $srch->addCondition('selprod_deleted', '=', 0);
        $srch->addCondition('selprod_type', '=', 2);
        $keyword = trim(FatApp::getPostedData('keyword', FatUtility::VAR_STRING, ''));
        if ($keyword != '') {
            $srch->addCondition('selprod_title', 'like', '%' . $keyword . '%');
        }
        $shop_name = trim(FatApp::getPostedData('shop_name', null, ''));
        if (!empty($shop_name)) {
            $cnd = $srch->addCondition('ts_l.shop_name', 'like', '%' . $shop_name . '%');
            $cnd->attachCondition('user_name', 'like', '%' . $shop_name . '%', 'OR');
        }
        $addonProdStatus = FatApp::getPostedData('addonprod_active');
        if ($addonProdStatus != '') {
            $srch->addCondition('selprod_active', '=', $addonProdStatus);
        }
        $srch->addMultipleFields(array('selprod_id', 'selprod_title', 'selprod_price', 'selprod_active', 'user_name','IFNULL(ts_l.shop_name,ts.shop_identifier) as shop_name, shop_id, shop_user_id'));
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs);
        $this->set('arr_listing', $records);
        $this->set('adminLangId', $this->adminLangId);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function view(int $prodId = 0) 
    {
        if ($prodId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $addonProdData = array();
        $addonProdData = SellerProduct::getAttributesById($prodId, ['selprod_id as addonprod_id', 'selprod_price as addonprod_price', 'selprod_user_id as addonprod_user_id','selprod_is_eligible_cancel','selprod_is_eligible_refund']);
        $addonProdLangData = SellerProduct::getLangDataArr($prodId);

        if (empty($addonProdData)) {
            FatUtility::exitWithErrorCode(404);
        }

        if (!empty($addonProdLangData)) {
            foreach ($addonProdLangData as $langData) {
                $addonProdData['addonprod_title'][$langData['selprodlang_lang_id']] = $langData['selprod_title'];
                $addonProdData['addonprod_description_' . $langData['selprodlang_lang_id']] = $langData['selprod_rental_terms'];
            }
        }

        $taxData = array();
        $tax = Tax::getTaxCatObjByProductId($addonProdData['addonprod_id'], $this->adminLangId, SellerProduct::PRODUCT_TYPE_ADDON, $addonProdData['addonprod_id']);
        $tax->addCondition('ptt_seller_user_id', '=', $addonProdData['addonprod_user_id']);
        $activatedTaxServiceId = Tax::getActivatedServiceId();
        $tax->addFld('ptt_taxcat_id');
        if ($activatedTaxServiceId) {
            $tax->addFld(array('concat(IFNULL(t_l.taxcat_name,t.taxcat_identifier), " (",t.taxcat_code,")")as taxcat_name', 'concat(IFNULL(t_lrent.taxcat_name,trent.taxcat_identifier), " (",trent.taxcat_code,")")as taxcat_name_rent'));
        } else {
            $tax->addFld(array('IFNULL(t_l.taxcat_name,t.taxcat_identifier)as taxcat_name', 'IFNULL(t_lrent.taxcat_name,trent.taxcat_identifier)as taxcat_name_rent'));
        }

        $tax->doNotCalculateRecords();
        $tax->setPageSize(1);
        $tax->addOrder('ptt_seller_user_id', 'ASC');
        $rs = $tax->getResultSet();
        $taxData = FatApp::getDb()->fetch($rs);
        if (!empty($taxData)) {
            $addonProdData['ptt_taxcat_id'] = $taxData['ptt_taxcat_id'];
            $addonProdData['taxcat_name'] = $taxData['taxcat_name'];
        }

        $this->set('addonProdData', $addonProdData);
        $this->set('adminLangId', $this->adminLangId);
        $this->_template->render(true, true);
    }

    private function getAttachedSellerProducts(array $addonProdIds): array
    {
        if (empty($addonProdIds)) {
            return [];
        }
        $srch = new searchBase(SellerProduct::DB_TBL_SELLER_PROD_ADDON, 'spa');
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'spa.spa_seller_prod_id = sp.selprod_id', 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->adminLangId, 'sp_l');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'plang.productlang_product_id = p.product_id	AND productlang_lang_id = ' . $this->adminLangId, 'plang' );
        $srch->addCondition('product_active', '=', applicationConstants::YES);
        $srch->addCondition('product_deleted', '=', applicationConstants::NO);
        $srch->addCondition('product_approved', '=', applicationConstants::YES);
        $srch->addMultipleFields(array('selprod_id', 'IFNULL(selprod_title, (IFNULL(product_name, product_identifier))) as selprod_title', 'spa_addon_product_id'));
        $srch->addCondition('spa_addon_product_id', 'IN', $addonProdIds);
        $srchRs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($srchRs);
    }

    private function searchForm()
    {
        $frm = new Form('frmSearchAddonProduct');
        $frm->addTextBox(Labels::getLabel('LBL_Search_By_Addons_Name', $this->adminLangId), 'keyword');
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Shop', $this->adminLangId), 'shop_name');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->adminLangId));
        $fld_cancel = $frm->addButton('', 'btn_clear', Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getListingSearchForm()
    {
        $frm = new Form('frmSearchProductLisiting');
        $frm->addTextBox(Labels::getLabel('LBL_Search_By_Addons_Name', $this->adminLangId), 'keyword');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'addonprod_active', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Shop', $this->adminLangId), 'shop_name');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->adminLangId));
        $fld_cancel = $frm->addButton('', 'btn_clear', Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        $frm->addHiddenField('', 'page');
        return $frm;
    }
}