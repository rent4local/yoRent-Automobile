<?php

class ProductRentalTest extends YkModelTest
{

    /**
     * @dataProvider setAddUpdateSelProData
     */
    public function testAddUpdateSelProData($data, $expected)
    {
        $prodRentObj = new ProductRental();
        $result = $prodRentObj->addUpdateSelProData($data);
        $this->assertEquals($expected, $result);
    }

    public function setAddUpdateSelProData()
    {
        $data = array(
            'sprodata_selprod_id' => 185, 'sprodata_is_for_sell' => 1, 'sprodata_is_for_rent' => 1,
            'sprodata_daily_price' => 10, 'sprodata_hourly_price' => 5, 'sprodata_weekly_price' => 15,
            'sprodata_monthly_price' => 20, 'sprodata_rental_security' => 100, 'sprodata_rental_stock' => 100,
            'sprodata_rental_buffer_days' => 1, 'sprodata_minimum_rental_duration' => 1, 'sprodata_duration_type' => 1,
            'sprodata_is_rental_data_updated' => date('Y-m-d H:i:s')
        ); // Add New Row for Seller Product 185

        $data1 = array(
            'sprodata_selprod_id' => 185, 'sprodata_is_for_sell' => 0, 'sprodata_is_for_rent' => 1,
            'sprodata_daily_price' => 10, 'sprodata_hourly_price' => 5, 'sprodata_weekly_price' => 15,
            'sprodata_monthly_price' => 20, 'sprodata_rental_security' => 100, 'sprodata_rental_stock' => 100,
            'sprodata_rental_buffer_days' => 1, 'sprodata_minimum_rental_duration' => 1, 'sprodata_duration_type' => 1,
            'sprodata_is_rental_data_updated' => date('Y-m-d H:i:s')
        ); // Update Existing Row

        $data2 = array(
            'sprodata_selprod_id' => 185, 'sprodata_is_for_sell_product' => 0, 'sprodata_is_for_rent' => 1,
            'sprodata_daily_price' => 10, 'sprodata_hourly_price' => 5, 'sprodata_weekly_price' => 15,
            'sprodata_monthly_price' => 20, 'sprodata_rental_security' => 100, 'sprodata_rental_stock' => 100,
            'sprodata_rental_buffer_days' => 1, 'sprodata_minimum_rental_duration' => 1, 'sprodata_duration_type' => 1,
            'sprodata_is_rental_data_updated' => date('Y-m-d H:i:s')
        ); //  Invalid Column Name sprodata_is_for_sell_product

        return array(
            array($data, true), // Valid
            array($data1, true), // Valid
            array($data2, false), // Invalid
        );
    }

    /**
     * @dataProvider setGetProductRentalData
     */
    public function testGetProductRentalData($selprodId, $expected)
    {
        $prodRentObj = new ProductRental();
        $prodRentObj->setMainTableRecordId($selprodId);
        $result = $prodRentObj->getProductRentalData();
        $this->$expected($result);
    }

    public function setGetProductRentalData()
    {
        return array(
            [184, 'assertIsArray'], // valid seller product id
            [200, 'assertEmpty'], // Invalid Seller Product Id
            [0, 'assertEmpty'], // Invalid Seller Product Id
            ['', 'assertEmpty'], // Invalid Seller Product Id
        );
    }

    /**
     * @dataProvider setGetRentalProductQuantityData
     */
    public function testGetRentalProductQuantity($selprodId, $startDate, $endDate, $prodBufferDays, $extendOpId, $expected, $expectedVal)
    {
        $prodRentObj = new ProductRental();
        $prodRentObj->setMainTableRecordId($selprodId);
        $result = $prodRentObj->getRentalProductQuantity($startDate, $endDate, $prodBufferDays, $extendOpId);
        $this->$expected($expectedVal, $result);
    }

    public function setGetRentalProductQuantityData()
    {
        return array(
            [118, '2021-01-05', '2021-01-10', 1, 0, 'assertGreaterThanOrEqual', 0], // valid reuest 
            [118, '2021-01-05', '2021-01-10', 0, 111, 'assertGreaterThanOrEqual', 0], // valid request
            [118, '2021-02-01', '2021-02-10', 1, 0, 'assertEquals', 0], // valid request
            [0, '2021-02-01', '2021-02-10', 1, 0, 'assertEquals', 0], // invalid request
            ['', '2021-02-01', '2021-02-10', 1, 0, 'assertEquals', 0], // invalid request
        );
    }

    /**
     * @dataProvider setGetDurationDiscountsData
     */
    public function testGetDurationDiscounts($selprodId, $expected)
    {
        $prodRentObj = new ProductRental();
        $prodRentObj->setMainTableRecordId($selprodId);
        $result = $prodRentObj->getDurationDiscounts();
        $this->$expected($result);
    }

    public function setGetDurationDiscountsData()
    {
        return array(
            [184, 'assertIsArray'], // return array of discounts
            [118, 'assertEmpty'], // return empty array because no discount avaialble for 118
            [0, 'assertEmpty'], // retun empty array as seller product id is invalid
        );
    }

    /**
     * @dataProvider setRentalTempHoldStockCountData
     */
    public function testRentalTempHoldStockCount($selprodId, $userId, $rentalStartDate, $rentalEndDate, $expected, $expectedVal)
    {
        $result = ProductRental::rentalTempHoldStockCount($selprodId, $userId, $rentalStartDate, $rentalEndDate);
        $this->$expected($expectedVal, $result);
    }

    public function setRentalTempHoldStockCountData()
    {
        return array(
            [118, 21, '2021-01-05 00:00:00', '2021-01-06 00:00:00', 'assertGreaterThanOrEqual', 1], // valid
            [118, 0, '2021-01-05 00:00:00', '2021-01-06 00:00:00', 'assertGreaterThanOrEqual', 1], // valid
            [118, 5, '2021-01-05 00:00:00', '2021-01-06 00:00:00', 'assertEquals', 0], // no record for user id 5
            [1, 0, '2021-01-05 00:00:00', '2021-01-06 00:00:00', 'assertEquals', 0], // no record for product id 1
        );
    }

    /**
     * @dataProvider setUpdateRentalProductStockData
     */
    public function testUpdateRentalProductStock($selprodId, $quantity, $startDate, $endDate, $decrement, $expected)
    {
        $prodRentObj = new ProductRental();
        $prodRentObj->setMainTableRecordId($selprodId);
        $result = $prodRentObj->updateRentalProductStock($quantity, $startDate, $endDate, $decrement);
        $this->assertEquals($expected, $result);
    }

    public function setUpdateRentalProductStockData()
    {
        return array(
            [118, 4, date('Y-m-d'), date('Y-m-d', strtotime('+2 days')), false, true], // valid
            [118, 1, date('Y-m-d'), date('Y-m-d', strtotime('+2 days')), true, true], // valid
            [118, 4, date('Y-m-d'), date('Y-m-d', strtotime('+2 days')), false, true], // valid
            [0, 4, date('Y-m-d'), date('Y-m-d', strtotime('+2 days')), false, true], // valid
                //[0, 4, '', date('Y-m-d', strtotime('+2 days')), false, false], Need to rectify code
                //[0, 4, date('Y-m-d'), '', false, false], // need to rectify code
        );
    }

    /**
     * @dataProvider setGetRentalTempHoldByCartKeyData
     */
    public function testGetRentalTempHoldByCartKey($selprodId, $userId, $cartKey, $expected, $expectedVal)
    {
        $prodRentObj = new ProductRental();
        $prodRentObj->setMainTableRecordId($selprodId);
        $result = $prodRentObj->getRentalProductQuantity($userId, $cartKey);
        $this->$expected($expectedVal, $result);
    }

    public function setGetRentalTempHoldByCartKeyData()
    {
        return array(
            [118, 21, 'IlNQXzExODIwMjEtMDEtMDUgMDA6MDAyMDIxLTAxLTA2IDAwOjAwIg==', 'assertGreaterThanOrEqual', 1], // valid
            [118, 21, 'DFSGFGDGFDJBHV12H==', 'assertEquals', 0], // Invalid Cart Key
            [118, 0, 'IlNQXzExODIwMjEtMDEtMDUgMDA6MDAyMDIxLTAxLTA2IDAwOjAwIg==', 'assertEquals', 0], // Invaild User Id
            [180, 21, 'IlNQXzExODIwMjEtMDEtMDUgMDA6MDAyMDIxLTAxLTA2IDAwOjAwIg==', 'assertEquals', 0], // Invaid Product Id
            [180, 'HGVGFXGF', 30, 'assertEquals', 0], // Invalid Argument  
        );
    }

    /**
     * @dataProvider setGetLangsData
     */
    public function testGetLangsData($selprodId, $expected)
    {
        $result = ProductRental::getLangsData($selprodId);
        $this->$expected($result);
    }

    public function setGetLangsData()
    {
        return array(
            ['hddg', 'assertFalse'], // Invalid Perameter
            [118, 'assertIsArray'], // Valid
            [200, 'assertEmpty'], // Invalid Product
        );
    }

}
