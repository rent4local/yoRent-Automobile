<?php

class Statistics
{

    public static function sellerSalesGraph()
    {
		$sales_earnings_chart_data = array();
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

		if ($loggedUserId > 0) {
			$dashboardStats = Stats::getUserSales($loggedUserId, STATS::SELLER_DASHBOARD_SALES_MONTH);
			foreach ($dashboardStats as $saleskey => $salesval) {
				$sales_earnings_chart_data[$saleskey] = round($salesval, 2);
			}
		}
		
        $dashboardInfo['sales_earnings_chart_data'] = $sales_earnings_chart_data;
        if ('ltr' == mb_strtolower(CommonHelper::getLayoutDirection())) {
            $dashboardInfo['sales_earnings_chart_data'] = array_reverse($sales_earnings_chart_data);
        }
		
        return $dashboardInfo;
    }

    public static function sellerRentalGraph()
    {
		$rentalEarningData = array();
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

		if ($loggedUserId > 0) {
			$dashboardStats = Stats::getUserRental($loggedUserId, STATS::SELLER_DASHBOARD_SALES_MONTH);
			foreach ($dashboardStats as $key => $val) {
				$rentalEarningData[$key] = round($val, 2);
			}
		}

		$dashboardInfo['rental_earnings_chart_data'] = $rentalEarningData;
        if ('ltr' == mb_strtolower(CommonHelper::getLayoutDirection())) {
            $dashboardInfo['rental_earnings_chart_data'] = array_reverse($rentalEarningData);
        }
		
		return $dashboardInfo;
    }

}
