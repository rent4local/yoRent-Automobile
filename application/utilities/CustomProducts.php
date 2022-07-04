<?php

trait CustomProducts
{

    public function customProduct()
    {
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }

        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        if (!User::canAddCustomProduct()) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'catalog'));
        }

        $frmSearchCustomProduct = $this->getCustomProductSearchForm();
        $this->set("frmSearchCustomProduct", $frmSearchCustomProduct);
        $this->_template->addJs('js/jscolor.js');
        $this->_template->render(true, true);
    }

    public function searchCustomProduct()
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $srch = Product::getSearchObject($this->siteLangId);
        $srch->addCondition('product_seller_id', '=', $this->userParentId);

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_identifier', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('product_model', 'like', '%' . $keyword . '%');
        }

        $srch->addMultipleFields(
                array(
                    'product_id',
                    'product_identifier',
                    'product_active',
                    'product_approved',
                    'product_added_on',
                    'product_name'
                )
        );
        $srch->addOrder('product_added_on', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL', FatApp::getConfig("CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL", FatUtility::VAR_INT, 1));

        $this->_template->render(false, false);
    }

    public function customProductLangForm($product_id, $lang_id, $autoFillLangData = 0)
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        $product_id = FatUtility::int($product_id);
        $lang_id = FatUtility::int($lang_id);

        if ($product_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != $this->userParentId) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        $prodCatId = 0;
        $product = new Product();
        $records = $product->getProductCategories($product_id);
        if (!empty($records)) {
            $prodcatArr = array_column($records, 'prodcat_id');
            $prodCatId = reset($prodcatArr);
        }

        $customProductLangFrm = $this->getCustomProductLangForm($lang_id);

        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Product::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($product_id, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $customProductLangData = current($translatedData);
        } else {
            $prodObj = new Product($product_id);
            $customProductLangData = $prodObj->getAttributesByLangId($lang_id, $product_id);
        }
        $customProductLangData['product_id'] = $product_id;
        if ($customProductLangData) {
            $customProductLangFrm->fill($customProductLangData);
        }
        $alertToShow = $this->CheckProductLinkWithCatBrand($product_id);
        $this->set('alertToShow', $alertToShow);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->set('activeTab', 'GENERAL');
        $this->set('languages', Language::getAllNames());
        $this->set('product_id', $product_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('product_lang_id', $lang_id);
        $this->set('prodcat_id', $prodCatId);
        $this->set('customProductLangFrm', $customProductLangFrm);
        $this->_template->render(false, false);
    }

    public function setupCustomProductLang()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        $post = FatApp::getPostedData();
        $lang_id = $post['lang_id'];
        $product_id = FatUtility::int($post['product_id']);

        if ($product_id == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != $this->userParentId) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */
        $frm = $this->getCustomProductLangForm($lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        unset($post['product_id']);
        unset($post['lang_id']);
        $data_to_update = array(
            'productlang_product_id' => $product_id,
            'productlang_lang_id' => $lang_id,
            'product_name' => $post['product_name'],
            'product_description' => $post['product_description'],
            'product_youtube_video' => $post['product_youtube_video'],
        );

        $prodObj = new Product($product_id);
        if (!$prodObj->updateLangData($lang_id, $data_to_update)) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Product::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($product_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Product::getAttributesByLangId($langId, $product_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->set('product_id', $product_id);
        $this->set('lang_id', $newTabLangId);

        $this->_template->render(false, false, 'json-success.php');
    }

    public function customProductOptions($product_id)
    {
        $product_id = FatUtility::int($product_id);
        if (!$product_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }

        $prodCatId = 0;
        $product = new Product();
        $records = $product->getProductCategories($product_id);
        if (!empty($records)) {
            $prodcatArr = array_column($records, 'prodcat_id');
            $prodCatId = reset($prodcatArr);
        }

        $customProductOptionFrm = $this->getCustomProductOptionForm();
        $alertToShow = $this->CheckProductLinkWithCatBrand($product_id);
        $this->set('alertToShow', $alertToShow);
        $this->set('customProductOptionFrm', $customProductOptionFrm);
        $this->set('product_id', $product_id);
        $this->set('prodcat_id', $prodCatId);
        $this->set('activeTab', 'OPTIONS');
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function productOptions($productId = 0)
    {
        $productId = FatUtility::int($productId);
        if (!$productId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        /* Validate product belongs to current logged seller[ */
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $productOptions = Product::getProductOptions($productId, $this->siteLangId);
        $this->set('productOptions', $productOptions);
        $this->set('productId', $productId);
        $this->_template->render(false, false);
    }

    private function getCustomProductOptionForm()
    {
        $frm = new Form('frmProductOptions', array('id' => 'frmProductOptions'));
        $frm->addHtml('', 'product_name', '');
        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Option_Groups', $this->siteLangId), 'option_name');
        $fld1->htmlAfterField = '<div class=""><small><a href="javascript:void(0);" onClick="optionForm(0);">' . Labels::getLabel('LBL_Add_New_Option', $this->siteLangId) . '</a></small></div><div class="row"><div class="col-md-12"><ul class="list--vertical" id="product_options_list"></ul></div>';
        $frm->addHiddenField('', 'product_id', '', array('id' => 'product_id'));

        return $frm;
    }

    public function updateProductOption()
    {
        $post = FatApp::getPostedData();
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);

        if (!$product_id || !$option_id) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }

        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $product_id)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $productOptions = Product::getProductOptions($product_id, $this->siteLangId, false, 1);
        $optionSeparateImage = Option::getAttributesById($option_id, 'option_is_separate_images');
        if (count($productOptions) > 0 && $optionSeparateImage == 1) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_you_have_already_added_option_having_separate_image', $this->siteLangId));
        }
        $prodObj = new Product($product_id);
        if (!$prodObj->addUpdateProductOption($option_id)) {
            FatUtility::dieJsonError($prodObj->getError());
        }
        Product::updateMinPrices($product_id);
        $this->set('msg', Labels::getLabel('LBL_Option_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function checkOptionLinkedToInventory()
    {
        $post = FatApp::getPostedData();
        $productId = FatUtility::int($post['product_id']);
        $optionId = FatUtility::int($post['option_id']);

        if (!$productId || !$optionId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* Validate product belongs to current logged seller[ */
        if ($productId) {
            $productRow = Product::getAttributesById($productId, array('product_seller_id'));
            if ($productRow['product_seller_id'] != $this->userParentId) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        /* Validate option is binded with seller product [ */
        $optionSrch = SellerProduct::getSearchObject();
        $optionSrch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'sp.selprod_id = spo.selprodoption_selprod_id', 'spo');
        $optionSrch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'LEFT OUTER JOIN', 'sp.selprod_product_id = po.prodoption_product_id', 'po');
        $optionSrch->addMultipleFields(array('selprodoption_option_id'));
        $optionSrch->addCondition('selprod_product_id', '=', $productId);
        $optionSrch->addCondition('prodoption_option_id', '=', $optionId);
        $optionSrch->addCondition('selprodoption_option_id', '=', $optionId);
        $optionSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);

        $rs = $optionSrch->getResultSet();
        $db = FatApp::getDb();
        $row = $db->fetch($rs);
        if (!empty($row)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_This_option_is_linked_with_the_inventory,_so_can_not_be_deleted', $this->siteLangId));
            return;
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("MSG_Option_can_be_deleted", $this->siteLangId));
        /* ] */
    }

    public function removeProductOption()
    {
        $post = FatApp::getPostedData();
        $productId = FatUtility::int($post['product_id']);
        $optionId = FatUtility::int($post['option_id']);

        if (!$productId || !$optionId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        /* Validate product belongs to current logged seller[ */
        if ($productId) {
            $productRow = Product::getAttributesById($productId, array('product_seller_id'));
            if ($productRow['product_seller_id'] != $this->userParentId) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        /* Get Linked Products [ */
        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
        $srch->addCondition('selprod_product_id', '=', $productId);
        $srch->addCondition('tspo.selprodoption_option_id', '=', $optionId);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addFld(array('selprod_id'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Option_is_linked_with_seller_inventory', $this->siteLangId));
        }
        /* ] */

        $prodObj = new Product($productId);
        if (!$prodObj->removeProductOption($optionId)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        Product::updateMinPrices($productId);
        FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Option_removed_successfully.', $this->siteLangId));
    }

    public function customProductImages($product_id, $showModal = 0)
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        $product_id = FatUtility::int($product_id);

        if (!$product_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if (!$productRow = Product::getAttributesById($product_id, array('product_seller_id'))) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if ($productRow['product_seller_id'] != $this->userParentId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $pObj = new Product($product_id);
        $isOptWithSizeChart = $pObj->checkOptionWithSizeChart();

        $imagesFrm = $this->getImagesFrm($product_id, $this->siteLangId, $isOptWithSizeChart);

        $imgTypesArr = $this->getSeparateImageOptions($product_id, $this->siteLangId);

        $productType = Product::getAttributesById($product_id, 'product_type');

        $hideButtons = FatApp::getPostedData('hideButtons', FatUtility::VAR_INT, 0);

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);

        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);

        $this->set('product_id', $product_id);
        $this->set('imagesFrm', $imagesFrm);
        $this->set('productType', $productType);
        $this->set('hideButtons', $hideButtons);
        if ($showModal) {
            $this->_template->render(false, false, 'seller/custom-product-images-modal.php');
        } else {
            $this->_template->render(false, false);
        }
    }

    public function images($product_id, $option_id = 0, $lang_id = 0)
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        $product_id = FatUtility::int($product_id);

        if (!$product_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if (!$productRow = Product::getAttributesById($product_id, array('product_seller_id'))) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if ($productRow['product_seller_id'] != $this->userParentId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $product_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $option_id, $lang_id, false, 0, 0, true);
        $imgTypesArr = $this->getSeparateImageOptions($product_id, $this->siteLangId);

        $pObj = new Product($product_id);
        $isOptWithSizeChart = $pObj->checkOptionWithSizeChart();

        $productSizeChart = [];
        if ($isOptWithSizeChart) {
            $productSizeChart = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_SIZE_CHART, $product_id, 0, $lang_id, false, 0, 0, true);
        }

        $this->set('sizeChartArr', $productSizeChart);


        $this->set('images', $product_images);
        $this->set('product_id', $product_id);
        $this->set('imgTypesArr', $imgTypesArr);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    /* public function setupCustomProductImages()
      {
      $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
      if (!User::canAddCustomProduct()) {
      FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
      }
      if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
      FatUtility::dieJsonError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
      }

      $post = FatApp::getPostedData();
      if (empty($post)) {
      Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
      FatUtility::dieJsonError(Message::getHtml());
      }
      $product_id = FatUtility::int($post['product_id']);
      $option_id = FatUtility::int($post['option_id']);
      $lang_id = FatUtility::int($post['lang_id']);



      if ($product_id) {
      $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
      if ($productRow['product_seller_id'] != $this->userParentId) {
      FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
      }
      }

      $productImagesArr = array();
      $sellerId = $this->userParentId;

      $subscription = false;
      $allowed_images = -1;
      if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
      $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $sellerId, array('ossubs_images_allowed'));
      $allowed_images = $currentPlanData['ossubs_images_allowed'];
      $subscription = true;
      }


      $options = Product::getProductOptions($product_id, $this->siteLangId, true, 1);
      $productSelectedOptionValues = array();
      $productGroupImages = array();

      $productOptionId = ($option_id == 0) ? -1 : $option_id;

      $images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $productOptionId, $this->siteLangId, true, '', $allowed_images);
      if ($images) {
      $productImagesArr += $images;
      }

      if ($productImagesArr) {
      foreach ($productImagesArr as $image) {
      $afileId = $image['afile_id'];
      if (!array_key_exists($afileId, $productGroupImages)) {
      $productGroupImages[$afileId] = array();
      }
      $productGroupImages[$afileId] = $image;
      }
      }


      if ($allowed_images > 0 && count($productImagesArr) >= $allowed_images) {
      FatUtility::dieJsonError(Labels::getLabel("MSG_Cant_upload_more_than_allowed_images", $this->siteLangId));
      }


      if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
      FatUtility::dieJsonError(Labels::getLabel("MSG_Please_select_a_file", $this->siteLangId));
      }
      $fileHandlerObj = new AttachedFile();
      if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $option_id, $_FILES['cropped_image']['name'], -1, $unique_record = false, $lang_id)) {
      FatUtility::dieJsonError($fileHandlerObj->getError());
      }
      FatApp::getDb()->updateFromArray('tbl_products', array('product_updated_on' => date('Y-m-d H:i:s')), array('smt' => 'product_id = ?', 'vals' => array($product_id)));

      FatUtility::dieJsonSuccess(Labels::getLabel("MSG_Image_Uploaded_Successfully", $this->siteLangId));
      } */

    public function setupCustomProductImages()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        if (!User::canAddCustomProduct()) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }

        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        $lang_id = FatUtility::int($post['lang_id']);


        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            $optionValues = Product::getSeparateImageOptions($product_id, $this->siteLangId);

            if ($productRow['product_seller_id'] != $this->userParentId || !array_key_exists($option_id, $optionValues)) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }

        $this->validateImageSubscriptionLimit($product_id, $option_id, $lang_id);

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Please_select_a_file", $this->siteLangId));
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES) { /* in kbs */
            FatUtility::dieJsonError(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->siteLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
        }
        
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $option_id, $_FILES['cropped_image']['name'], -1, $unique_record = false, $lang_id)) {
            FatUtility::dieJsonError($fileHandlerObj->getError());
        }
        FatApp::getDb()->updateFromArray('tbl_products', array('product_updated_on' => date('Y-m-d H:i:s')), array('smt' => 'product_id = ?', 'vals' => array($product_id)));

        FatUtility::dieJsonSuccess(Labels::getLabel("MSG_Image_Uploaded_Successfully", $this->siteLangId));
    }

    private function validateImageSubscriptionLimit($product_id, $productOptionId, $lang_id)
    {
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $this->userParentId, array('ossubs_images_allowed'));
            $allowed_images = $currentPlanData['ossubs_images_allowed'];

            $optionValues = Product::getSeparateImageOptions($product_id, $this->siteLangId);
            $srch = new SearchBase(AttachedFile::DB_TBL);
            $srch->doNotCalculateRecords();
            $srch->addCondition('afile_type', '=', AttachedFile::FILETYPE_PRODUCT_IMAGE);
            $srch->addCondition('afile_record_id', '=', $product_id);
            $srch->addCondition('afile_lang_id', 'IN', [$lang_id, 0]);
            if (0 < $productOptionId) {
                $srch->addCondition('afile_record_subid', 'IN', [$productOptionId, 0]);
                $images = FatApp::getDb()->fetchAll($srch->getResultSet());
                $allReadyAddedCount = count($images);
            } else {
                $srch->addCondition('afile_record_subid', 'IN', array_keys($optionValues));
                $srch->addGroupBy('afile_record_subid');
                $srch->addOrder('image_count', 'desc');
                $srch->addMultipleFields(['count(afile_id) as image_count', 'afile_record_subid']);
                $images = FatApp::getDb()->fetchAll($srch->getResultSet(), 'afile_record_subid');
                $allReadyAddedCount = 0;
                if ($images) {
                    if (isset($images[0])) {
                        $allReadyAddedCount += $images[0]['image_count'];
                        unset($images[0]);
                    }
                    if (count($images)) {
                        /* adding all option  + max count of other option */
                        $allReadyAddedCount += current($images)['image_count'];
                    }
                }
            }

            if ($allowed_images > 0 && $allReadyAddedCount >= $allowed_images) {
                FatUtility::dieJsonError(Labels::getLabel("MSG_Cant_upload_more_than_allowed_images", $this->siteLangId));
            }
        }
    }

    public function deleteCustomProductImage($product_id, $image_id, int $isSizeChart = 0)
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $product_id = FatUtility::int($product_id);
        $image_id = FatUtility::int($image_id);
        if (!$image_id || !$product_id) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* Validate product belongs to current logged seller[ */
        $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
        if ($productRow['product_seller_id'] != $this->userParentId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        /* ] */
        $fileData = AttachedFile::getAttributesById($image_id);

        $productObj = new Product();
        if (!$productObj->deleteProductImage($product_id, $image_id, $isSizeChart)) {
            FatUtility::dieJsonError($productObj->getError());
        }

        if (!empty($fileData)) {
            if ($isSizeChart) {
                if (file_exists(CONF_UPLOADS_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . $fileData['afile_physical_path']);
                }
            } else {
                if (file_exists(CONF_UPLOADS_PATH . AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $fileData['afile_physical_path']);
                }
            }
        }

        FatApp::getDb()->updateFromArray('tbl_products', array('product_updated_on' => date('Y-m-d H:i:s')), array('smt' => 'product_id = ?', 'vals' => array($product_id)));

        FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Image_removed_successfully', $this->siteLangId));
    }

    public function setCustomProductImagesOrder()
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $productObj = new Product();
        $post = FatApp::getPostedData();
        $product_id = FatUtility::int($post['product_id']);
        /* Validate product belongs to current logged seller[ */
        $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
        if ($productRow['product_seller_id'] != $this->userParentId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        /* ] */
        $imageIds = explode('-', $post['ids']);
        $count = 1;
        foreach ($imageIds as $row) {
            $order[$count] = $row;
            $count++;
        }

        if (!$productObj->updateProdImagesOrder($product_id, $order)) {
            Message::addErrorMessage($productObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("LBL_Ordered_Successfully", $this->siteLangId));
    }

    /* Custom product Specifications */

    public function customProductSpecifications($product_id)
    {
        $productSpecifications = Product::getProductSpecifications($product_id, $this->siteLangId);

        $prodCatId = 0;
        $product = new Product();
        $records = $product->getProductCategories($product_id);
        if (!empty($records)) {
            $prodcatArr = array_column($records, 'prodcat_id');
            $prodCatId = reset($prodcatArr);
        }

        $alertToShow = $this->CheckProductLinkWithCatBrand($product_id);
        $this->set('alertToShow', $alertToShow);
        $this->set('prodSpec', $productSpecifications);
        $this->set('product_id', $product_id);
        $this->set('prodcat_id', $prodCatId);
        $languages = Language::getAllNames();
        $this->set('languages', $languages);
        $this->set('activeTab', 'SPECIFICATIONS');
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function productSpecifications($productId)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $productSpecifications = Product::getProductSpecifications($productId, $this->siteLangId);

        $languages = Language::getAllNames();
        $this->set('prodSpec', $productSpecifications);
        $this->set('productId', $productId);
        $this->set('languages', $languages);
        $this->set('siteLangId', $this->siteLangId);

        $this->_template->render(false, false);
    }

    public function deleteProdSpec($productId = 0)
    {
        $post = FatApp::getPostedData();
        $prodspec_id = FatUtility::int($post['prodSpecId']);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($prodspec_id > 0) {
            if (!UserPrivilege::canEditSellerProductSpecification($prodspec_id, $productId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $productId, $prodspec_id, $langId);
        $prodSpecObj = new ProdSpecification($prodspec_id);
        if (!$prodSpecObj->deleteRecord(true)) {
            Message::addErrorMessage(Labels::getLabel($prodSpecObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* [ DELETE UPLOADED FILE */
        $this->deleteSpecFile($productId, $prodspec_id);
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Specification_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getShippingTab()
    {
        $shipping_rates = array();
        $post = FatApp::getPostedData();
        $userId = $this->userParentId;
        $product_id = $post['product_id'];
        //$shipping_rates = Products::getProductShippingRates();
        $this->set('siteLangId', $this->siteLangId);
        $shipping_rates = array();
        $shipping_rates = Product::getProductShippingRates($product_id, $this->siteLangId, 0, $userId);

        $this->set('siteLangId', $this->siteLangId);
        $this->set('product_id', $product_id);
        $this->set('shipping_rates', $shipping_rates);
        $this->_template->render(false, false);
    }

    public function countries_autocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = $this->userParentId;
        $srch = Countries::getSearchObject(true, $this->siteLangId);
        $srch->addOrder('country_name');

        $srch->addMultipleFields(array('country_id, country_name, country_code'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('country_name', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $countries = $db->fetchAll($rs, 'country_id');
        if (isset($post['includeEverywhere']) && $post['includeEverywhere']) {
            $everyWhereArr = array('country_id' => '-1', 'country_name' => Labels::getLabel('LBL_Everywhere_Else', $this->siteLangId));
            $countries[] = $everyWhereArr;
        }

        $json = array();
        foreach ($countries as $key => $country) {
            $json[] = array(
                'id' => $country['country_id'],
                'name' => strip_tags(html_entity_decode(isset($country['country_name']) ? $country['country_name'] : $country['country_code'], ENT_QUOTES, 'UTF-8')),
            );
        }
        die(json_encode($json));
    }

    public function shippingMethodsAutocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = $this->userParentId;
        $srch = ShippingApi::getSearchObject(true, $this->siteLangId);
        $srch->addOrder('shippingapi_name');

        $srch->addMultipleFields(array('shippingapi_id, shippingapi_name'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('shippingapi_name', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $shippingMethods = $db->fetchAll($rs, 'shippingapi_id');


        $json = array();
        foreach ($shippingMethods as $key => $sMethod) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($sMethod['shippingapi_name'], ENT_QUOTES, 'UTF-8')),
            );
        }
        die(json_encode($json));
    }

    public function shippingCompanyAutocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = $this->userParentId;
        $srch = ShippingCompanies::getSearchObject(true, $this->siteLangId);
        $srch->addOrder('scompany_name');

        $srch->addMultipleFields(array('scompany_id, scompany_name'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('scompany_name', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $shippingCompanies = $db->fetchAll($rs, 'scompany_id');


        $json = array();
        foreach ($shippingCompanies as $key => $sCompany) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($sCompany['scompany_name'], ENT_QUOTES, 'UTF-8')),
            );
        }
        die(json_encode($json));
    }

    public function shippingMethodDurationAutocomplete()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $userId = $this->userParentId;
        $srch = ShippingDurations::getSearchObject($this->siteLangId, true);
        $srch->addOrder('sduration_name');

        $srch->addMultipleFields(array('sduration_id, sduration_name', 'sduration_from', 'sduration_to', 'sduration_days_or_weeks'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('sduration_id', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $shipDurations = $db->fetchAll($rs, 'sduration_id');

        $json = array();
        foreach ($shipDurations as $key => $shipDuration) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($shipDuration['sduration_name'], ENT_QUOTES, 'UTF-8')),
                'duraion' => ShippingDurations::getShippingDurationTitle($shipDuration, $this->siteLangId),
            );
        }
        die(json_encode($json));
    }

    /*  ---  Seller Product Links  --- - */

    public function customProductLinks($productId = 0)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();


        $lang_id = $this->siteLangId;
        $frm = $this->getLinksForm($productId);

        $srch = Product::getSearchObject($lang_id);
        $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'tp.product_brand_id = brand.brand_id', 'brand');

        $srch->joinTable(Brand::DB_TBL_LANG, 'LEFT OUTER JOIN', 'brandlang_brand_id = brand.brand_id AND brandlang_lang_id = ' . $lang_id);

        $srch->addMultipleFields(array('product_id', 'brand_status', 'brand_deleted', 'product_brand_id', 'IFNULL(product_name,product_identifier) as product_name', 'IFNULL(brand_name,brand_identifier) as brand_name'));
        $srch->addCondition('product_id', '=', $productId);
        $srch->addCondition('brand.brand_active', '=', applicationConstants::YES);
        $srch->addCondition('brand.brand_deleted', '=', applicationConstants::NO);
        $rs = $srch->getResultSet();
        $product_row = FatApp::getDb()->fetch($rs);
        $prodObj = new Product();
        $product_tags = $prodObj->getProductTags($productId, $lang_id);

        $alertToShow = $this->CheckProductLinkWithCatBrand($productId);
        $this->set('alertToShow', $alertToShow);

        $prodCatId = 0;
        $product = new Product();
        $records = $product->getProductCategories($productId);
        if (!empty($records)) {
            $prodcatArr = array_column($records, 'prodcat_id');
            $prodCatId = reset($prodcatArr);
        }

        $frm->fill($product_row);

        $this->set('product_name', $product_row['product_name']);
        $this->set('product_tags', $product_tags);
        $this->set('frmLinks', $frm);
        $this->set('product_id', $productId);
        $this->set('prodcat_id', $prodCatId);
        $this->set('activeTab', 'LINKS');
        $this->_template->render(false, false);
    }

    public function setupProductLinks()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $post['product_id'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_tags = (isset($post['product_tag'])) ? $post['product_tag'] : array();
        $frm = $this->getLinksForm($post['product_id']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = $post['product_id'];
        unset($post['product_id']);

        if ($product_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* $product_categories = $post['product_category'];
          $product_categories = explode(',',$product_categories); */

        $prodObj = new Product($product_id);

        $data_to_be_save['product_brand_id'] = FatUtility::int($post['product_brand_id']);
        $prodObj->assignValues($data_to_be_save);

        if (!$prodObj->save()) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* saving of product categories[
          if( !$prodObj->addUpdateProductCategories($product_id, $product_categories ) ){
          Message::addErrorMessage( $prodObj->getError() );
          FatUtility::dieWithError(Message::getHtml());
          }
          /* ] */
        /* saving of product Tag[ */


        if (!$prodObj->addUpdateProductTags($product_tags)) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        Tag::updateProductTagString($product_id);
        /* ] */

        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function tagsAutoComplete()
    {
        $post = FatApp::getPostedData();

        $srch = Tag::getSearchObject();
        $srch->addOrder('tag_identifier');
        $srch->joinTable(
                Tag::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->siteLangId
        );
        $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('tag_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('tag_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = $db->fetchAll($rs, 'tag_id');
        $json = array();
        foreach ($options as $key => $option) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['tag_name'], ENT_QUOTES, 'UTF-8')),
                'tag_identifier' => strip_tags(html_entity_decode($option['tag_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function tagSetup()
    {
        $frm = $this->getTagsForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $tag_id = $post['tag_id'];
        unset($post['tag_id']);

        $record = new Tag($tag_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage(Labels::getLabel('MSG_This_identifier_is_not_available._Please_try_with_another_one.', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($tag_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Tag::getAttributesByLangId($langId, $tag_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $tag_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        /* update product tags association and tag string in products lang table[ */
        Tag::updateTagStrings($tag_id);
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Tag_Updated_Successfully', $this->siteLangId));
        $this->set('tagId', $tag_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function tagLangSetup()
    {
        $post = FatApp::getPostedData();

        $tag_id = FatUtility::int($post['tag_id']);
        $lang_id = FatUtility::int($post['lang_id']);

        if ($tag_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getTagLangForm($tag_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['tag_id']);
        unset($post['lang_id']);
        $data = array(
            'taglang_lang_id' => $lang_id,
            'taglang_tag_id' => $tag_id,
            'tag_name' => $post['tag_name'],
        );

        $tagObj = new Tag($tag_id);
        if (!$tagObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($tagObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Tag::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($tag_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Tag::getAttributesByLangId($langId, $tag_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        /* update product tags association and tag string in products lang table[ */
        Tag::updateTagStrings($tag_id);
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Tag_Updated_Successfully', $this->siteLangId));
        $this->set('tagId', $tag_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function addtagsForm($tag_id = 0)
    {
        $tag_id = FatUtility::int($tag_id);
        $frm = $this->getTagsForm($tag_id);

        if (0 < $tag_id) {
            $data = Tag::getAttributesById($tag_id, array('tag_id', 'tag_identifier'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('tag_id', $tag_id);
        $this->set('frmTag', $frm);
        $this->set('langId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function tagsLangForm($tag_id = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $tag_id = FatUtility::int($tag_id);
        $lang_id = FatUtility::int($lang_id);

        if ($tag_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $tagLangFrm = $this->getTagLangForm($tag_id, $lang_id);

        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Tag::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($tag_id, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = Tag::getAttributesByLangId($lang_id, $tag_id);
        }

        if ($langData) {
            $tagLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('tag_id', $tag_id);
        $this->set('tag_lang_id', $lang_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('tagLangFrm', $tagLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function setupTag()
    {
        $this->userPrivilege->canEditProductTags(UserAuthentication::getLoggedUserId());
        $frm = $this->getTagsForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $tag_id = $post['tag_id'];
        if ($tag_id > 0) {
            if (!UserPrivilege::canSellerUpdateTag($this->userParentId, $tag_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        unset($post['tag_id']);
        $post['tag_user_id'] = $this->userParentId;
        $record = new Tag($tag_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($tag_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Tag::getAttributesByLangId($langId, $tag_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $tag_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        /* update product tags association and tag string in products lang table[ */
        Tag::updateTagStrings($tag_id);
        /* ] */

        $this->set('msg', Labels::getLabel("MSG_Tag_Setup_Successful", $this->siteLangId));
        $this->set('tagId', $tag_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* ...................................Product Shipping Rates.................................. */

    public function removeProductShippingRates($product_id, $userId = 0)
    {
        $db = FatApp::getDb();
        $product_id = FatUtility::int($product_id);
        $userId = FatUtility::int($userId);


        if (!$db->deleteRecords(ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES, array('smt' => ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX . 'prod_id = ? and   ' . ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX . 'user_id = ?', 'vals' => array($product_id, $userId)))) {
            $this->error = $db->getError();
            return false;
        }

        return true;
    }

    private function addUpdateProductShippingRates($product_id, $data)
    {
        $this->removeProductShippingRates($product_id, $this->userParentId);

        if (!empty($data) && count($data) > 0) {
            foreach ($data as $key => $val) :
                if ((isset($val["country_id"]) && $val["country_id"] >= 0 || $val["country_id"] == -1) && $val["company_id"] > 0 && $val["processing_time_id"] > 0) {
                    $prodShipData = array(
                        'pship_prod_id' => $product_id,
                        'pship_user_id' => $this->userParentId,
                        'pship_country' => (isset($val["country_id"]) && FatUtility::int($val["country_id"])) ? FatUtility::int($val["country_id"]) : 0,
                        'pship_company' => (isset($val["company_id"]) && FatUtility::int($val["company_id"])) ? FatUtility::int($val["company_id"]) : 0,
                        'pship_duration' => (isset($val["processing_time_id"]) && FatUtility::int($val["processing_time_id"])) ? FatUtility::int($val["processing_time_id"]) : 0,
                        'pship_charges' => (1 > FatUtility::float($val["cost"]) ? 0 : FatUtility::float($val["cost"])),
                        'pship_additional_charges' => FatUtility::float($val["additional_cost"]),
                    );
                    if (isset($val["pship_id"])) {
                        $prodShipData['pship_id'] = FatUtility::int($val["pship_id"]);
                    }
                    if (!FatApp::getDb()->insertFromArray(ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES, $prodShipData, false, array(), $prodShipData)) {
                        $this->error = FatApp::getDb()->getError();
                        return false;
                    }
                }
            endforeach;
        }
        return true;
    }

    public function addUpdateProductSellerShipping($product_id, $data_to_be_save)
    {
        $productSellerShiping = array();
        $productSellerShiping['ps_product_id'] = $product_id;
        $productSellerShiping['ps_user_id'] = $this->userParentId;
        $productSellerShiping['ps_free'] = isset($data_to_be_save['ps_free']) ? $data_to_be_save['ps_free'] : 0;
        $productSellerShiping['ps_fullfillment_type'] = isset($data_to_be_save['ps_fullfillment_type']) ? $data_to_be_save['ps_fullfillment_type'] : 0;
        if (!FatApp::getDb()->insertFromArray(Product::DB_TBL_PRODUCT_SHIPPING, $productSellerShiping, false, array(), $productSellerShiping)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    private function getCustomProductSearchForm()
    {
        $frm = new Form('frmSearchCustomProduct');
        $frm->addTextBox('', 'keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getTagsForm($tag_id = 0)
    {
        $tag_id = FatUtility::int($tag_id);

        $frm = new Form('frmTag', array('id' => 'frmTag'));
        $frm->addHiddenField('', 'tag_id', $tag_id);
        $frm->addRequiredField(Labels::getLabel("LBL_Tag_Identifier", $this->siteLangId), 'tag_identifier');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->siteLangId));
        return $frm;
    }

    private function getTagLangForm($tag_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmTagLang', array('id' => 'frmTagLang'));
        $frm->addHiddenField('', 'tag_id', $tag_id);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Tag_Name', $this->siteLangId), 'tag_name');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Update", $this->siteLangId));
        return $frm;
    }

    private function getLinksForm($product_id = 0)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $product_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = new Form('frmLinks', array('id' => 'frmLinks'));
        $frm->addTextBox(Labels::getLabel('LBL_Product_Name', $this->siteLangId), 'product_name');

        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Category', $this->siteLangId), 'choose_links');
        $fld2 = $frm->addHtml('', 'addNewOptionLink', '</a><div id="product_links_list" class="col-xs-10" ></div>');
        $fld1->attachField($fld2);
        $frm->addHiddenField('', 'product_brand_id');

        $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Tag', $this->siteLangId), 'tag_name');
        $fld1->htmlAfterField = '<div class="col-md-12"><small><a href="javascript:void(0);" onClick="addTagForm(0);">' . Labels::getLabel('LBL_Tag_Not_Found?_Click_here_to_', $this->siteLangId) . ' ' . Labels::getLabel('LBL_Add_New_Tag', $this->siteLangId) . '</a></small></div><div class="row"><div class="col-md-12"><ul class="list--vertical" id="product-tag"></ul></div>';

        //$frm->addHtml('','product-tag','');

        $frm->addHiddenField('', 'product_id', $product_id);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $this->siteLangId));
        return $frm;
    }

    private function getProductSpecForm()
    {
        $frm = new Form('frmProductSpec');
        $languages = Language::getAllNames();
        $defaultLang = true;
        foreach ($languages as $langId => $langName) {
            $attr['class'] = 'langField_' . $langId;
            if (true === $defaultLang) {
                $attr['class'] .= ' defaultLang';
                $defaultLang = false;
            }
            $frm->addRequiredField(
                    Labels::getLabel('LBL_Specification_Name', $this->siteLangId), 'prod_spec_name[' . $langId . ']', '', $attr
            );
            $frm->addRequiredField(
                    Labels::getLabel('LBL_Specification_Value', $this->siteLangId), 'prod_spec_value[' . $langId . ']', '', $attr
            );
        }
        $frm->addHiddenField('', 'product_id');
        $frm->addHiddenField('', 'prodspec_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));

        return $frm;
    }

    private function getImagesFrm(int $product_id = 0, int $lang_id = 0, bool $isUploadSizeChart = false)
    {
        $sizeArr = imagesSizes::productImageSizeArr()[applicationConstants::getActiveTheme()];
        $imgTypesArr = $this->getSeparateImageOptions($product_id, $lang_id);
        $frm = new Form('imageFrm', array('id' => 'imageFrm'));
        $frm->addSelectBox(Labels::getLabel('LBL_Image_File_Type', $this->siteLangId), 'option_id', $imgTypesArr, 0, array('class' => 'option'), '');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->siteLangId), 'lang_id', array(0 => Labels::getLabel('LBL_All_Languages', $this->siteLangId)) + $languagesAssocArr, '', array('class' => 'language'), '');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s)', $this->siteLangId), 'prod_image', array('id' => 'prod_image'));
        $fldImg->htmlBeforeField = '<div class="filefield">';
        $fldImg->htmlAfterField = '</div><span class="form-text text-muted">' . Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_' . $sizeArr['width'] . '_x_' . $sizeArr['height'], $this->siteLangId) . '</span>';

        /* [ UPLOAD SIZE CHART  */
        if ($isUploadSizeChart) {
            $frm->addFileUpload(Labels::getLabel('LBL_Upload_Size_Chart', $this->siteLangId), 'prod_size_chart', array('id' => 'prod_size_chart'));
        }
        /* ] */

        $frm->addHiddenField('', 'min_width', $sizeArr['width']);
        $frm->addHiddenField('', 'min_height', $sizeArr['height']);
        $frm->addHiddenField('', 'product_id', $product_id);
        return $frm;
    }

    private function getSeparateImageOptions($product_id, $lang_id)
    {
        $imgTypesArr = array(0 => Labels::getLabel('LBL_For_All_Options', $this->siteLangId));
        $productOptions = Product::getProductOptions($product_id, $lang_id, true, 1);

        foreach ($productOptions as $val) {
            if (!empty($val['optionValues'])) {
                foreach ($val['optionValues'] as $k => $v) {
                    $option_name = (isset($val['option_name']) && $val['option_name']) ? $val['option_name'] : $val['option_identifier'];
                    //$imgTypesArr[$k] = $v .' ( '. $option_name .' )';
                    $imgTypesArr[$k] = $v;
                }
            }
        }
        return $imgTypesArr;
    }

    private function getCustomProductImagesForm()
    {
        $frm = new Form('frmCustomProductImage');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s)', $this->siteLangId), 'prod_image', array('id' => 'prod_image'));
        $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename"></span>';
        $fldImg->htmlAfterField = '</div><br/><span class="form-text text-muted">' . Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_500_x_500', $this->siteLangId) . '</span>';
        $frm->addHiddenField('', 'product_id');
        return $frm;
    }

    private function getCustomProductLangForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmCustomProductLang');
        $frm->addHiddenField('', 'product_id')->requirements()->setRequired();
        ;
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $langId, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Name', $langId), 'product_name');
        /* $frm->addTextArea( Labels::getLabel('LBL_Short_Description', $langId),'product_short_description');         */
        $frm->addTextBox(Labels::getLabel('LBL_YouTube_Video', $langId), 'product_youtube_video');
        $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $langId), 'product_description');
        $fld->htmlBeforeField = '<div class="editor-bar">';
        $fld->htmlAfterField = '</div>';

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $langId == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    public function CheckProductLinkWithCatBrand($productId)
    {
        $alertToShow = false;
        if ($productId) {
            $productRow = Product::getAttributesById($productId, array('product_brand_id'));
            $prodObj = new Product();
            $prodCategories = $prodObj->getProductCategories($productId);
            if (!$prodCategories || $productRow['product_brand_id'] == 0) {
                $alertToShow = true;
            }
            $this->set('alertToShow', $alertToShow);
        }
        return $alertToShow;
    }

    public function getTranslatedSpecData()
    {
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $prodSpecName = FatApp::getPostedData('prod_spec_name');
        $prodSpecValue = FatApp::getPostedData('prod_spec_value');

        if (empty($prodSpecName) || empty($prodSpecValue)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $translatedText = $this->translateLangFields(ProdSpecification::DB_TBL_LANG, ['prod_spec_name' => $prodSpecName[$siteDefaultLangId], 'prod_spec_value' => $prodSpecValue[$siteDefaultLangId]]);
        $data = [];
        foreach ($translatedText as $langId => $value) {
            $data[$langId]['prod_spec_name[' . $langId . ']'] = $value['prod_spec_name'];
            $data[$langId]['prod_spec_value[' . $langId . ']'] = $value['prod_spec_value'];
        }
        CommonHelper::jsonEncodeUnicode($data, true);
    }

    public function customProductForm($prodId = 0)
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        $prodId = FatUtility::int($prodId);
        if (0 == $prodId && FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && Product::getActiveCount($this->userParentId) >= SellerPackages::getAllowedLimit($this->userParentId, $this->siteLangId, 'ossubs_products_allowed')) {
            Message::addErrorMessage(Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }
        if (!User::canAddCustomProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'customProduct'));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        $displayInventoryTab = false;
        if ($prodId == 0) {
            $displayInventoryTab = true;
        }
        if ($prodId > 0) {
            $inventories = SellerProduct::getCatelogFromProductId($prodId);
            if (count($inventories) == 0) {
                $available = Product::availableForAddToStore($prodId, $this->userParentId);
                if ($available) {
                    $displayInventoryTab = true;
                }
            }
        }
        
        $productData = Product::getAttributesById($prodId, ['product_type', 'product_approved']);
        if ( /* FatApp::getConfig('CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL', FatUtility::VAR_INT) &&  */ isset($productData['product_approved']) && $productData['product_approved'] == 0) {
            $displayInventoryTab = false;
        }
        
        
        $productType = isset($productData['product_type']) ? $productData['product_type'] : '';
        $refererUrl = CommonHelper::redirectUserReferer(true);
        $arr = array_values(array_filter(explode('/', $refererUrl)));
        array_shift($arr);
        array_shift($arr);


        $isCustomFields = false;
        if ($prodId > 0) {
            $prod = new Product();
            $productCategories = $prod->getProductCategories($prodId);
            if (!empty($productCategories)) {
                $selectedCat = array_keys($productCategories);
                $pcObj = new ProductCategory($selectedCat[0]);
                $isCustomFields = $pcObj->isCategoryHasCustomFields($this->siteLangId);
            }
        }

        $this->set('isCustomFields', $isCustomFields);
        $this->set('previousAction', (isset($arr[1])) ? $arr[1] : 'index');
        $this->set('productId', $prodId);
        $this->set('productType', $productType);
        $this->_template->addJs(array('js/tagify.min.js', 'js/tagify.polyfills.min.js', 'js/cropper.js', 'js/cropper-main.js'));
        $this->set("includeEditor", true);
        $this->set('displayInventoryTab', $displayInventoryTab);
        $this->_template->render();
    }

    public function customProductGeneralForm($productId)
    {
        $productId = FatUtility::int($productId);
        $userId = $this->userParentId;
        if ($productId > 0) {
            $productRow = Product::getAttributesById($productId, ['product_seller_id']);
            if ($productRow && $productRow['product_seller_id'] != $userId) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        $productFrm = $this->getCustomProductIntialSetUpFrm($productId);

        if ($productId > 0) {
            $prodData = Product::getAttributesById($productId);
            foreach ($languages as $langId => $data) {
                $prod = new Product();
                $productLangData = $prod->getAttributesByLangId($langId, $productId);
                if (!empty($productLangData)) {
                    $prodData['product_name'][$langId] = $productLangData['product_name'];
                    $prodData['product_youtube_video'][$langId] = $productLangData['product_youtube_video'];
                    //$prodData['product_description'][$langId] = $productLangData['product_description'];
                    $prodData['product_description_' . $langId] = $productLangData['product_description'];
                }
            }

            $taxData = array();
            $tax = Tax::getTaxCatObjByProductId($productId, $this->siteLangId);
            if ($prodData && $prodData['product_seller_id'] > 0) {
                $tax->addCondition('ptt_seller_user_id', '=', $prodData['product_seller_id']);
            } else {
                $tax->addCondition('ptt_seller_user_id', '=', 0);
            }

            $activatedTaxServiceId = Tax::getActivatedServiceId();

            $tax->addFld(['ptt_taxcat_id', 'ptt_taxcat_id_rent']);
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
                $prodData['ptt_taxcat_id'] = $taxData['ptt_taxcat_id'];
                $prodData['ptt_taxcat_id_rent'] = $taxData['ptt_taxcat_id_rent'];
                $prodData['taxcat_name'] = $taxData['taxcat_name'];
                $prodData['taxcat_name_rent'] = $taxData['taxcat_name_rent'];
            }

            $srch = Product::getSearchObject($this->siteLangId);
            $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'tp.product_brand_id = brand.brand_id', 'brand');
            $srch->joinTable(Brand::DB_TBL_LANG, 'LEFT OUTER JOIN', 'brandlang_brand_id = brand.brand_id AND brandlang_lang_id = ' . $this->siteLangId);
            $srch->addMultipleFields(array('product_brand_id', 'IFNULL(brand_name,brand_identifier) as brand_name', 'IFNULL(brand.brand_active,1) AS brand_active', 'IFNULL(brand.brand_deleted,0) AS brand_deleted'));
            $srch->addCondition('product_id', '=', $productId);
            $srch->addHaving('brand_active', '=', applicationConstants::YES);
            $srch->addHaving('brand_deleted', '=', applicationConstants::NO);
            $rs = $srch->getResultSet();
            $brandData = FatApp::getDb()->fetch($rs);
            if (!empty($brandData)) {
                $prodData['product_brand_id'] = $brandData['product_brand_id'];
                $prodData['brand_name'] = $brandData['brand_name'];
            }

            $prod = new Product();
            $productCategories = $prod->getProductCategories($productId);
            if (!empty($productCategories)) {
                $selectedCat = array_keys($productCategories);
                $prodCat = new ProductCategory();
                $selectedCatName = $prodCat->getParentTreeStructure($selectedCat[0], 0, '', $this->siteLangId);
                $prodData['category_name'] = html_entity_decode($selectedCatName);
                $prodData['ptc_prodcat_id'] = $selectedCat[0];
            }

            $productFrm->fill($prodData);
        }

        unset($languages[$siteDefaultLangId]);
        $this->set('productFrm', $productFrm);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);
        $this->_template->render(false, false);
    }

    public function setupCustomProduct()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);

        if (0 == $productId && FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && Product::getActiveCount($this->userParentId) >= SellerPackages::getAllowedLimit($this->userParentId, $this->siteLangId, 'ossubs_products_allowed')) {
            FatUtility::dieWithError(Labels::getLabel('MSG_You_have_crossed_your_package_limit', $this->siteLangId));
        }

        $frm = $this->getCustomProductIntialSetUpFrm($productId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($post['product_brand_id'] < 1 && FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Brand_From_List', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($post['ptc_prodcat_id'] < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Category_From_List', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (FatUtility::int($post['ptt_taxcat_id_rent']) < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Tax_Category_From_List', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($productId > 0) {
            $prodSellerId = Product::getAttributesById($productId, 'product_seller_id');
            if ($prodSellerId != $this->userParentId) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }

        $data = $post;
        if ($productId == 0) {
            $data['product_seller_id'] = $this->userParentId;
            $prodRequireAdminApproval = FatApp::getConfig("CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL", FatUtility::VAR_INT, 1);
            $data['product_approved'] = ($prodRequireAdminApproval == 1) ? 0 : 1;
        }
        $data['product_added_by_admin_id'] = 0;

        $prod = new Product($productId);
        if (!$prod->saveProductData($data)) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        Product::updateMinPrices($productId);

        if (!$prod->saveProductLangData($post)) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!$prod->saveProductCategory($post['ptc_prodcat_id'])) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!$prod->saveProductTax($post['ptt_taxcat_id'], $this->userParentId, SellerProduct::PRODUCT_TYPE_PRODUCT, $post['ptt_taxcat_id_rent'])) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($productId == 0 && FatApp::getConfig("CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL", FatUtility::VAR_INT, 1)) {
            $mailData = array(
                'request_title' => $post['product_identifier'],
                'brand_name' => (!empty($post['brand_name'])) ? $post['brand_name'] : ''
            );
            $email = new EmailHandler();
            if (!$email->sendNewCatalogNotification($this->siteLangId, $mailData)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Email_could_not_be_sent', $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'customProduct'));
            }

            /* send notification to admin [ */
            $notificationData = array(
                'notification_record_type' => Notification::TYPE_CATALOG,
                'notification_record_id' => $prod->getMainTableRecordId(),
                'notification_user_id' => $this->userParentId,
                'notification_label_key' => Notification::NEW_CATALOG_REQUEST_NOTIFICATION,
                'notification_added_on' => date('Y-m-d H:i:s'),
            );

            if (!Notification::saveNotifications($notificationData)) {
                Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            /* ] */
        }

        $pcObj = new ProductCategory($post['ptc_prodcat_id']);

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->siteLangId));
        $this->set('productId', $prod->getMainTableRecordId());
        $this->set('isCustomFields', $pcObj->isCategoryHasCustomFields($this->siteLangId));
        $this->set('productType', $post['product_type']);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productAttributeAndSpecificationsFrm($productId)
    {
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }
        if (!User::canAddCustomProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'customProduct'));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        $productId = FatUtility::int($productId);
        if ($productId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $productFrm = $this->getProductAttributeAndSpecificationsFrm($productId);
        $productData = Product::getAttributesById($productId);
        $prodShippingDetails = Product::getProductShippingDetails($productId, $this->siteLangId, $productData['product_seller_id']);
        $productData['ps_free'] = isset($prodShippingDetails['ps_free']) ? $prodShippingDetails['ps_free'] : 0;
        $prodSpecificsDetails = Product::getProductSpecificsDetails($productId);
        $productData['product_warranty'] = isset($prodSpecificsDetails['product_warranty']) ? $prodSpecificsDetails['product_warranty'] : '';
        $productFrm->fill($productData);

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);
        $this->set('productFrm', $productFrm);
        $this->set('productData', $productData);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);
        $this->set('productId', $productId);
        $this->_template->render(false, false, 'seller/product-attribute-and-specifications-frm.php');
    }

    public function setUpProductAttributes()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);
        $frm = $this->getProductAttributeAndSpecificationsFrm($productId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodData = Product::getAttributesById($productId, array('product_seller_id', 'product_type'));
        if ($prodData['product_seller_id'] != $this->userParentId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $prod = new Product($productId);
        if (!$prod->saveProductData($post)) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $post['ps_product_id'] = $productId;
        $productSpecifics = new ProductSpecifics($productId);
        $productSpecifics->assignValues($post);
        $data = $productSpecifics->getFlds();
        if (!$productSpecifics->addNew(array(), $data)) {
            Message::addErrorMessage($productSpecifics->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* if ($prodData['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
          $psFree = isset($post['ps_free']) ? $post['ps_free'] : 0;
          $psFromCountryId = 0;
          $prodShippingDetails = Product::getProductShippingDetails($productId, $this->siteLangId, $prodData['product_seller_id']);
          if (!empty($prodShippingDetails)) {
          $psFromCountryId = $prodShippingDetails['ps_from_country_id'];
          }
          if (!$prod->saveProductSellerShipping($prodData['product_seller_id'], $psFree, $psFromCountryId)) {
          Message::addErrorMessage($prod->getError());
          FatUtility::dieWithError(Message::getHtml());
          }
          } */
        $this->set('msg', Labels::getLabel('LBL_Product_Attributes_Setup_Successful', $this->siteLangId));
        $this->set('productId', $prod->getMainTableRecordId());
        $this->_template->render(false, false, 'json-success.php');
    }

    public function prodSpecForm($productId)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productId = FatUtility::int($productId);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $prodSpecId = FatApp::getPostedData('prodSpecId', FatUtility::VAR_INT, 0);
        if ($productId < 1 || $langId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodSpecData = array();
        if ($prodSpecId > 0) {
            if (!UserPrivilege::canEditSellerProductSpecification($prodSpecId, $productId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $prodSpec = new ProdSpecification();
            $prodSpecData = $prodSpec->getProdSpecification($prodSpecId, $productId, $langId, true, true);
        }

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);

        $this->set('langId', $langId);
        $this->set('prodSpecData', $prodSpecData);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);
        $this->_template->render(false, false, 'seller/prod-spec-form.php');
    }

    public function prodSpecificationsByLangId()
    {
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($productId < 1 || $langId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prod = new Product($productId);
        $productSpecifications = $prod->getProdSpecificationsByLangId($this->siteLangId);
        if ($productSpecifications === false) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('siteDefaultLang', FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $this->set('productSpecifications', $productSpecifications);
        $this->set('langId', $langId);
        $this->_template->render(false, false, 'seller/product-specifications.php');
    }

    public function setupProductSpecifications()
    {
        $post = FatApp::getPostedData();
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);
        $prodSpecId = FatApp::getPostedData('prodSpecId', FatUtility::VAR_INT, 0);
        $isFileForm = FatApp::getPostedData('isFileForm', FatUtility::VAR_INT, 0);
        $isAutoCompleteData = FatApp::getPostedData('autocomplete_lang_data', FatUtility::VAR_INT, 0);
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        if ($prodSpecId > 0) {
            if (!UserPrivilege::canEditSellerProductSpecification($prodSpecId, $productId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $prod = new Product($productId);
        $langId = $post['langId'];

        $fileUpload = false;
        if ($isFileForm) {
            $fileUpload = true;
        }
        $specGrp = (isset($post['prodspec_group'])) ? $post['prodspec_group'] : "";

        if (!$specId = $prod->saveProductSpecifications($prodSpecId, $langId, $post['prodspec_name'][$langId], $post['prodspec_value'][$langId], $specGrp, $isFileForm, $fileUpload, $isAutoCompleteData, $post)) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($isFileForm && 1 > $prodSpecId) { /* MOVE SPECIFICATION TEMP FILES */
            $prodSpecId = $this->moveSpecificationTempFiles($productId, $specId);
        }


        $this->set('msg', Labels::getLabel('LBL_Specification_added_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function prodSpecGroupAutoComplete()
    {
        $post = FatApp::getPostedData();
        $srch = ProdSpecification::getSearchObject($post['langId'], false);
        if (!empty($post['keyword'])) {
            $srch->addCondition('prodspec_group', 'LIKE', '%' . $post['keyword'] . '%');
        }
        $srch->setPageSize(FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10));
        $srch->addMultipleFields(array('DISTINCT(prodspec_group)'));
        $rs = $srch->getResultSet();
        $prodSpecGroup = FatApp::getDb()->fetchAll($rs);
        $json = array();
        foreach ($prodSpecGroup as $key => $group) {
            $json[] = array(
                'name' => strip_tags(html_entity_decode($group['prodspec_group'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function productOptionsAndTag($productId)
    {
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }
        if (!User::canAddCustomProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'customProduct'));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        $productId = FatUtility::int($productId);
        if ($productId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $productTags = Product::getProductTags($productId);
        $productOptions = Product::getProductOptions($productId, $this->siteLangId);
        $productType = Product::getAttributesById($productId, 'product_type');
        $this->set('productTags', $productTags);
        $this->set('productOptions', $productOptions);
        $this->set('productId', $productId);
        $this->set('productType', $productType);
        $this->_template->render(false, false, 'seller/product-options-and-tag.php');
    }

    public function upcListing($productId)
    {
        $productId = FatUtility::int($productId);
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($productId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_product_id', '=', $productId);
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $upcCodeData = FatApp::getDb()->fetchAll($rs, 'upc_options');

        $productOptions = Product::getProductOptions($productId, $this->siteLangId, true);
        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '|');
        $this->set('optionCombinations', $optionCombinations);
        $this->set('upcCodeData', $upcCodeData);
        $this->set('productId', $productId);
        $this->_template->render(false, false);
    }

    public function updateUpc(int $productId = 0)
    {
        /* if (!UserPrivilege::canEditSellerProductSpecification($prodSpecId, $productId)) {
          Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
          FatUtility::dieWithError(Message::getHtml());
          } */

        if (!$productId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $post = FatApp::getPostedData();
        if (false === $post || $post['code'] == '') {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_fill_UPC/EAN_code', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $options = str_replace('|', ',', $post['optionValueId']);

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_product_id', '!=', $productId);
        $srch->addCondition('upc_code', '=', $post['code']);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row && $row['upc_product_id'] != $productId) {
            Message::addErrorMessage(Labels::getLabel('MSG_This_UPC/EAN_code_already_assigned_to_another_product', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_product_id', '=', $productId);
        $srch->addCondition('upc_options', '=', $options);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $data = array(
            'upc_code' => $post['code'],
            'upc_product_id' => $productId,
            'upc_options' => $options,
        );

        if ($row && $row['upc_product_id'] == $productId && $row['upc_options'] == $options) {
            $upcObj = new UpcCode($row['upc_code_id']);
        } else {
            $upcObj = new UpcCode();
        }

        $upcObj->assignValues($data);
        if (!$upcObj->save()) {
            Message::addErrorMessage($upcObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        Tag::updateProductTagString($productId);

        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->siteLangId));
        $this->set('product_id', $productId);
        $this->set('lang_id', FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateProductTag()
    {
        $this->userPrivilege->canEditProductTags(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);
        $tagId = FatApp::getPostedData('tag_id', FatUtility::VAR_INT, 0);
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($productId < 1 || $tagId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prod = new Product($productId);
        if (!$prod->addUpdateProductTag($tagId)) {
            Message::addErrorMessage(Labels::getLabel($prod->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        Tag::updateProductTagString($productId);

        $this->set('msg', Labels::getLabel('LBL_Tag_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProductTag()
    {
        $this->userPrivilege->canEditProductTags(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);
        $tagId = FatApp::getPostedData('tag_id', FatUtility::VAR_INT, 0);
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($productId < 1 || $tagId < 1) {
            Message::addErrorMessage(Labels::getLabel('LBL_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $prod = new Product($productId);
        if (!$prod->removeProductTag($tagId)) {
            Message::addErrorMessage(Labels::getLabel($prod->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        Tag::updateProductTagString($productId);

        $this->set('msg', Labels::getLabel('LBL_Tag_Removed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productShippingFrm($productId)
    {
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }
        if (!User::canAddCustomProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'customProduct'));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        $productId = FatUtility::int($productId);
        if ($productId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $productData = Product::getAttributesById($productId);
        $shippedByUserId = $productData['product_seller_id'];
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $shippedByUserId = 0;
        }

        $productFrm = $this->getProductShippingFrm($productId);
        $prodShippingDetails = Product::getProductShippingDetails($productId, $this->siteLangId, $shippedByUserId);
        $productData['ps_free'] = (isset($prodShippingDetails['ps_free'])) ? $prodShippingDetails['ps_free'] : 0;

        /* [ GET ATTACHED PROFILE ID */
        $profSrch = ShippingProfileProduct::getSearchObject();
        $profSrch->addCondition('shippro_product_id', '=', $productId);
        $profSrch->addCondition('shippro_user_id', '=', $shippedByUserId);
        $proRs = $profSrch->getResultSet();
        $profileData = FatApp::getDb()->fetch($proRs);
        if (!empty($profileData)) {
            $productData['shipping_profile'] = $profileData['profile_id'];
        }
        /* ] */

        $productFrm->fill($productData);
        $this->set('productFrm', $productFrm);
        $this->set('productId', $productId);
        $this->_template->render(false, false, 'seller/product-shipping-frm.php');
    }

    public function translatedProductData()
    {
        $prodName = FatApp::getPostedData('product_name', FatUtility::VAR_STRING, '');
        $prodDesc = FatApp::getPostedData('product_description', FatUtility::VAR_STRING, '');
        $toLangId = FatApp::getPostedData('toLangId', FatUtility::VAR_INT, 0);
        $data = array(
            'product_name' => $prodName,
            'product_description' => $prodDesc,
        );
        $product = new Product();
        $translatedData = $product->getTranslatedProductData($data, $toLangId);
        if (!$translatedData) {
            Message::addErrorMessage($product->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('productName', $translatedData[$toLangId]['product_name']);
        $this->set('productDesc', $translatedData[$toLangId]['product_description']);
        $this->set('msg', Labels::getLabel('LBL_Product_Data_Translated_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setUpProductShipping()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        if (!User::canAddCustomProduct()) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);
        $frm = $this->getProductShippingFrm($productId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodSellerId = Product::getAttributesById($productId, 'product_seller_id');
        if ($prodSellerId != $this->userParentId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
        }

        $prod = new Product($productId);
        if (!$prod->saveProductData($post)) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $psFree = isset($post['ps_free']) ? $post['ps_free'] : 0;
            if (!$prod->saveProductSellerShipping($prodSellerId, $psFree, 0)) {
                Message::addErrorMessage($prod->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        } else {
            if (!Product::isShipFromConfigured($productId)) {
                if (!$prod->saveProductSellerShipping(0, 0, FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0))) {
                    Message::addErrorMessage($prod->getError());
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
        }

        $shipBy = $this->userParentId;
        $shipProProdData = [];
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $shipBy = 0;
            $shippingProfile = ShippingProfile::getProfileArr($this->siteLangId, 0, true, true, true);
            $shippingProfileId = array_key_first($shippingProfile);

            $isShippingProfileLinked = ShippingProfileProduct::isShippingProfileLinked($productId);
            if (!$isShippingProfileLinked) {
                $shipProProdData = array(
                    'shippro_shipprofile_id' => $shippingProfileId,
                    'shippro_product_id' => $productId,
                    'shippro_user_id' => $shipBy
                );
            }
        } else {
            if (isset($post['shipping_profile']) && $post['shipping_profile'] > 0) {
                $shipProProdData = array(
                    'shippro_shipprofile_id' => $post['shipping_profile'],
                    'shippro_product_id' => $productId,
                    'shippro_user_id' => $shipBy
                );
            }
        }

        if (!empty($shipProProdData)) {
            $spObj = new ShippingProfileProduct();
            if (!$spObj->addProduct($shipProProdData)) {
                Message::addErrorMessage($spObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $isCustomFields = false;
        if ($productId > 0 && FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0)) {
            $productCategories = $prod->getProductCategories($productId);
            if (!empty($productCategories)) {
                $selectedCat = array_keys($productCategories);
                $pcObj = new ProductCategory($selectedCat[0]);
                $isCustomFields = $pcObj->isCategoryHasCustomFields($this->siteLangId);
            }
        }


        $this->set('msg', Labels::getLabel('LBL_Product_Shipping_Setup_Successful', $this->siteLangId));
        $this->set('productId', $prod->getMainTableRecordId());
        $this->set('isUseCustomFields', $isCustomFields);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadSizeChartImages()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->siteLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $fileHandlerObj = new AttachedFile();
        $productId = FatUtility::int($post['product_id']);
        $langId = FatUtility::int($post['lang_id']);

        /* [  DELETE OLD SIZE CHART */
        $productSizeChart = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_SIZE_CHART, $productId, 0, $langId, false, 0, 0, true);
        if (!empty($productSizeChart)) {
            foreach ($productSizeChart as $fileData) {
                if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_PRODUCT_SIZE_CHART, $productId, $fileData['afile_id'])) {
                    Message::addErrorMessage($fileHandlerObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
                if (file_exists(CONF_UPLOADS_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . $fileData['afile_physical_path']);
                }
            }
        }
        /* ] */

        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_PRODUCT_SIZE_CHART, $productId, 0, $_FILES['cropped_image']['name'], -1, false, $langId)
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set("msg", Labels::getLabel('LBL_Image_Uploaded_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function prodSpecMediaForm($productId)
    {
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productId = FatUtility::int($productId);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $prodSpecId = FatApp::getPostedData('prodSpecId', FatUtility::VAR_INT, 0);
        if ($productId < 1 || $langId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodSpecData = array();
        if ($prodSpecId > 0) {
            if (!UserPrivilege::canEditSellerProductSpecification($prodSpecId, $productId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $prodSpec = new ProdSpecification();
            $prodSpecData = $prodSpec->getProdSpecification($prodSpecId, $productId, $langId, true, true);
        }

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);

        $this->set('langId', $langId);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);
        $this->set('prodSpecData', $prodSpecData);
        $this->set('productId', $productId);
        $this->_template->render(false, false, 'seller/prod-spec-form-media.php');
    }

    public function uploadProductSpecificationMediaData()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $prodSpecId = FatUtility::int($post['prodspec_id']);
        $isImage = FatUtility::int($post['is_image']);
        $langId = FatUtility::int($post['langId']);
        $productId = FatUtility::int($post['prodspec_product_id']);

        if ($prodSpecId < 1 && (($isImage < 1 && !is_uploaded_file($_FILES['prodspec_files_' . $langId]['tmp_name'])) && ($isImage == 1 && !is_uploaded_file($_FILES['cropped_image']['tmp_name'])))) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
        }
        if ($prodSpecId > 0) {
            if (!UserPrivilege::canEditSellerProductSpecification($prodSpecId, $productId)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $saveToTemp = false;
        if (1 > $prodSpecId) {
            $saveToTemp = true;
        }

        $fileHandlerObj = new AttachedFile();
        $isImage = true;
        $fileId = 0;
        if (isset($_FILES['cropped_image']) && is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES) { /* in kbs */
                Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->siteLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
                FatUtility::dieJsonError(Message::getHtml());
            }
            $this->deleteSpecFile($productId, $prodSpecId, $langId, $saveToTemp);
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $productId, $prodSpecId, $_FILES['cropped_image']['name'], -1, $unique_record = false, $langId, 0, 0, $saveToTemp)) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            $fileId = $fileHandlerObj->getMainTableRecordId();
        } else if (is_uploaded_file($_FILES['prodspec_files_' . $langId]['tmp_name'])) {
            $isImage = false;
            if ($_FILES['prodspec_files_' . $langId]['size'] > 10240000) { 
                Message::addErrorMessage(Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            
            $this->deleteSpecFile($productId, $prodSpecId, $langId, $saveToTemp);
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['prodspec_files_' . $langId]['tmp_name'], AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $productId, $prodSpecId, $_FILES['prodspec_files_' . $langId]['name'], -1, $unique_record = false, $langId, 0, 0, $saveToTemp)) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            $fileId = $fileHandlerObj->getMainTableRecordId();
        }

        if ($fileId > 0) {
            $attachmentUrl = UrlHelper::generateUrl('Image', 'attachment', [$fileId, $saveToTemp], CONF_WEBROOT_FRONTEND);
            if ($isImage) {
                $fileHtml = "<img src='" . $attachmentUrl . "' class='img-thumbnail image-small' />";
            } else {
                $fileHtml = "<a href='" . $attachmentUrl . "' download><i class='fa fa-download' aria-hidden='true'></i></a>";
            }
            $this->set('uploadedFileData', $fileHtml);
        }

        $this->set("msg", Labels::getLabel('LBL_Specification_File_Uploaded_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php', false, false);
    }

    public function prodSpecificationsMediaByLangId()
    {
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        if (!UserPrivilege::canSellerEditCustomProduct($this->userParentId, $productId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($productId < 1 || $langId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prod = new Product($productId);
        $productSpecifications = $prod->getProdSpecificationsByLangId($this->siteLangId, true);
        if ($productSpecifications === false) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('productSpecifications', $productSpecifications);
        $this->set('langId', $langId);
        $this->set('siteDefaultLangId', FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $this->set('productId', $productId);
        $this->_template->render(false, false, 'seller/product-specifications-media.php');
    }

    private function deleteSpecFile(int $productId, int $prodSpecId, int $langId = 0, bool $saveToTemp = false): bool
    {
        if ($saveToTemp) {
            $criteria = array(
                'afile_record_id' => $productId,
                'afile_lang_id' => $langId,
                'afile_type' => AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE,
            );
            $filesData = AttachedFile::getTempImagesWithCriteria($criteria);
        } else {
            $displayAll = true;
            if ($langId > 0) {
                $displayAll = false;
            }
            $filesData = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $productId, $prodSpecId, $langId, $displayAll, 0, 0, false, $displayAll);
        }

        if (!empty($filesData)) {
            foreach ($filesData as $fileData) {
                $fileId = $fileData['afile_id'];
                $prodObj = new Product();
                if (!$prodObj->deleteProductSpecFile(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $productId, $fileId, $saveToTemp)) {
                    Message::addErrorMessage($prodObj->getError());
                    //FatUtility::dieJsonError(Message::getHtml());
                    return false;
                }

                if (file_exists(CONF_UPLOADS_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . $fileData['afile_physical_path']);
                }
            }
        }
        return true;
    }

    public function getSpecificationTranslatedData()
    {
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $post = FatApp::getPostedData();
        if (0 > $langId) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Invalid_Request", $this->siteLangId));
        }
        $specObj = new ProdSpecification();
        $data = $specObj->getTranslatedProductSpecData($post, $langId);

        if (!empty($data) && isset($data[$langId])) {
            FatUtility::dieJsonSuccess($data[$langId]);
        }
    }

    private function moveSpecificationTempFiles(int $productId, int $specId): bool
    {
        $criteria = array(
            'afile_record_id' => $productId,
            'afile_type' => AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE,
        );
        $attachedFiles = AttachedFile::getTempImagesWithCriteria($criteria);

        if (!empty($attachedFiles)) {
            foreach ($attachedFiles as $attachFile) {
                $fileId = $attachFile['afile_id'];
                unset($attachFile['afile_id']);
                $attachFile['afile_record_subid'] = $specId;
                $fileHandler = new AttachedFile();
                $fileHandler->assignValues($attachFile);
                if (!$fileHandler->save()) {
                    Message::addErrorMessage($fileHandler->getError());
                    return false;
                }
                //unset($fileHandler);
                $whr = ['smt' => 'afile_id = ?', 'vals' => array($fileId)];
                FatApp::getDb()->deleteRecords(AttachedFile::DB_TBL_TEMP, $whr);
            }
        }
        return true;
    }

}
