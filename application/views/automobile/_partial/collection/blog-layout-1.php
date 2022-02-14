<?php if (isset($collection['blogs']) && count($collection['blogs']) > 0) { ?>
    <section class="section collection--blog">
        <div class="container">
            <div class="section__heading">
                <?php echo ($collection['collection_name'] != '') ? ' <h2>' . $collection['collection_name'] . '</h2>' : ''; ?>
                <?php echo ($collection['collection_description'] != '') ? ' <h5>' . $collection['collection_description'] . '</h5>' : ''; ?>
            </div>
            <div class="d-grid" data-view="4">
                <?php foreach ($collection['blogs'] as $blog) { ?>
                    <div class="blog-item">
                        <div class="blog">
                            <div class="blog__media">
                                <a href="<?php echo UrlHelper::generateUrl('Blog', 'postDetail', array($blog['post_id'])); ?>" class="animate-scale">
                                    <img data-aspect-ratio="4:3" src="<?php echo UrlHelper::generateFullUrl('Image', 'blogPostFront', array($blog['post_id'], $siteLangId, 'AUTOFEATURED', 0, 0, true)); ?>" alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $blog['post_title']; ?>" title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $blog['post_title']; ?>" /></a>
                            </div>
                            <div class="blog__content">
                                <div class="blog-detail">
                                    <div class="blog-date"><?php echo date('jS F Y', strtotime($blog['post_updated_on'])); ?><span class="slash">|</span> <?php echo $blog['post_author_name']; ?></div>
                                    <a href="<?php echo UrlHelper::generateUrl('Blog', 'postDetail', array($blog['post_id'])); ?>" class="blog-title">
                                        <?php
                                        $title = !empty($blog['post_title']) ? $blog['post_title'] : $blog['post_identifier'];
                                        echo mb_strimwidth($title, 0, applicationConstants::BLOG_TITLE_CHARACTER_LENGTH, '...');
                                        ?></a>
                                    <p><?php echo FatUtility::decodeHtmlEntities($blog['post_short_description']); ?></p>
                                </div>
                                <a href="<?php echo UrlHelper::generateUrl('Blog', 'postDetail', array($blog['post_id'])); ?>" class="action">
                                    <span class="arrow-right"><?php echo Labels::getLabel('LBL_Continue_Reading', $siteLangId); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="flex-center mt-btn">
                <a class="btn btn-outline-brand btn-round arrow-right" href="<?php echo UrlHelper::generateUrl('Blog'); ?>"><?php echo Labels::getLabel('LBL_VIEW_ALL', $siteLangId); ?>
                </a>
            </div>
        </div>
    </section>
<?php } ?>