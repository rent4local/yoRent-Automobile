<?php

class AttachAddonPoductsController extends SellerBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $this->userPrivilege->canViewAddons(UserAuthentication::getLoggedUserId());
        if (FatApp::getConfig('CONF_ALLOW_RENTAL_SERVICES', FatUtility::VAR_INT, 0) == 0) {
            FatUtility::exitWithErrorCode(404);
        }
        $searchForm = $this->searchForm();
        $this->set('searchForm', $searchForm);
        $this->set('canEdit', $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId(), true));
        $this->_template->addJs('js/yomultiselect.all.min.js');
        $this->_template->addCSS('css/yomultiselect.default-v2.min.css');
        $this->_template->render(true, true);
    }

    public function addonProductsAutoComplete()
    {
        $userId = $this->userParentId;
        $db = FatApp::getDb();
        $json = array();
        $post = FatApp::getPostedData();

        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->addCondition('selprod_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('sp.selprod_type', '=', 'mysql_func_'. SellerProduct::PRODUCT_TYPE_ADDON, 'AND', true);
        $srch->addCondition('sp.selprod_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        $srch->addOrder('selprod_title');
        $srch->addOrder('selprod_id');
        $srch->addMultipleFields(array('selprod_id', 'IFNULL(selprod_title, selprod_identifier) as selprod_title'));
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('selprod_identifier', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $rs = $srch->getResultSet();
        $products = $db->fetchAll($rs, 'selprod_id');
        if ($products) {
            foreach ($products as $addonprodId => $product) {
                $json[] = array(
                    'id' => $addonprodId,
                    'value' => strip_tags(html_entity_decode($product['selprod_title'], ENT_QUOTES, 'UTF-8')),
                );
            }
        }

        die(json_encode($json));
    }

    public function sellerProducts()
    {
        $db = FatApp::getDb();
        $post = FatApp::getPostedData();
        $userId = $this->userParentId;
        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->siteLangId, 'p_l');
        $srch->addCondition('spd.sprodata_is_for_rent', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
        $srch->addCondition('sp.selprod_type', '=', 'mysql_func_'. SellerProduct::PRODUCT_TYPE_PRODUCT, 'AND', true);
        $srch->addCondition('sp.selprod_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('sprodata_rental_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        $srch->addCondition('p.product_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        $srch->addCondition('p.product_approved', '=', 'mysql_func_'. Product::APPROVED, 'AND', true);
        $srch->addOrder('product_name');
        $srch->addOrder('selprod_title');
        $srch->addOrder('selprod_id');
        $srch->addMultipleFields(array('selprod_id', 'IFNULL(selprod_title, IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_price', 'product_id'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('product_identifier', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $rs = $srch->getResultSet();
        $products = $db->fetchAll($rs, 'selprod_id');

        $productsGroupData = [];
        if ($products) {
            foreach ($products as $selprodId => $product) {
                $options = SellerProduct::getSellerProductOptions($product['selprod_id'], true, $this->siteLangId);
                $productId = (empty($options)) ? $selprodId : 0;
                $productName = (empty($options)) ? $product['selprod_title'] : $product['product_name'];
                $productsGroupData[$product['product_id']]['id'] = $productId;
                $productsGroupData[$product['product_id']]['text'] = strip_tags(htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'));
                if (!empty($options)) {
                    $optionvalueNames = array_column($options, 'optionvalue_name');
                    $optionName = implode('|', $optionvalueNames);
                    $productsGroupData[$product['product_id']]['items'][] = array(
                        "id" => $selprodId,
                        "text" => strip_tags(htmlspecialchars($optionName, ENT_QUOTES, 'UTF-8'))
                    );
                }
            }
        }

        sort($productsGroupData);
        //return $productsGroupData;
        die(json_encode($productsGroupData));
    }

    public function search()
    {
        $searchForm = $this->searchForm();
        $post = $searchForm->getFormDataFromArray(FatApp::getPostedData());
        $userId = $this->userParentId;
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = new searchBase(SellerProduct::DB_TBL_SELLER_PROD_ADDON, 'spa');
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'spa.spa_addon_product_id = sp.selprod_id', 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->siteLangId, 'sp_l');
        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('selprod_title', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('selprod_identifier', 'like', '%' . $keyword . '%');
        }
        $srch->addCondition('selprod_user_id', '=', 'mysql_func_'. $userId, 'AND', true);

        $srch->addMultipleFields(array('selprod_id', 'IFNULL(selprod_title, selprod_identifier) as selprod_title'));
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
                    $options = SellerProduct::getSellerProductOptions($product['selprod_id'], true, $this->siteLangId);
                    if (!empty($options)) {
                        $optionvalueNames = array_column($options, 'optionvalue_name');
                        $optionName = implode('|', $optionvalueNames);
                        $product['selprod_title'] = $product['selprod_title'] . ' - ' . $optionName;
                    }
                    $attachedProductsData[$product['spa_addon_product_id']][] = $product;
                }
            }
        }

        $this->set('canEdit', $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId(), true));
        $this->set('arrListing', $records);
        $this->set('attachedProductsData', $attachedProductsData);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function updateAddonWithProducts()
    {
        $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId());
        $addonProductId = FatApp::getPostedData('addon_product_id', FatUtility::VAR_INT, 0);
        $selectedProducts = FatApp::getPostedData('products_data');
        $selectedProductsArr = explode(',', $selectedProducts);

        if ($addonProductId <= 0 || empty($selectedProductsArr)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Rental_Addon_And_Minimum_One_Product_Is_Required', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $selProdObj = new SellerProduct();
        if (!$selProdObj->updateAddonToProduct($selectedProductsArr, $addonProductId)) {
            Message::addErrorMessage($selProdObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteAttachedProduct()
    {
        $userId = $this->userParentId;
        $addonProdId = FatApp::getPostedData('addon_product_id', FatUtility::VAR_INT, 0);
        $selProdId = FatApp::getPostedData('seller_product_id', FatUtility::VAR_INT, 0);

        if ($addonProdId <= 0 || $selProdId <= 0) {
            Message::addErrorMessage(Labels::getLabel('Lbl_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $selProdUserId = SellerProduct::getAttributesById($selProdId, 'selprod_user_id');
        $addonProdUserId = SellerProduct::getAttributesById($addonProdId, 'selprod_user_id');

        if ($selProdUserId != $userId || $addonProdUserId != $userId) {
            Message::addErrorMessage(Labels::getLabel('Lbl_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $selProdObj = new SellerProduct();
        if (!$selProdObj->deleteAttachedAddonProduct($addonProdId, $selProdId)) {
            Message::addErrorMessage($selProdObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Deleted_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getAttachedSellerProducts(array $addonProdIds): array
    {
        if (empty($addonProdIds)) {
            return [];
        }
        $userId = $this->userParentId;
        $srch = new searchBase(SellerProduct::DB_TBL_SELLER_PROD_ADDON, 'spa');
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'spa.spa_seller_prod_id = sp.selprod_id', 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->siteLangId, 'sp_l');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'plang.productlang_product_id = p.product_id	AND productlang_lang_id = ' . $this->siteLangId, 'plang' );
        $srch->addCondition('product_active', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
        $srch->addCondition('product_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        $srch->addCondition('product_approved', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
        $srch->addMultipleFields(array('selprod_id', 'IFNULL(selprod_title, (IFNULL(product_name, product_identifier))) as selprod_title', 'spa_addon_product_id'));
        $srch->addCondition('spa_addon_product_id', 'IN', $addonProdIds);
        $srch->addCondition('selprod_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srchRs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($srchRs);
    }

    private function searchForm()
    {
        $frm = new Form('frmSearchAddonProduct');
        $frm->addTextBox(Labels::getLabel('LBL_Search_By', $this->siteLangId), 'keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->siteLangId));
        $frm->addButton('', 'btn_clear', Labels::getLabel('LBL_Clear', $this->siteLangId));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

}
