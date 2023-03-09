<div class="row justify-content-between align-items-center">   
    <div class="col-auto">
        <div class="tabs tabs--small clearfix">
            <ul>
                <li class="<?php echo ($controllerName == 'reports' && $action == 'salesReport') ? 'is-active' : ''; ?>">
                    <a href="<?php echo UrlHelper::generateUrl('reports', 'salesReport');?>">
                        <?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId); ?>
                    </a>
                </li>
                <li class="<?php echo ($controllerName == 'reports' && $action == 'rentalReport') ? 'is-active' : ''; ?>">
                    <a href="<?php echo UrlHelper::generateUrl('reports', 'rentalReport');?>">
                        <?php echo Labels::getLabel('LBL_Rental_Report', $siteLangId); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
