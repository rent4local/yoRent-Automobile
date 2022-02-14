<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php
if (count($headerNavigation) > 0) { ?>
    <?php 
    $getOrgUrl = (CONF_DEVELOPMENT_MODE) ? true : false;
    $charLimitArr = applicationConstants::getNavigationCharLimit();
    $activeTheme = applicationConstants::getActiveTheme();
    $noOfCharAllowedInNav = 80;
    $rightNavCharCount = 5;
    if (isset($charLimitArr[$activeTheme]) && !empty($charLimitArr[$activeTheme])) {
        $charLimitArr = $charLimitArr[$activeTheme];
        $noOfCharAllowedInNav = $charLimitArr['main_nav'];
        $rightNavCharCount = $charLimitArr['other_nav'];
    }
    if (!$isUserLogged) {
        $rightNavCharCount = $rightNavCharCount + mb_strlen(html_entity_decode(Labels::getLabel('LBL_Sign_In', $siteLangId), ENT_QUOTES, 'UTF-8'));
    } else {
        $rightNavCharCount = $rightNavCharCount + mb_strlen(html_entity_decode(Labels::getLabel('LBL_Hi,', $siteLangId) . ' ' . $userName, ENT_QUOTES, 'UTF-8'));
    }
    $rightNavCharCount = $rightNavCharCount + mb_strlen(html_entity_decode(Labels::getLabel("LBL_Cart", $siteLangId), ENT_QUOTES, 'UTF-8'));
    $noOfCharAllowedInNav = $noOfCharAllowedInNav - $rightNavCharCount;
    $navLinkCount = 0;

    foreach ($headerNavigation as $nav) {
        if (!$nav['pages']) {
            break;
        }
        foreach ($nav['pages'] as $link) {
            $noOfCharAllowedInNav = $noOfCharAllowedInNav - mb_strlen(html_entity_decode($link['nlink_caption'], ENT_QUOTES, 'UTF-8'));
            if ($noOfCharAllowedInNav < 0) {
                break;
            }
            $navLinkCount++;
        }
    }

    foreach ($headerNavigation as $nav) {
        if ($nav['pages']) {
            $mainNavigation = array_slice($nav['pages'], 0, $navLinkCount);
            foreach ($mainNavigation as $link) {
                $navUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id']);
                $OrgnavUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id'], $getOrgUrl);
                $href = (isset($link['children']) && count($link['children']) > 0) ? "javascript:void(0);" : $navUrl;
                ?>
                <div class="js--menu-trigger ft-menu__group --bg <?php echo ($link['nlink_type'] == NavigationLinks::NAVLINK_TYPE_CMS || $link['nlink_type'] == NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE) ? "--default" : "--mega" ?>  <?php echo (isset($link['children']) && count($link['children']) > 0) ? "has--children" : ""; ?>">
                    <a href="<?php echo $href; ?>" class="ft-menu__item ft-target --button"><?php echo $link['nlink_caption']; ?></a>
                    <?php
                    if (isset($link['children']) && count($link['children']) > 0) {
                        if ($link['nlink_type'] == NavigationLinks::NAVLINK_TYPE_CMS || $link['nlink_type'] == NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE) {
                            ?>
                            <div class="ft-menu__content --top" style="top: 100%;">
                                <nav class="ft-menu ft-nav --block --start">
                                    <?php
                                    foreach ($link['children'] as $children) {
                                        $subCatUrl = CommonHelper::getnavigationUrl($children['nlink_type'], $children['nlink_url'], $children['nlink_cpage_id'], $children['nlink_category_id']);
                                        $subCatOrgUrl = CommonHelper::getnavigationUrl($children['nlink_type'], $children['nlink_url'], $children['nlink_cpage_id'], $children['nlink_category_id'], $getOrgUrl);
                                        ?>
                                        <a href="<?php echo $subCatUrl; ?>" data-org-url="<?php echo $subCatOrgUrl; ?>" class="ft-menu__item ft-target --button"><?php echo $children['nlink_caption']; ?></a>
                                    <?php } ?>
                                </nav>
                            </div> 
                        <?php } else { ?>
                            <div class="ft-menu__content --floated --top">
                                <div class="px-md-3">
                                    <div class="row">
                                        <?php
                                        foreach ($link['children'] as $children) {
                                            $subCatUrl = '';
                                            if (isset($children['prodcat_id']) && $children['prodcat_id'] > 0) {
                                                $subCatUrl = UrlHelper::generateUrl('category', 'view', array($children['prodcat_id']));
                                                $subCatOrgUrl = UrlHelper::generateUrl('category', 'view', array($children['prodcat_id']), '', null, false, $getOrgUrl);
                                            } elseif (isset($children['nlink_type'])) {
                                                $subCatUrl = CommonHelper::getnavigationUrl($children['nlink_type'], $children['nlink_url'], $children['nlink_cpage_id'], $children['nlink_category_id']);
                                                $subCatOrgUrl = CommonHelper::getnavigationUrl($children['nlink_type'], $children['nlink_url'], $children['nlink_cpage_id'], $children['nlink_category_id'], $getOrgUrl);
                                            }
                                            ?>
                                            <div class="col col-12 col-md-12 col-lg-3">
                                                <div class="js--accordion-trigger ft-accordion --desktop">
                                                    <a href="<?php echo $subCatUrl; ?>" data-org-url="<?php echo $subCatOrgUrl; ?>" class="ft-accordion__item ft-target --button "><?php echo (isset($children['prodcat_id']) && $children['prodcat_id'] > 0) ? $children['prodcat_name'] : $children['nlink_caption']; ?></a>
                                                    <?php if (isset($children['children']) && count($children['children']) > 0) { ?>
                                                        <div class="ft-accordion__content">
                                                            <nav class="ft-nav --block">
                                                                <?php
                                                                foreach ($children['children'] as $childCat) {
                                                                    $catUrl = '';
                                                                    if (isset($childCat['prodcat_id']) && $childCat['prodcat_id'] > 0) {
                                                                        $catUrl = UrlHelper::generateUrl('category', 'view', array($childCat['prodcat_id']));
                                                                        $catOrgUrl = UrlHelper::generateUrl('category', 'view', array($childCat['prodcat_id']), '', null, false, $getOrgUrl);
                                                                    } elseif (isset($childCat['nlink_type'])) {
                                                                        $catUrl = CommonHelper::getnavigationUrl($childCat['nlink_type'], $childCat['nlink_url'], $childCat['nlink_cpage_id'], $childCat['nlink_category_id']);
                                                                        $catOrgUrl = CommonHelper::getnavigationUrl($childCat['nlink_type'], $childCat['nlink_url'], $childCat['nlink_cpage_id'], $childCat['nlink_category_id'], $getOrgUrl);
                                                                    }
                                                                    if (trim($catUrl) != '') {
                                                                        ?>
                                                                        <a href="<?php echo $catUrl; ?>"  data-org-url="<?php echo $catOrgUrl; ?>" class="ft-target --link"><?php echo (isset($childCat['prodcat_id']) && $childCat['prodcat_id'] > 0) ? $childCat['prodcat_name'] : $childCat['nlink_caption']; ?></a>

                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </nav>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <?php } ?>    
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            }
        }
    } ?>

    
<?php 
    }
?>