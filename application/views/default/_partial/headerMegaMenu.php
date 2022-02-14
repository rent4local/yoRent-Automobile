<?php /* [ NEW NAVIGATION IMPLEMENTATION */ ?>
<?php if (FatApp::getConfig('CONF_ENABLE_ALL_CATEGORIES_NAVIGATION', FatUtility::VAR_INT, 1) && count($headerCategories)) { ?>
        <!-- [ ALL CATEGORIES NAVIGATION -->
            <div class="js--menu-trigger ft-menu__group --mega has--children --bg-none" data-classes="animate__animated animate__fadeInUp">
                <a href="javascript:void(0);" class="ft-menu__link ft-target --button"><?php echo Labels::getLabel('LBL_All_Categories', $siteLangId); ?></a>
                <div class="ft-menu__content --floated --top">
                    <nav class="ft-menu ft-nav --block --start col-12 col-xl-2">
                        <?php
                        $i = 0;
                        foreach ($headerCategories as $pcatid => $pcatval) {
                            if ($i < 8) {
                                $pCatNavUrl = UrlHelper::generateUrl('category', 'view', array($pcatval['prodcat_id']));
                                $pCatHasChild = (count($pcatval['children']) > 0) ? true : false;
                                if ($pCatHasChild) {
                                    /* $childLevel1 = array_slice($pcatval['children'], 0, 5); */
                                    $childLevel1Arr = array_chunk($pcatval['children'], 2);
                                    ?>
                                    <div class="js--menu-trigger ft-menu__group --mega has--children">
                                        <a href="<?php echo $pCatNavUrl; ?>" class="ft-menu__item ft-target --forward --button <?php echo ($i == 0) ? "--active" : ""; ?>"><?php echo $pcatval['prodcat_name']; ?></a>
                                        <div class="ft-menu__content --fixed --top --start --fill col-12 col-md-12 col-lg-10 ">
                                            <div class="container container-fluid px-md-3">
                                                <div class="row">
                                                    <?php foreach ($childLevel1Arr as $childLevel1) { ?>
                                                        <div class="col col-12 col-md-6 col-lg-4">
                                                            <?php
                                                            foreach ($childLevel1 as $catkey => $catval) {
                                                                $catNavUrl = UrlHelper::generateUrl('category', 'view', array($catval['prodcat_id']));
                                                                $catHasChild = (count($catval['children']) > 0) ? true : false;
                                                                ?>
                                                                <div class="js--accordion-trigger ft-accordion --desktop">
                                                                    <a href="<?php echo $catNavUrl; ?>" class="ft-accordion__item ft-target --button "><?php echo $catval['prodcat_name']; ?></a>
                                                                    <?php if ($catHasChild) { ?>
                                                                        <div class="ft-accordion__content">
                                                                            <nav class="ft-nav  --block">
                                                                                <?php
                                                                                foreach ($catval['children'] as $ccatkey => $ccatval) {
                                                                                    $ccatNavUrl = UrlHelper::generateUrl('category', 'view', array($ccatval['prodcat_id']));
                                                                                    $ccatHasChild = (count($ccatval['children']) > 0) ? true : false;
                                                                                    ?>
                                                                                    <a href="<?php echo $ccatNavUrl; ?>" class="ft-target --link"><?php echo $ccatval['prodcat_name']; ?></a>
                                                                                <?php } ?>    
                                                                            </nav>
                                                                        </div>
                                                                    <?php } ?>

                                                                </div>
                                                            <?php } ?> 
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <a href="<?php echo $pCatNavUrl; ?>" class="ft-menu__item ft-target --button <?php echo ($i == 0) ? "--active" : ""; ?>"><?php echo $pcatval['prodcat_name']; ?></a>
                                    <?php
                                }
                                $i++;
                            } else {
                                ?>
                                <a href="<?php echo UrlHelper::generateUrl('category'); ?>" class="ft-menu__item ft-target --button"><?php echo Labels::getLabel('LBL_All_Categories', $siteLangId); ?></a>
                                <?php
                                break;
                            }
                        }
                        ?>
                    </nav>
                </div>
            </div>
        <!-- ] -->
<?php } ?>
<?php /* ] */ ?>