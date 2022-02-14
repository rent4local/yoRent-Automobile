<?php
class SellerProductTest extends YkModelTest
{

    /**
     * @dataProvider setSellerProductData
     */
    public function testSave($data, $expected)
    {
        $selprod = new SellerProduct();
        $selprod->setMainTableRecordId($data['selprod_id']);
        $selprod->assignValues($data);
        $result = $selprod->save();
        $this->assertEquals($expected, $result);
    }

    public function setSellerProductData()
    {
        $data = array('selprod_id' => 0, 'selprod_user_id' => 4, 'selprod_product_id' => 1, 'selprod_code' => 111, 'selprod_price' => 280, 'selprod_cost' => 1, 'selprod_stock' => 1, 'selprod_min_order_qty' => 0, 'selprod_subtract_stock' => 1, 'selprod_track_inventory' => 1, 'selprod_threshold_stock_level' => 0, 'selprod_sku' => "HGHFHG", 'selprod_condition' => 1, 'selprod_added_on' => '2021-01-04 00:00:00', 'selprod_updated_on' => '2021-01-04 00:00:00', 'selprod_available_from' => '2021-01-04 00:00:00', 'selprod_comments' => '', 'selprod_active' => 1, 'selprod_cod_enabled' => 0, 'selprod_fulfillment_type' => 1, 'selprod_sold_count' => 0, 'selprod_url_keyword' => '', 'selprod_max_download_times' => '', 'selprod_downloadable_link' => '', 'selprod_download_validity_in_days' => '', 'selprod_urlrewrite_id' => '', 'selprod_deleted' => 0); // Add product in seller inventory
        $data2 = array('selprod_id' => 185, 'selprod_user_id' => 4, 'selprod_product_id' => 1, 'selprod_code' => 111, 'selprod_price' => 280, 'selprod_cost' => 100, 'selprod_stock' => 1, 'selprod_min_order_qty' => 0, 'selprod_subtract_stock' => 1, 'selprod_track_inventory' => 1, 'selprod_threshold_stock_level' => 0, 'selprod_sku' => "HGHFHG", 'selprod_condition' => 1, 'selprod_added_on' => '2021-01-04 00:00:00', 'selprod_updated_on' => '2021-01-04 00:00:00', 'selprod_available_from' => '2021-01-04 00:00:00', 'selprod_comments' => '', 'selprod_active' => 1, 'selprod_cod_enabled' => 0, 'selprod_fulfillment_type' => 1, 'selprod_sold_count' => 0, 'selprod_url_keyword' => '', 'selprod_max_download_times' => '', 'selprod_downloadable_link' => '', 'selprod_download_validity_in_days' => '', 'selprod_urlrewrite_id' => '', 'selprod_deleted' => 0); // Update existing product

        return array(
            array($data, true),
            array($data2, true),
        );
    }

    /**
     * @dataProvider setUpdateLangData
     */
    public function testUpdateLangData($data, $mainTableId, $expected)
    {
        $selprod = new SellerProduct();
        $selprod->setMainTableRecordId($mainTableId);
        $result = $selprod->updateLangData($data['selprodlang_lang_id'], $data);
        $this->assertEquals($expected, $result);
    }

    public function setUpdateLangData()
    {
        $data = array(
            'selprodlang_selprod_id' => 185,
            'selprodlang_lang_id' => 1,
            'selprod_title' => 'New Seller Product Language Data Update Test Case',
            'selprod_comments' => 'New Seller Product Language Data Update Test Case Comment',
            'selprod_features' => 'New Seller Product Language Data Update Test Case features',
            'selprod_warranty' => 'New Seller Product Language Data Update Test Case warranty',
        ); // Update Existing Seller product Language Data

        return array(
            array($data, 0, false), // Seller Product Id 0
            array($data, 185, true), // Update the Data for 185
        );
    }

    /**
     * @dataProvider setAddUpdateSellerUpsellProducts
     */
    public function testAddUpdateSellerUpsellProducts($selProdId, $upselPodIds, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->addUpdateSellerUpsellProducts($selProdId, $upselPodIds);
        $this->assertEquals($expected, $result);
    }

    public function setAddUpdateSellerUpsellProducts()
    {
        return array(
            array(189, [188], true), // Attach 188 with 189
            array(185, [0], true), // skip the 0 to attach
            array(185, [185, 188, 120], true), // Attach 185, 188, 120 with 185
            array(185, 120, false), // Give Paremeter Type Error
            array('', 120, false), // False if  seplprod id is not given
        );
    }

    /**
     * @dataProvider setAddUpdateSellerUpsellProducts
     */
    public function testAddUpdateSellerRelatedProdcts($selprodId, $relatedProdIds, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->addUpdateSellerRelatedProdcts($selprodId, $relatedProdIds);
        $this->assertEquals($expected, $result);
    }

    public function setAddUpdateSellerRelatedProdcts()
    {
        return array(
            array(189, [188], true), // Attach 188 with 189
            array(185, [0], true), // skip the 0 to attach
            array(185, [185, 188, 120], true), // Attach 185, 188, 120 with 185
            array(185, 120, false), // Give Paremeter Type Error
            array('', 120, false), // False if  seplprod id is not given
        );
    }

    /**
     * @dataProvider setGetUpsellProductsData
     */
    public function testGetUpsellProducts($selprodId, $langId, $userId, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->getUpsellProducts($selprodId, $langId, $userId);
        $this->$expected($result);
    }

    public function setGetUpsellProductsData()
    {
        return array(
            array(185, 1, 4, 'assertIsArray'), //Valid Seller product id
            array(1, 1, 0, 'assertEmpty'), //Invalid Seller product id 
        );
    }

    /**
     * @dataProvider setGetAttributesByIdData
     */
    public function testGetAttributesById($selprodId, $atrribute, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->getAttributesById($selprodId, $atrribute);
        $this->$expected($result);
    }

    public function setGetAttributesByIdData()
    {
        return array(
            array(180, [], 'assertIsArray'), //Valid Seller product id
            array(0, [], 'assertEmpty'), //Invalid Seller Product Id
            array(180, 'selprod_price', 'assertIsNumeric'), //Return selprod_price
            array(180, 'selprod_title', 'Women Black Heels', 'assertFalse'), //Invalid Field Name, Give Error
        );
    }

    /**
     * @dataProvider setAddUpdateSellerProductOptionsData
     */
    public function testAddUpdateSellerProductOptions($selprodId, $data, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->addUpdateSellerProductOptions($selprodId, $data);
        $this->assertEquals($expected, $result);
    }

    public function setAddUpdateSellerProductOptionsData()
    {
        return array(
            array(180, [], true), //Vaild Seller Product Id Remove Old Options with products
            array(180, [40 => 134, 16 => 55], true), //Valid Seller product id
            array(0, [40 => 134, 16 => 55], false), //Invalid Seller Product Id
        );
    }

    /**
     * @dataProvider setGetSellerProductOptionsData
     */
    public function testGetSellerProductOptions($selprodId, $withAllJoins, $langId, $optionId, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->getSellerProductOptions($selprodId, $withAllJoins, $langId, $optionId);
        $this->$expected($result);
    }

    public function setGetSellerProductOptionsData()
    {
        return array(
            array(180, true, 1, '', 'assertIsArray'), //Vaild Seller Product Id Return Array
            array(180, true, 1, 40, 'assertIsArray'), //Vaild Seller Product Id and Option Id Return Array
            array(180, true, 1, 45, 'assertEmpty'), //Vaild Seller Product Id but invalid Option Id return empty array
            array(180, false, 0, '', 'assertIsArray'), // Vaild Seller Product Id Return Array
            array(180, true, 0, '', 'assertFalse'), // Vaild Seller Product Id but invalid lang id Give Error
            array(0, true, 1, 40, 'assertEmpty'), // valid Option Id but Invaild Seller Product Id Give Error
        );
    }

    /**
     * @dataProvider setGetSellerProductOptionsBySelProdCode
     */
    public function testGetSellerProductOptionsBySelProdCode($selprodCode, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->getSellerProductOptionsBySelProdCode($selprodCode);
        $this->$expected($result);
    }

    public function setGetSellerProductOptionsBySelProdCode()
    {
        return array(
            array("65_134", 'assertIsArray'), //Vaild SelprodCode return Array
            array("6561_24521", 'assertEmpty'), //Invalid SelprodCode return empty Array
            array("", 'assertEmpty'), //Invalid SelprodCode return empty Array
        );
    }

    /**
     * @dataProvider setGetSellerProductSpecialPrices
     */
    public function testGetSellerProductSpecialPrices($selprodId, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->getSellerProductSpecialPrices($selprodId);
        $this->$expected($result);
    }

    public function setGetSellerProductSpecialPrices()
    {
        return array(
            array(118, 'assertIsArray'), //Vaild Seller Product Return Array
            array(0, 'assertEmpty'), //Invalid Seller Product id return empty Array
        );
    }

    /**
     * @dataProvider setGetSellerProductSpecialPriceById
     */
    public function testGetSellerProductSpecialPriceById($selprodPriceId, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->getSellerProductSpecialPriceById($selprodPriceId);
        $this->$expected($result);
    }

    public function setGetSellerProductSpecialPriceById()
    {
        return array(
            [25, 'assertIsArray'], //== valid selprod price id return array
            [0, 'assertNull'], //== invalid selprod price id return false
        );
    }

    /**
     * @dataProvider setDeleteSellerProductSpecialPriceData
     */
    public function testDeleteSellerProductSpecialPrice($selpordPriceId, $selprodId, $userId, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->deleteSellerProductSpecialPrice($selpordPriceId, $selprodId, $userId);
        $this->assertEquals($expected, $result);
    }

    public function setDeleteSellerProductSpecialPriceData()
    {
        array(
            [0, 0, 3, false], // Invalid Price and Selprod id Give Error
            [1, 43, 3, false], // Invalid User Id
            [1, 44, 4, false], // Invalid Product Id
            [5, 43, 4, false], // Invalid Price Id
            [5, 42, 0, true], // Vaid Request Delete Price
            [1, 44, 4, true], // Vaild Request Delete Price
        );
    }

    /**
     * @dataProvider setAddUpdateSellerProductSpecialPriceData
     */
    public function testAddUpdateSellerProductSpecialPrice($data, $return, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->addUpdateSellerProductSpecialPrice($data, $return);
        $this->$expected($result);
    }

    public function setAddUpdateSellerProductSpecialPriceData()
    {
        $data = [
            'splprice_id' => 0, 'splprice_selprod_id' => 1, 'splprice_start_date' => date('Y-m-d H:i:s'),
            'splprice_end_date' => date('Y-m-d H:i:s', strtotime('+1 months', strtotime(date('Y-m-d H:i:s')))),
            'splprice_price' => 50
        ]; // add new price

        $data1 = [
            'splprice_id' => 1, 'splprice_selprod_id' => 44, 'splprice_start_date' => date('Y-m-d H:i:s'),
            'splprice_end_date' => date('Y-m-d H:i:s', strtotime('+2 months', strtotime(date('Y-m-d H:i:s')))),
            'splprice_price' => 50
        ]; // update existing price

        $data2 = [
            'splprice_id' => 1, 'splprice_selprod_id' => 44, 'splprice_start_date' => date('Y-m-d H:i:s'),
            'splprice_end_date' => date('Y-m-d H:i:s', strtotime('+2 months', strtotime(date('Y-m-d H:i:s')))),
            'splprice_price_amount' => 50
        ]; // Invalid Column Name so return false


        return array(
            array($data, false, 'assertTrue'), // Add New Price and return true
            array($data, true, 'assertIsNumeric'), // Add New Price and Return Price Id
            array($data1, false, 'assertTrue'), // Update Existing Price and return true
            array($data1, true, 'assertIsNumeric'), // Update Existing Price and return Price Id
            array($data2, false, 'assertFalse'), // Invaild Request Return False
        );
    }

    /**
     * @dataProvider setGetProductCommissionData
     */
    public function testGetProductCommission($selprodId, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->getProductCommission($selprodId);
        $this->$expected($result);
    }

    public function setGetProductCommissionData()
    {
        return array(
            [180, 'assertIsNumeric'], // Valid Seller Product Id Return Commission Percentage
            [0, 'assertFalse'], // Invaild Seller Product Id Give Error
            [200, 'assertFalse'], // Invaild Seller Product Id Give Error
        );
    }

    /**
     * @dataProvider setGetRelatedProducts
     */
    public function testGetRelatedProducts($langId, $sellProdId, $criteria, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->getRelatedProducts($langId, $sellProdId, $criteria);
        $this->$expected($result);
    }

    public function setGetRelatedProducts()
    {
        return array(
            array(1, 185, "selprod_id", "assertIsArray"), // Valid Request return relates products of 185 and selprod_id column only
            array(1, 185, array("selprod_id", "product_name"), "assertIsArray"), // Valid Request return relates products of 185 and selprod_id and product_name column only
            array(1, 185, '', "assertIsArray"), // valid request return related products of 185
            array(0, 185, '', "assertIsArray"), // Valid Request return relates products of 185
            array(0, 0, '', "assertIsArray"), // Valid Request return all relates products
        );
    }

    /**
     * @dataProvider setDeleteSellerProductData
     */
    public function testDeleteSellerProduct($selprodId, $expected)
    {
        $selprod = new SellerProduct();
        $result = $selprod->deleteSellerProduct($selprodId);
        $this->assertEquals($expected, $result);
    }

    public function setDeleteSellerProductData()
    {
        return array(
            [189, true], // valid request delete product
            [0, false], // invalid request return false
        );
    }

    /**
     * @dataProvider setGetProductDisplayTitleData
     */
    public function testGetProductDisplayTitle($selProdId, $langId, $toHtml = false, $expected)
    {
        $result = SellerProduct::getProductDisplayTitle($selProdId, $langId, $toHtml = false);
        $this->$expected($result);
    }

    public function setGetProductDisplayTitleData()
    {
        return array(
            [180, 1, false, "assertIsString"], // Valid Product Id return Product Name
            [180, 1, true, "assertIsString"], // Valid Product Id return Product Name
            [180, 0, true, "assertIsString"], // Invalid Lang Id Give Error
            [0, 1, false, "assertFalse"], // Invalid Product Id Return Null
            [0, 1, true, "assertFalse"], // Invalid Product Id Return Null
        );
    }

    /**
     * @dataProvider setGetVolumeDiscountsData
     */
    public function testGetVolumeDiscounts($selProdId, $expected)
    {
        $selProdObj = new SellerProduct();
        $selProdObj->setMainTableRecordId($selProdId);
        $result = $selProdObj->getVolumeDiscounts();
        $this->$expected($result);
    }

    public function setGetVolumeDiscountsData()
    {
        return array(
            [42, 'assertIsArray'], // Valid Product Id Return array
            [180, 'assertEmpty'], // Invalid Product Id return empty array
            [0, 'assertEmpty'], // Invalid Product Id return empty array
            ['', 'assertEmpty'], // Invalid Product Id return empty array
        );
    }

    /**
     * @dataProvider setRewriteUrlProductData
     */
    public function testRewriteUrlProduct($selprodId, $keyword, $expected)
    {
        $selProdObj = new SellerProduct();
        $selProdObj->setMainTableRecordId($selprodId);
        $result = $selProdObj->rewriteUrlProduct($keyword);
        $this->assertEquals($expected, $result);
    }

    public function setRewriteUrlProductData()
    {
        return array(
            [180, '', true], // Update url with prouct id
            [180, 'women heels new', true], // vaild product request
            [0, 'women heels new', false], // invalid product request
        );
    }

    /**
     * @dataProvider setRewriteUrlReviewsData
     */
    public function testRewriteUrlReviews($selprodId, $keyword, $expected)
    {
        $selProdObj = new SellerProduct();
        $selProdObj->setMainTableRecordId($selprodId);
        $result = $selProdObj->rewriteUrlReviews($keyword);
        $this->assertEquals($expected, $result);
    }

    public function setRewriteUrlReviewsData()
    {
        return array(
            [180, '', true], // Update url with prouct id
            [180, 'women heels new review link update test', true], // vaild product request
            [0, 'women heels new review link update test', false], // invalid product request
        );
    }

    /**
     * @dataProvider setRewriteUrlMoreSellersData
     */
    public function testRewriteUrlMoreSellers($selprodId, $keyword, $expected)
    {
        $selProdObj = new SellerProduct();
        $selProdObj->setMainTableRecordId($selprodId);
        $result = $selProdObj->rewriteUrlMoreSellers($keyword);
        $this->assertEquals($expected, $result);
    }

    public function setRewriteUrlMoreSellersData()
    {
        return array(
            [180, '', true], // Update url with prouct id
            [180, 'women heels new more seller link update test', true], // vaild product request
            [0, 'women heels  new more seller link update test', false], // invalid product request
        );
    }

    /**
     * @dataProvider setGetActiveCountData
     */
    public function testGetActiveCount($userId, $selprodId, $expected, $value)
    {
        $result = SellerProduct::getActiveCount($userId, $selprodId);
        $this->$expected($value, $result);
    }

    public function setGetActiveCountData()
    {
        return array(
            [4, 180, 'assertGreaterThanOrEqual', 1], // Valid User Id and seller product id give all active product count excluding given seller product id
            [1, 180, 'assertEquals', 0], // Invalid Seller Id Give count 0
            [4, 0, 'assertGreaterThanOrEqual', 1], // valid Seller User id Give all active seller product count
            [0, 0, 'assertEquals', 0], // Invalid Seller Id Give count 0
        );
    }

    /**
     * @dataProvider setGetSelProdDataByIdData
     */
    public function testGetSelProdDataById($selprodId, $langId, $expected)
    {
        $result = SellerProduct::getSelProdDataById($selprodId, $langId);
        $this->$expected($result);
    }

    public function setGetSelProdDataByIdData()
    {
        return array(
            [180, 1, 'assertIsArray'], // Valid Seller Product Id and Lang Id Return Array
            [180, 0, 'assertIsArray'], // Valid Seller Product Id and Invalid Lang Id Return Array
            [200, 1, 'assertNull'], // Invalid Seller Product Id return null
        );
    }

    /**
     * @dataProvider setSaveMetaData
     */
    public function testSaveMetaData($selprodId, $expected)
    {
        $selProdObj = new SellerProduct();
        $selProdObj->setMainTableRecordId($selprodId);
        $result = $selProdObj->saveMetaData();
        $this->assertEquals($expected, $result);
    }

    public function setSaveMetaData()
    {
        return array(
            [184, true], // valid seller product id
            [0, false], // invalid seller product id
        );
    }

    /**
     * @dataProvider setGetCatelogFromProductIdData
     */
    public function testGetCatelogFromProductId($productId, $expected)
    {
        $result = SellerProduct::getCatelogFromProductId($productId);
        $this->$expected($result);
    }

    public function setGetCatelogFromProductIdData()
    {
        return array(
            [1, 'assertIsArray'], // valid product id
            [0, 'assertEmpty'], // invalid product id
            [51, 'assertEmpty'], // No Acive Seller Product for catalog
        );
    }

    /**
     * @dataProvider setIsProductRentalData
     */
    public function testIsProductRental($selprodId, $expected)
    {
        $result = SellerProduct::isProductRental($selprodId);
        $this->assertEquals($expected, $result);
    }

    public function setIsProductRentalData()
    {
        return array(
            [170, false], // Seller Product is not for rental
            [1, true], // Seller Product is  for rental
            [0, false], // Invalid Seller Product Id
            [185, false] // Invalid Seller Product Id
        );
    }

    /**
     * @dataProvider setIsProductSaleData
     */
    public function testIsProductSale($selprodId, $expected)
    {
        $result = SellerProduct::isProductSale($selprodId);
        $this->assertEquals($expected, $result);
    }

    public function setIsProductSaleData()
    {
        return array(
            [173, false], // Seller Product is not for rental
            [184, true], // Seller Product is  for rental
            [0, false], // Invalid Seller Product Id
            [185, false] // Invalid Seller Product Id
        );
    }

}
