<?php

class CartTest extends YkModelTest
{

    /**
     * @dataProvider setAddCart
     */
    public function testAdd($userId, $selProdId, $qty, $prodgroupId, $returnUserId, $productFor, $extraData, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->add($selProdId, $qty, $prodgroupId, $returnUserId, $productFor, $extraData);
        $this->assertEquals($expected, $result);
    }

    public function setAddCart()
    {
        return array(
            array(6, 'test', 'test', 0, false, 1, [], false), // Invalid selprodid and quantity
            array(6, 'test', 1, 0, false, 1, [], false), // Invalid selprodid and valid quantity
            array(6, 14, 'test', 0, false, 1, [], false), // Invalid quantity and valid selprodid
            array(6, 115, 2, 0, false, 1, [], true), // Deleted seller product
            array(6, 13, 4, 0, false, 1, [], true), // Deleted product catalog
            array(6, 19, 1, 0, false, 1, [], true), // Deactivate seller product
            array(6, 62, 1, 0, false, 1, [], true), // Deactivate product catalog
            array(6, 109, 1, 0, false, 1, [], true), // Product out of stock
            array(6, 185, 1, 0, false, 1, [], false), // Product Is Not for Sale (Missing Check in Model Need to Modify Code)
            array(6, 14, 1, 0, false, 1, [], true), // Valid selprodid and quantity
            /* Rental Cases */
            //array(6, 14, 1, 0, false, 2, [], false), // Invalid Rental Data
            //array(6, 14, 1, 0, false, 2, ['rental_start_date' => '2021-01-05 00:00:00', 'rental_end_date' => '2020-01-06 00:00:00'], false), // Cant't Add Sale and Rent products together in cart (Missing Check in Model)
            //array(10, 14, 1, 0, false, 2, ['rental_start_date' => '2021-01-05 00:00:00', 'rental_end_date' => '2020-01-06 00:00:00'], false), // Product is Out of Stock for Rent (Missing Check in Model)
            //array(10, 118, 1, 0, false, 2, ['rental_start_date' => '2021-01-05 00:00:00', 'rental_end_date' => '2020-01-06 00:00:00'], false), // Product is Out of Stock for Rent for selected dates
            array(10, 118, 1, 0, false, 2, ['rental_start_date' => '2021-02-05 00:00:00', 'rental_end_date' => '2020-02-06 00:00:00'], true), // Valid
        );
    }

    /**
     * @dataProvider setUpdateTempStock
     */
    public function testUpdateTempStockHold($userId, $selProdId, $qty, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->updateTempStockHold($selProdId, $qty);
        $this->assertEquals($expected, $result);
    }

    public function setUpdateTempStock()
    {
        return array(
            array(24, 'test', 'test', false), // Invalid selprodid and quantity
            array(24, 'test', 1, false), // Invalid selprodid and valid quantity
            array(24, 14, 'test', false), // Invalid quantity and valid selprodid
            array(24, 14, 1, true), // Valid selprodid and quantity
        );
    }

    /**
     * @dataProvider setRemoveCart
     */
    public function testRemove($userId, $key, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->remove($key);
        $this->assertEquals($expected, $result);
    }

    public function setRemoveCart()
    {
        return array(
            array('test', 'test', false), // Invalid user id and key
            array('test', md5('czo1OiJTUF8xNCI7'), false), // Invalid user id and valid key
            array(24, 'test', false), // Invalid key and valid user id
            array(24, md5('czo1OiJTUF8xNCI7'), true), // Valid user id and key
        );
    }

    /**
     * @dataProvider setUpdateCart
     */
    public function testUpdateCart($userId, $key, $quantity, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->update($key, $quantity);
        $this->assertEquals($expected, $result);
    }

    public function setUpdateCart()
    {
        return array(
            array(6, 'test', 'test', false), // Invalid key and quantity
            array(6, 'test', 1, false), // Invalid key and valid quantity
            array(6, '901d654fbc401258caf0e68296921ecd', 2, true), // Valid key and quantity
        );
    }

    /**
     * @dataProvider providerGetSellerProductData
     */
    public function testGetSellerProductData($userId, $selProdId, $quantity, $siteLangId, $loggedUserId, $productFor, $duration, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->getSellerProductData($selProdId, $quantity, $siteLangId, $loggedUserId, $productFor, $duration);
        $this->$expected($result);
    }

    public function providerGetSellerProductData()
    {
        $duration = CommonHelper::getDifferenceBetweenDates('2021-01-05', '2021-01-06', 4);
        $totalHours = Common::hoursBetweenDates('2021-01-05', '2021-01-06');
        $totalDays = Common::daysBetweenDates('2021-01-05', '2021-01-06');
        $totalWeeks = Common::weeksBetweenDates('2021-01-05', '2021-01-06');
        $totalMonths = Common::monthsBetweenDates('2021-01-05', '2021-01-06');
        $duration['total_hours'] = $totalHours;
        $duration['total_days'] = $totalDays;
        $duration['total_weeks'] = $totalWeeks;
        $duration['total_months'] = $totalMonths;

        return array(
            array(6, 14, 1, 1, 0, 1, [], 'assertIsArray'), //Valid product id and quantity
            array(6, 118, 1, 1, 0, 2, $duration, 'assertIsArray'), //Valid Product for rent
            array(6, 170, 1, 1, 0, 2, $duration, 'assertFalse'), //Product is not  for rent
            array(6, 118, 1, 1, 0, 2, [], 'assertFalse'), //Invalid Duration data for rent
            array(6, 'test', 1, 1, 0, 1, [], 'assertFalse'), //Invalid product id with valid quantity            
            array(6, 'test', 'test', 1, 0, 1, [], 'assertFalse'), //Invalid product id and quantity            
            array(6, 115, 1, 1, 0, 1, [], 'assertFalse'), // Deleted seller product
            array(6, 13, 1, 1, 0, 1, [], 'assertFalse'), // Deleted product catalog
            array(6, 19, 1, 1, 0, 1, [], 'assertFalse'), // Deactivate seller product
            array(6, 62, 1, 1, 0, 1, [], 'assertFalse'), // Deactivate product catalog
            array(6, 109, 1, 1, 0, 1, [], 'assertFalse'), // Product out of stock                        
            array(6, 175, 1, 1, 0, 1, [], 'assertFalse'), //  Product is not for Sale      
            array(6, 14, 'test', 1, 0, 1, [], 'assertIsArray'), //Valid product id with invalid quantity
        );
    }

    /**
     * @dataProvider providerSetCartAttributes
     */
    public function testSetCartAttributes($userId, $tempUserId, $expected)
    {
        $result = Cart::setCartAttributes($userId, $tempUserId);
        $this->assertEquals($expected, $result);
    }

    public function providerSetCartAttributes()
    {
        return array(
            array(0, 0, false), // Invalid userid and tempUserId
            array(6, 'cjeh8i175mjbjitimfdheifdno', true), // Valid userid and tempUserId 
        );
    }

    /**
     * @dataProvider providerGetProducts
     */
    public function testGetProducts($userId, $siteLangId, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->getProducts($siteLangId);
        $this->assertEquals($expected, count($result));
    }

    public function providerGetProducts()
    {
        return array(
            array(0, 0, 0), //Invalid userId and langId
            array('test', 1, 0), //Invalid userId with valid langId
            array(6, 1, 2), //Valid userId and langId having product in cart
            array(10, 1, 0), // Deleted seller product of cart
            array(10, 1, 0), // Deleted product catalog of cart
            array(10, 1, 0), // Deactived seller product of cart
            array(10, 1, 0), // Deactived product catlog of cart
            array(10, 1, 0), // Made product out of stock of cart        
        );
    }

    /**
     * @dataProvider providerGetSubTotal
     */
    public function testGetSubTotal($userId, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->getSubTotal();
        $this->assertEquals($expected, $result);
    }

    public function providerGetSubTotal()
    {
        return array(
            array(6, 280), //Valid user id
            array(0, 0), //Invalid user id            
        );
    }

    /**
     * @dataProvider providerGetCartFinancialSummary
     */
    public function testGetCartFinancialSummary($userId, $siteLangId)
    {
        $cart = new Cart($userId);
        $result = $cart->getCartFinancialSummary($siteLangId);
        $this->assertIsArray($result);
    }

    public function providerGetCartFinancialSummary()
    {
        return array(
            array(6, 1), //Valid user id and lang id
            array(0, 1), //Invalid user id and valid lang id
            array(0, 0), //Invalid user id and lang id
        );
    }

    /**
     * @dataProvider providerUpdateCartDiscountCoupon
     */
    public function testUpdateCartDiscountCoupon($userId, $couponCode, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->updateCartDiscountCoupon($couponCode);
        $this->assertEquals($expected, $result);
    }

    public function providerUpdateCartDiscountCoupon()
    {
        return array(
            array(6, 'WRONG10', false), //Invalid coupon code    
            array(6, 'NEW10', true), //Valid coupon code        
        );
    }

    /**
     * @dataProvider providerRemoveCartDiscountCoupon
     */
    public function testRemoveCartDiscountCoupon($userId, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->removeCartDiscountCoupon();
        $this->assertEquals($expected, $result);
    }

    public function providerRemoveCartDiscountCoupon()
    {
        return array(
            array(6, true), //Valid user id
            array(0, false), //Invalid user id
        );
    }

    /**
     * @dataProvider providerGetCouponDiscounts
     */
    public function testGetCouponDiscounts($userId, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->getCouponDiscounts();
        $this->assertEquals($expected, $result['coupon_discount_total']);
    }

    public function providerGetCouponDiscounts()
    {
        return array(
            array(6, 50), //Cart with discount coupon code
        );
    }

    /**
     * @dataProvider providerUpdateCartUseRewardPoints
     */
    public function testUpdateCartUseRewardPoints($userId, $rewardPoints, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->updateCartUseRewardPoints($rewardPoints);
        $this->assertEquals($expected, $result);
    }

    public function providerUpdateCartUseRewardPoints()
    {
        return array(
            array(6, 200, true), //Cart with reward points
        );
    }

    /**
     * @dataProvider providerRemoveUsedRewardPoints
     */
    public function testRemoveUsedRewardPoints($userId, $expected)
    {
        $cart = new Cart($userId);
        $result = $cart->removeUsedRewardPoints();
        $this->assertEquals($expected, $result);
    }

    public function providerRemoveUsedRewardPoints()
    {
        return array(
            array(6, true), //Valid user id
        );
    }

}
