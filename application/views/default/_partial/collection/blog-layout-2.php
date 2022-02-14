<?php if (isset($collection['blogs']) && count($collection['blogs']) > 0) { ?>
    <section class="section collection-blog" id="blogs_layout2_<?php echo $collection['collection_id']; ?>">
        <div class="container">
            <div class="section__title  text-center">
                <h5><?php echo $collection['collection_description']; ?></h5>
                <h2><?php echo $collection['collection_name']; ?></h2>
            </div>
        </div>
        <div class="container container--narrow">
            <div class="row">
                <?php foreach ($collection['blogs'] as $blog) { ?>
                    <?php
                    $title = !empty($blog['post_title']) ? $blog['post_title'] : $blog['post_identifier'];
                    $title = mb_strimwidth($title, 0, applicationConstants::BLOG_TITLE_CHARACTER_LENGTH, '...');

                    $shortDesc = !empty($blog['post_short_description']) ? $blog['post_short_description'] : "";
                    $shortDesc = mb_strimwidth($shortDesc, 0, applicationConstants::BLOG_DESCRIPTION_CHARACTER_LENGTH, '...');
                    ?>

                    <div class="col-md-4 col-custom-sm">
                        <div class="blog">
                            <div class="blog__card">
                                <div class="blog__card--body">
                                    <span class="date-vertical">
                                        <?php echo date('F d, Y', strtotime($blog['post_updated_on'])); ?>
                                    </span>
                                    <div class="blog-media">
                                        <a href="<?php echo UrlHelper::generateUrl('Blog', 'postDetail', array($blog['post_id'])); ?>">
                                            <picture class="product-img" data-ratio="3:4">
                                            <source type="image/webp" srcset="<?php echo UrlHelper::generateFullUrl('Image', 'blogPostFront', array($blog['post_id'], $siteLangId, 'COLLECTION', 0, 0, true)); ?>">
                                            <img data-aspect-ratio="3:4" alt="<?php echo $blog['post_title'];?>" src="<?php echo UrlHelper::generateFullUrl('Image', 'blogPostFront', array($blog['post_id'], $siteLangId, 'COLLECTION', 0, 0, true)); ?>">
                                            </picture>
                                        </a>
                                    </div>
                                </div>
                                <div class="blog__card--footer">
                                    
                                        <h3 class="blog-title"><a href="<?php echo UrlHelper::generateUrl('Blog', 'postDetail', array($blog['post_id'])); ?>">
                                        <?php echo $title; ?></a></h3>
                                    
                                    <p class="blog-detail"><?php echo $shortDesc; ?></p>
                                    <a href="<?php echo UrlHelper::generateUrl('Blog', 'postDetail', array($blog['post_id'])); ?>" class="link-more mt-3"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
<?php } ?>