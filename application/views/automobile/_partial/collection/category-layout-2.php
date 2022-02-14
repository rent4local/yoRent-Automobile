<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (isset($collection['categories']) && count($collection['categories'])) { ?>
<section class="section section--category">
    <div class="container">
        <div class="d-flex justify-content-between mb-7">
            <div class="section__title">
                <?php echo ($collection['collection_description'] != '') ? ' <h5>' . $collection['collection_description'] .'</h5>' : ''; ?>
                <?php echo ($collection['collection_name'] != '') ? ' <h2>' . $collection['collection_name'] .'</h2>' : ''; ?>
            </div>
            
            <div class="link--more"> <a href="<?php echo UrlHelper::generateUrl('Collections', 'View', array($collection['collection_id']));?>" class="arrow--right"><?php echo Labels::getLabel('LBL_Discover_more', $siteLangId); ?></a>
            </div>
            
        </div>
        <div class="row">
            <?php foreach ($collection['categories'] as $category) { ?>
            <div class="col-lg-3 col-sm-6">
                <div class="category">
                    <div class="category__head">
                        <div class="category-media">
                            <a href="<?php echo UrlHelper::generateUrl('Category', 'View', array($category['prodcat_id'] )); ?>">
                            <img loading='lazy' data-ratio="4:1"
                                src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Category', 'icon', array($category['prodcat_id'] , $siteLangId, 'HOME', applicationConstants::SCREEN_DESKTOP)), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $category['prodcat_name']; ?>"
                                title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $category['prodcat_name']; ?>">
                            </a>
                        </div>
                    </div>
                    <?php $i=0; 
                    foreach ($category['subCategories'] as $subCat) { ?>
                        <?php $i++;  
                    } ?>
                    <div class="category__body">
                        <a href="<?php echo UrlHelper::generateUrl('Category', 'View', array($category['prodcat_id'] )); ?>">
                            <h4><?php echo $category['prodcat_name']; ?></h4>
                        </a>
                        <p><?php echo $i; ?> <?php echo Labels::getLabel('LBL_Tools_&_Equipment', $siteLangId); ?></p>
                    </div>  
                </div>              
            </div>
            <?php }?>
        </div>
    </div>
</section>
<?php } ?>
