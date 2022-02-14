<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($collection['categories'])) {
    //echo '<pre>'; print_r($collection); echo '</pre>'; exit;
    ?>
    <section class="section collection-category" id="prod_category_lay4_<?php echo $collection['collection_id']; ?>">
        <div class="container container--fluid">
            <div class="section__title  text-center">
                <h5><?php echo $collection['collection_description']; ?></h5>
                <h2><?php echo $collection['collection_name']; ?></h2>
            </div>
            <div class="gallery_wrapper">
                <?php
                $index = 1;
                foreach ($collection['categories'] as $key => $category) {
                    if ($index > $recordLimit) {
                        break;
                    }
                    switch ($index) {
                        case 1 :
                            $gridClass = 'grid-post grid-wide';
                            $gridChildClass = 'grid-wide-media';
                            break;
                        case 2 :
                            $gridClass = 'grid-post grid-sm';
                            $gridChildClass = 'grid-sm-media';
                            break;
                        case 3 :
                            $gridClass = 'grid-post grid-height';
                            $gridChildClass = 'grid-lg-media';
                            break;
                        case 4 :
                            $gridClass = 'grid-post grid-sm-left';
                            $gridChildClass = 'grid-sm-media';
                            break;
                        default :
                            $gridClass = 'grid-post grid-wide-center';
                            $gridChildClass = 'grid-wide-media';
                            break;
                    }
                    ?>
                    <div class="<?php echo $gridClass; ?>">
                        <div class="<?php echo $gridChildClass; ?>">
                            <a href="<?php echo UrlHelper::generateFullUrl('Category', 'view', array($key)); ?>">
                                <img src="<?php echo UrlHelper::generateFullUrl('Image', 'CollectionCatTmage', array($collection['collection_id'], $index, 'ORIGINAL', 0, $siteLangId)); ?>"></a> 
                        </div>
                        <div class="grid-content">
                            <?php echo $category['prodcat_name']; ?>
                        </div>
                    </div>
                    <?php
                    $index++;
                }
                ?>
            </div>
        </div>
    </section>
<?php } ?>