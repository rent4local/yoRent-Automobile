<?php
class CompareProductsController extends MyAppController
{

    public function __construct($action)
    {
        parent::__construct($action);

        if (!FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1)) {
            Message::addInfo(Labels::getLabel("MSG_Invalid_Request", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Home'));
        }
    }

    public function index()
    {
        if (true ===  MOBILE_APP_API_CALL) {
            $compareProductsData = json_decode(FatApp::getPostedData('appData'), true);
        } else {
            $compareProductsData = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME];
        }

        if (empty($compareProductsData)) {
            FatApp::redirectUser(UrlHelper::generateUrl());
        }

        $prodArr = $compareProductsData['products'];
        $prodIdArr = array_values($prodArr);
        $selProdArr = array_keys($prodArr);
        
        $compProd = new CompareProduct();
        $productsDetail = $compProd->productsDetail($selProdArr, $this->siteLangId);
        
        $diff = array_diff($selProdArr, array_keys($productsDetail));

        if (!empty($diff)) {
            foreach ($diff as $removedProd) {
                $this->unsetSession($removedProd);
            }
            $prodArr = $compareProductsData['products'];
            $prodIdArr = array_values($prodArr);
            $selProdArr = array_keys($prodArr);
        }
        $attrGrpArr = [];
        $infoAttrArr = [];

        if (FatApp::getConfig("CONF_USE_CUSTOM_FIELDS", FatUtility::VAR_INT, 1)) {
            $attrGrpArr = $this->getCatAttributes($compareProductsData['attr_grp_cat_id']);
            $infoAttrArr = $this->attributes($prodIdArr);
        }
        
        $prodOptionsArr = $this->catalogOptions($prodArr);
        $selProdOptionsArr = SellerProduct::getSellersProductOptions($selProdArr, true, $this->siteLangId);

        $selectedOptionsArr = array();
        $idsForReview = array_column($productsDetail, 'selprod_user_id');
        
        //echo "<pre>"; print_r($idsForReview); echo "</pre>"; exit;
        if (!empty($selProdOptionsArr)) {
            foreach ($selProdOptionsArr as $selProdOptionArr) {
                $selectedOptionsArr[$selProdOptionArr['selprodoption_selprod_id']][$selProdOptionArr['selprodoption_option_id']] = $selProdOptionArr['selprodoption_optionvalue_id'];
            }
        }
        
        $prodReviewArr = array();
        $selProdReviews = new SelProdReview();
        $prodReviews = $selProdReviews->reviews($prodIdArr, $this->siteLangId, $idsForReview);
        if (!empty($prodReviews)) {
            foreach ($prodReviews as $prodReview) {
                /* $prodReviewArr[$prodReview['spreview_product_id']][] = $prodReview; */
                $prodReviewArr[$prodReview['spreview_seller_user_id'].'_'.$prodReview['spreview_product_id']][] = $prodReview;
            }
        }
        
        $moreSellersProd = array();
        if (!empty($productsDetail)) {
            $selProdCodeArr = array_column($productsDetail, 'selprod_code');
            $moreSellersProd = $this->moreSellersProd($selProdArr, $selProdCodeArr);
        }

        $cartObj = new Cart();
        $specProdArr = $compProd->getCompareProductsSpecifications($prodIdArr, $this->siteLangId);
        $specificationArr = $this->sortSpecProdArr($specProdArr);

        /* share url */
        $shareUrl = $this->generateShareUrl($compareProductsData['attr_grp_cat_id'], $prodArr);
        /* -- */

        $this->set('shareUrl', $shareUrl);
        $this->set('specificationArr', $specificationArr);
        $this->set('attrGrpArr', $attrGrpArr);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('prodArr', $prodArr);
        $this->set('infoAttrArr', $infoAttrArr);
        $this->set('prodReviewArr', $prodReviewArr);
        $this->set('productsDetail', $productsDetail);
        $this->set('prodOptionsArr', $prodOptionsArr);
        $this->set('selectedOptionsArr', $selectedOptionsArr);
        $this->set('moreSellersProd', $moreSellersProd);
        $this->set('cartType', $cartObj->getCartTYpe());
        $this->set('rentalTypeArr', applicationConstants::rentalTypeArr($this->siteLangId));
        $this->_template->render();
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array('title' => Labels::getLabel('LBL_Product_Comparison', $this->siteLangId));
        return $nodes;
    }

    public function add()
    {
        $selProdId = FatApp::getPostedData('selProdId', FatUtility::VAR_INT, 0);
        $isUrlCompare = FatApp::getPostedData('isUrlCompare', FatUtility::VAR_INT, 0);
        if (true ===  MOBILE_APP_API_CALL) {
            $appData = json_decode(FatApp::getPostedData('appData'), true);
            $dataToSave = $this->addUpdate($selProdId, $appData);
            $this->set('dataToSave', $dataToSave);
            $this->_template->render();
        }
        $this->addUpdate($selProdId, [], $isUrlCompare);
        $json['productListing'] = $this->listing();
        $json['comparedProductsCount'] = $this->comparedProductsCount();
        $json['msg'] = Labels::getLabel('MSG_Product_added_to_compare_list_successfully!', $this->siteLangId);
        FatUtility::dieJsonSuccess($json);
    }

    public function removeProduct()
    {
        $selProdId = FatApp::getPostedData('selProdId', FatUtility::VAR_INT, 0);
        $isUrlCompare = FatApp::getPostedData('isUrlCompare', FatUtility::VAR_INT, 0);

        if (true ===  MOBILE_APP_API_CALL) {
            $appData = json_decode(FatApp::getPostedData('appData'), true);
            $responseData = $this->unsetSession($selProdId, $appData);
            $this->set('dataToSave', $responseData);
            $this->set('msg', Labels::getLabel('MSG_Product_remove_from_compare_list_successfully!', $this->siteLangId));
            $this->_template->render();
        }

        if ($isUrlCompare) {
            $this->redirectCompareUrl(0, $selProdId, $isUrlCompare);
        }

        $this->unsetSession($selProdId);
        $json['productListing'] = $this->listing();
        $json['comparedProductsCount'] = $this->comparedProductsCount();
        $json['msg'] = Labels::getLabel('MSG_Product_remove_from_compare_list_successfully!', $this->siteLangId);
        FatUtility::dieJsonSuccess($json);
    }

    public function clearList()
    {
        unset($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]);
        $json['msg'] = Labels::getLabel('MSG_All_Products_removed_from_compare_list_successfully!', $this->siteLangId);
        FatUtility::dieJsonSuccess($json);
    }

    public function updateProduct()
    {
        $oldProdId = FatApp::getPostedData('oldProdId', FatUtility::VAR_INT, 0);
        $newProdId = FatApp::getPostedData('newProdId', FatUtility::VAR_INT, 0);

        $sellProd = new SellerProduct();
        $prodDetails = $sellProd->getProdDetail($newProdId, $this->siteLangId);
        if (empty($prodDetails)) {
            FatUtility::dieWithError(Labels::getLabel('LBL_No_Attribute_group_category_attach_with_this_product', $this->siteLangId));
        }

        $prodAttrGrpCatId = $prodDetails['product_spec_cat_id'];

        if (true ===  MOBILE_APP_API_CALL) {
            $appData = json_decode(FatApp::getPostedData('appData'), true);
            $this->validate($newProdId, $prodAttrGrpCatId, 2, $appData);
            $resultAppData = $this->unsetSession($oldProdId, $appData);
            $response = $this->addUpdate($newProdId, $resultAppData);
            $this->set('dataToSave', $response);
            $this->_template->render();
        }

        $this->validate($newProdId, $prodAttrGrpCatId, 2);

        $this->unsetSession($oldProdId);
        $this->addUpdate($newProdId);
        $json['msg'] = Labels::getLabel('MSG_Product_Updated_successfully!', $this->siteLangId);
        FatUtility::dieJsonSuccess($json);
    }

    public function addUpdate($selProdId, $appData = array(), $isUrlCompare = 0)
    {
        if (1 > $selProdId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        $sellProd = new SellerProduct();
        $prodDetails = $sellProd->getProdDetail($selProdId, $this->siteLangId);
        if (empty($prodDetails)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_No_Attribute_group_category_attach_with_this_product', $this->siteLangId));
        }

        if ($prodDetails['prodcat_comparison'] == 0) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Product_category_is_not_enabled_for_comparison', $this->siteLangId));
        }

        $prodAttrGrpCatId = $prodDetails['product_spec_cat_id'];

        if (true ===  MOBILE_APP_API_CALL) {
            $this->validate($selProdId, $prodAttrGrpCatId, 1, $appData);

            $appData['attr_grp_cat_id'] = $prodAttrGrpCatId;
            $appData['products'][$selProdId] = $prodDetails['selprod_product_id'];
            $appData['products_detail'][$selProdId] = $prodDetails;
            return $appData;
        }

        $this->validate($selProdId, $prodAttrGrpCatId, 1, [], $isUrlCompare);

        if ($isUrlCompare) {
            $this->redirectCompareUrl($prodDetails['selprod_product_id'], $selProdId);
        }

        $this->setSession($prodDetails['selprod_product_id'], $selProdId, $prodAttrGrpCatId, $prodDetails);
        return true;
    }

    private function validate($selProdId, $prodAttrGrpCatId, $addOrUpdate = 1, $appSessionDetails = array(), $isUrlCompare = 0)
    {
        $sessionElement = ($isUrlCompare == 1) ? CompareProduct::COMPARE_SESSION_URL_ELEMENT_NAME : CompareProduct::COMPARE_SESSION_ELEMENT_NAME;

        if (true ===  MOBILE_APP_API_CALL) {
            $compareProductsData = $appSessionDetails;
            $maxCountLimit = CompareProduct::COMPARE_PRODUCTS_APP_LIMIT;
        } else {
            $compareProductsData = isset($_SESSION[$sessionElement]) ? $_SESSION[$sessionElement] : [];
            $maxCountLimit = CompareProduct::COMPARE_PRODUCTS_LIMIT;
        }

        if (!empty($compareProductsData)) {
            $count = 1;
            if ($addOrUpdate == 2) {
                $count = 0;
            }
            if (count($compareProductsData['products']) + $count > $maxCountLimit) {
                FatUtility::dieJsonError(sprintf(Labels::getLabel('LBL_You_can_compare_maximum_%s_products', $this->siteLangId), $maxCountLimit));
            }

            if ($prodAttrGrpCatId != $compareProductsData['attr_grp_cat_id']) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_You_can_compare_same_type_of_products_only', $this->siteLangId));
            }

            if (array_key_exists($selProdId, $compareProductsData['products'])) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Product_already_added_into_compare_list', $this->siteLangId));
            }
        }
        return true;
    }

    private function getCatAttributes($attrCatId)
    {
        if (1 > $attrCatId) {
            return false;
        }

        $cpObj = new CompareProduct();
        $srch = $cpObj->getCatAttributes($attrCatId, $this->siteLangId, true);
        $rs = $srch->getResultSet();
        $attributes = FatApp::getDb()->fetchAll($rs);

        $attrGrpArr = array();
        foreach ($attributes as $attribute) {
            $attrgrp_name = $attribute['attrgrp_name'];
            if ('' == $attrgrp_name) {
                $attrgrp_name = 'Others';
            }
            $attrGrpArr[$attribute['attr_attrgrp_id']]['attr_grp_name'] = $attrgrp_name;
            $attrGrpArr[$attribute['attr_attrgrp_id']]['attributes'][] = $attribute;
        }

        return $attrGrpArr;
    }

    private function attributes($prodIdArr)
    {
        if (empty($prodIdArr)) {
            return false;
        }

        $infoAttrArr = array();

        $numericAttributes = Product::getProductNumericAttributes($prodIdArr);
        $textAttributes = Product::getProductTextualAttributes($prodIdArr, $this->siteLangId);

        foreach ($numericAttributes as $numAttr) {
            $infoAttrArr[$numAttr['prodnumattr_product_id']][$numAttr['prodnumattr_attrgrp_id']] = $numAttr;
        }

        foreach ($textAttributes as $textAttr) {
            if (!empty($infoAttrArr[$textAttr['prodtxtattr_product_id']]) && !empty($infoAttrArr[$textAttr['prodtxtattr_product_id']][$textAttr['prodtxtattr_attrgrp_id']])) {
                $infoAttrArr[$textAttr['prodtxtattr_product_id']][$textAttr['prodtxtattr_attrgrp_id']] += $textAttr;
            } else {
                $infoAttrArr[$textAttr['prodtxtattr_product_id']][$textAttr['prodtxtattr_attrgrp_id']] = $textAttr;
            }
        }

        return $infoAttrArr;
    }

    private function setSession($prodId, $selProdId, $prodAttrGrpCatId, $prodDetails = array())
    {
        $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['attr_grp_cat_id'] = $prodAttrGrpCatId;
        $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'][$selProdId] = $prodId;
        $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products_detail'][$selProdId] = $prodDetails;
        return true;
    }

    private function unsetSession($selProdId, $appData = array())
    {
        if (true ===  MOBILE_APP_API_CALL) {
            $compareProductsData = $appData;
        } else {
            $compareProductsData = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME];
        }

        if (1 > $selProdId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }


        if (!array_key_exists($selProdId, $compareProductsData['products'])) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        if (1 == count($compareProductsData['products'])) {
            unset($compareProductsData);
        } else {
            unset($compareProductsData['products'][$selProdId]);
            unset($compareProductsData['products_detail'][$selProdId]);
        }

        $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME] = isset($compareProductsData) ? $compareProductsData : [];

        if (true ===  MOBILE_APP_API_CALL) {
            return $compareProductsData;
        }
    }

    private function catalogOptions($prodArr)
    {
        $prodOptionsArr = array();
        $compProd = new CompareProduct();
        $attachedOptions = $compProd->attachedOptions($prodArr, $this->siteLangId);

        if (empty($attachedOptions)) {
            return $prodOptionsArr;
        }

        $compProd = new CompareProduct();
        $productsOptions = $compProd->optionsValues($prodArr, array_keys($attachedOptions), $this->siteLangId);
        if (!empty($productsOptions)) {
            foreach ($productsOptions as $productOptions) {
                if (empty($prodOptionsArr[$productOptions['product_id']][$productOptions['option_id']])) {
                    $prodOptionsArr[$productOptions['product_id']][$productOptions['option_id']] = $attachedOptions[$productOptions['option_id']];
                }
                $prodOptionsArr[$productOptions['product_id']][$productOptions['option_id']]['values'][$productOptions['optionvalue_id']] = $productOptions;
            }
        }
        return $prodOptionsArr;
    }

    public function listing()
    {
        $detailPage = FatApp::getPostedData('detail_page', FatUtility::VAR_INT, 0);
        $productsDetail = array();
        if (!empty($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME])) {
            $productsDetail = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products_detail'];
        }
        $this->set('siteLangId', $this->siteLangId);
        $this->set('productsDetail', $productsDetail);

        if (1 == $detailPage) {
            $this->_template->render(false, false);
        } else {
            return $this->_template->render(false, false, 'compare-products/listing.php', true);
        }
    }

    public function autocomplete()
    {
        $json = array();
        $keyword = FatApp::getPostedData('keyword', null, '');
        $attrgrpid = FatApp::getPostedData('attrgrpid', FatUtility::VAR_INT, 0);

        $selprodArr = [];
        if (true ===  MOBILE_APP_API_CALL) {
            $catId = FatApp::getPostedData('attr_grp_cat_id', FatUtility::VAR_INT, 0);
        } elseif ($attrgrpid > 0) {
            $catId = $attrgrpid;

            if (isset($_SESSION[CompareProduct::COMPARE_SESSION_URL_ELEMENT_NAME]['products']) && !empty($_SESSION[CompareProduct::COMPARE_SESSION_URL_ELEMENT_NAME]['products'])) {
                $selprodArr = array_keys($_SESSION[CompareProduct::COMPARE_SESSION_URL_ELEMENT_NAME]['products']);
            }
        } else {
            $catId = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['attr_grp_cat_id'];
            if (empty($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['attr_grp_cat_id'])) {
                die(json_encode($json));
            }

            if (isset($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products']) && !empty($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) {
                $selprodArr = array_keys($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products']);
            }
        }

        $compProd = new CompareProduct();
        $records = $compProd->searchProducts($keyword, $catId, $this->siteLangId, $selprodArr, true);

        foreach ($records as $key => $option) {
            $options = SellerProduct::getSellerProductOptions($key, true, $this->siteLangId);
            $variantsStr = '';
            array_walk($options, function ($item, $key) use (&$variantsStr) {
                $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
            });
            $shopName = strip_tags(html_entity_decode($option['shop_name'], ENT_QUOTES, 'UTF-8'));
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['product_name'], ENT_QUOTES, 'UTF-8')) . $variantsStr . ' | ' . Labels::getLabel("LBL_Seller_:", $this->siteLangId) . ' ' . $shopName,
            );
        }

        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('resultData', $json);
            $this->_template->render();
        }

        die(json_encode($json));



        /* foreach ($records as $key => $record) {
            $productName = strip_tags(html_entity_decode($record['product_name'], ENT_QUOTES, 'UTF-8'));
            $shopName = strip_tags(html_entity_decode($record['shop_name'], ENT_QUOTES, 'UTF-8'));
            $json[] = array(
                'id' => $record['selprod_id'],
                'name'  => $productName .' | '. $shopName
            );
        }

        if (true ===  MOBILE_APP_API_CALL) {
            $this->set('resultData', $json);
            $this->_template->render();
        }

        die(json_encode($json)); */
    }

    private function moreSellersProd($selProdArr, $selProdCodeArr)
    {
        $moreSelProd = array();
        $compProd = new CompareProduct();
        $moreSellersProd = $compProd->moreSellersProd($selProdArr, $selProdCodeArr, $this->siteLangId);
        if (!empty($moreSellersProd)) {
            foreach ($moreSellersProd as $prod) {
                $moreSelProd[$prod['shop_id']][$prod['selprod_code']] = $prod;
            }
        }
        return $moreSelProd;
    }

    private function comparedProductsCount()
    {
        $count = 0;
        if (!empty($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME])) {
            $count = count($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products']);
        }
        return $count;
    }

    private function sortSpecProdArr($arr)
    {
        if (empty($arr)) {
            return $arr;
        }

        $sortedArr = [];
        foreach ($arr as $val) {
            $sortedArr[$val['prodspec_group']][$val['prodspec_product_id']][] = $val;
        }
        return $sortedArr;
    }

    public function compare($get)
    {
        $get = base64_decode(strtr($get, '._-', '+/='));
        parse_str($get, $compareProductsData);

        $attr_grp_id = FatUtility::int($compareProductsData['attr_grp_cat_id']);
        unset($compareProductsData['url']);
        unset($compareProductsData['attr_grp_cat_id']);

        if (empty($compareProductsData) || $attr_grp_id < 1) {
            FatApp::redirectUser(UrlHelper::generateUrl('Home'));
        }

        $_SESSION[CompareProduct::COMPARE_SESSION_URL_ELEMENT_NAME]['attr_grp_cat_id'] = $attr_grp_id;
        $_SESSION[CompareProduct::COMPARE_SESSION_URL_ELEMENT_NAME]['products'] = $compareProductsData;

        $prodArr = $compareProductsData;
        $prodIdArr = array_values($prodArr);
        $selProdArr = array_keys($prodArr);

        $compProd = new CompareProduct();
        $productsDetail = $compProd->productsDetail($selProdArr, $this->siteLangId);
        $diff = array_diff($selProdArr, array_keys($productsDetail));
        if (!empty($diff)) {
            foreach ($diff as $removedProd) {
                $this->unsetSession($removedProd);
            }
            $prodArr = $compareProductsData;
            $prodIdArr = array_values($prodArr);
            $selProdArr = array_keys($prodArr);
        }

        $attrGrpArr = [];
        $infoAttrArr = [];

        if (FatApp::getConfig("CONF_USE_CUSTOM_FIELDS", FatUtility::VAR_INT, 1)) {
            $attrGrpArr = $this->getCatAttributes($attr_grp_id);
            $infoAttrArr = $this->attributes($prodIdArr);
        }

        $prodOptionsArr = $this->catalogOptions($prodArr);
        $selProdOptionsArr = SellerProduct::getSellersProductOptions($selProdArr, true, $this->siteLangId);

        $selectedOptionsArr = array();
        if (!empty($selProdOptionsArr)) {
            foreach ($selProdOptionsArr as $selProdOptionArr) {
                $selectedOptionsArr[$selProdOptionArr['selprodoption_selprod_id']][$selProdOptionArr['selprodoption_option_id']] = $selProdOptionArr['selprodoption_optionvalue_id'];
            }
        }
        $idsForReview = array_column($productsDetail, 'selprod_user_id');
        $prodReviewArr = array();
        $selProdReviews = new SelProdReview();
        $prodReviews = $selProdReviews->reviews($prodIdArr, $this->siteLangId, $idsForReview);
        if (!empty($prodReviews)) {
            foreach ($prodReviews as $prodReview) {
                /* $prodReviewArr[$prodReview['spreview_product_id']][] = $prodReview; */
                $prodReviewArr[$prodReview['spreview_seller_user_id'].'_'.$prodReview['spreview_product_id']][] = $prodReview;
            }
        }

        $moreSellersProd = array();
        if (!empty($productsDetail)) {
            $selProdCodeArr = array_column($productsDetail, 'selprod_code');
            $moreSellersProd = $this->moreSellersProd($selProdArr, $selProdCodeArr);
        }

        $cartObj = new Cart();

        $specProdArr = $compProd->getCompareProductsSpecifications($prodIdArr, $this->siteLangId);
        $specificationArr = $this->sortSpecProdArr($specProdArr);
        $this->set('specificationArr', $specificationArr);
        $this->set('attrGrpArr', $attrGrpArr);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('prodArr', $prodArr);
        $this->set('infoAttrArr', $infoAttrArr);
        $this->set('prodReviewArr', $prodReviewArr);
        $this->set('productsDetail', $productsDetail);
        $this->set('prodOptionsArr', $prodOptionsArr);
        $this->set('selectedOptionsArr', $selectedOptionsArr);
        $this->set('moreSellersProd', $moreSellersProd);
        $this->set('cartType', $cartObj->getCartTYpe());
        $this->set('rentalTypeArr', applicationConstants::rentalTypeArr($this->siteLangId));
        $this->set('attrGrpId', $attr_grp_id);
        $this->set('isUrlCompare', 1);

        /* share url */
        $shareUrl = $this->generateShareUrl($attr_grp_id, $prodArr);
        /* -- */

        $this->set('shareUrl', $shareUrl);
        $this->_template->render(true, true, 'compare-products/index.php');
    }

    public function redirectCompareUrl($productId, $selprodId, $isRemoveRequest = 0)
    {
        $compareProductsData = $_SESSION[CompareProduct::COMPARE_SESSION_URL_ELEMENT_NAME];
        $productsArr = isset($compareProductsData['products']) ? $compareProductsData['products'] : [];
        $attrGrpCatId = isset($compareProductsData['attr_grp_cat_id']) ? $compareProductsData['attr_grp_cat_id'] : 0;
        if (!isset($compareProductsData) || empty($productsArr) || empty($attrGrpCatId)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        if ($isRemoveRequest) {
            unset($compareProductsData['products'][$selprodId]);
            $msg = Labels::getLabel('MSG_Product_removed_from_compare_list_successfully!', $this->siteLangId);
        } else {
            $compareProductsData['products'][$selprodId] = $productId;
            $msg = Labels::getLabel('MSG_Product_added_to_compare_list_successfully!', $this->siteLangId);
        }
        if (!empty($compareProductsData['products'])) {
            $_SESSION[CompareProduct::COMPARE_SESSION_URL_ELEMENT_NAME]['products'] = $compareProductsData['products'];
            /* share url */
            $shareUrl = $this->generateShareUrl($attrGrpCatId, $compareProductsData['products']);
            /* -- */
        } else {
            $shareUrl = UrlHelper::generateUrl('Home');
        }

        $json['shareurl'] = $shareUrl;
        $json['msg'] = $msg;
        FatUtility::dieJsonSuccess($json);
    }

    public function generateShareUrl($attrGrpCatId, $productsArr)
    {
        if (empty($attrGrpCatId) || empty($productsArr)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        $queryStr = 'attr_grp_cat_id=' . $attrGrpCatId;
        foreach ($productsArr as $key => $val) {
            $queryStr = $queryStr . '&' . $key . '=' . $val;
        }
        $encodedQueryStr = strtr(base64_encode($queryStr), '+/=', '._-');
        return UrlHelper::generateFullUrl('compareProducts', 'compare', array($encodedQueryStr));
    }
}
