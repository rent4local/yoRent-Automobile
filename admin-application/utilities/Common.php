<?php
class Common
{
    static function setHeaderBreadCrumb($template)
    {
        $controllerName = FatApp::getController();
        $action = FatApp::getAction();

        $controller = new $controllerName('');
        $template->set('nodes', $controller->getBreadcrumbNodes($action));
        $template->set('adminLangId', CommonHelper::getlangId());
    }
	
	public static function daysBetweenDates($startDate, $endDate)
    {
        $days = abs((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24)) + 1;
        return $days > 0 ? ceil($days) : 1;
    }

    public static function hoursBetweenDates($startDate, $endDate)
    {
        $hours = abs((strtotime($endDate) - strtotime($startDate)) / (60 * 60));
        return $hours > 0 ? ceil($hours) : 1;
    }

    public static function weeksBetweenDates($startDate, $endDate)
    {
        $weeks = abs((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24 * 7));
        return $weeks > 0 ? ceil($weeks) : 1;
    }

    public static function monthsBetweenDates($startDate, $endDate)
    {
        /* $months = abs((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24 * 30));
          return $months > 0 ? ceil($months) : 1; */
        $date1 = new DateTime($startDate);
        $date2 = new DateTime($endDate);

        $diff = $date1->diff($date2);
        $years = $diff->y;
        $months = $diff->m + ($years * 12);
        $totalDays = $diff->d;
        $hours = $diff->h;
        $minutes = $diff->i;
        if ($totalDays > 0 || $hours > 0 || $minutes > 0) {
            $months ++;
        }
        return $months;
    }
}
?>
