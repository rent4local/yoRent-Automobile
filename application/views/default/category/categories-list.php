<div class="cg-main">
    <?php
    $catCount = 1;
    foreach ($categoriesArr as $category) {
		$totalProduct = (isset($category['totalProducts'])) ? $category['totalProducts'] : 0;
		if((!empty($category['children']))) {
			$childArr = $category['children'];
			$childProductColArr = array_column($childArr, 'totalProducts');
			$totalProduct = array_sum($childProductColArr);
		}
		?>
        <div class="item anchor--js--link-<?php echo $catCount; ?>" >
            <h6 class="big-title"> 
                <i class="cg-icon">
                    <img src="<?php echo UrlHelper::generateUrl('category', 'icon', array($category['prodcat_id'], '1', 'collection_page')); ?>"> 
                </i> 

                <a href="<?php echo UrlHelper::generateUrl('category', 'view', array($category['prodcat_id'])); ?>"><?php echo $category['prodcat_name']; ?></a> 
            </h6>
            <?php if (!empty($category['children'])) { ?>
                <div class="cell__body">
                    <ul class="sub-catagory-pro">
                        <?php foreach ($category['children'] as $subcat) { ?>
                            <?php /* <li><a href="<?php echo UrlHelper::generateUrl('category', 'view', array($subcat['prodcat_id'])); ?>"> <?php echo $subcat['prodcat_name'] ?></a></li> */ ?>
                            <h6 class="big-title mb-2 mt-2"> 
                                <a href="<?php echo UrlHelper::generateUrl('category', 'view', array($subcat['prodcat_id'])); ?>"><?php echo $subcat['prodcat_name']; ?></a> 
                            </h6>
                            <!-- [ 3RD LEVEL CATEGORIES LISTING -->
                            <?php if (!empty($subcat['children'])) { ?>
                                <div class="cell__body">
                                    <ul>
                                        <?php foreach ($subcat['children'] as $subChildCat) { ?>
                                            <li><a href="<?php echo UrlHelper::generateUrl('category', 'view', array($subChildCat['prodcat_id'])); ?>"> <?php echo $subChildCat['prodcat_name'] ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                            <!-- ]-->
                        <?php } ?>
                    </ul>
                </div>
            <?php }
            ?>
        </div>
        <?php
        $catCount++;
    }
    ?>
</div>