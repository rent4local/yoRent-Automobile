<ul class="list-collections">
    <?php foreach ($categoriesArr as $category) { ?>
        <li class="list-collections__item">
            <a href="<?php echo UrlHelper::generateUrl('category', 'view', array($category['prodcat_id'])); ?>">
                <div class="aspect-ratio" style="padding-bottom: 45%">
                    <div class="list-collections__image" style="background-image: url(<?php echo UrlHelper::generateFullFileUrl('Category', 'banner', array($category['prodcat_id'], $siteLangId)); ?>);">
                    </div>
                </div>
                <h6 class="list-collections__heading"><?php echo $category['prodcat_name']; ?></h6>
            </a>
        </li>
    <?php } ?>
</ul>
<?php
$searchFunction = 'goToCategorySearchPage';
$postedData['page'] = (isset($page)) ? $page : 1;
$postedData['recordCount'] = $recordCount;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchCategories', 'id' => 'frmSearchCategories'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $postedData['page'], 'recordCount' => $recordCount, 'callBackJsFunc' => $searchFunction, 'siteLangId' => $siteLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>    
