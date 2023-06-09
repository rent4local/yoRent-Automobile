<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
/* $this->includeTemplate('_partial/blogTopFeaturedCategories.php'); */ ?>
<section class="section <?php echo (false === CommonHelper::isAppUser()) ? 'post-detail' : ''; ?>">
    <div class="container">
        <div class="row">
            <div class="col-xl-9 col-lg-8 mb-4 mb-md-0">
                <div class="posted-content">
                    <?php if (false === CommonHelper::isAppUser()) { ?>
                        <div class="posted-media">
                            <?php if (!empty($post_images)) { ?>
                                <div class="post__pic">
                                    <?php foreach ($post_images as $post_image) { ?>
                                        <div class="item">
                                            <div class="media-wrapper"><img data-ratio="16:9" src="<?php echo FatUtility::generateUrl('image', 'blogPostFront', array($post_image['afile_record_id'], $post_image['afile_lang_id'], "LAYOUT1", 0, $post_image['afile_id']), CONF_WEBROOT_FRONT_URL); ?>" alt="<?php echo $post_image['afile_attribute_alt']; ?>" title="<?php echo $post_image['afile_attribute_title']; ?>"></div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <div class="post-data">
                        <?php if (false === CommonHelper::isAppUser()) { ?>
                            <div class="post-meta-detail">
                                <div class="post--title"><?php echo $blogPostData['post_title']; ?></div>
                                <div class="posted-by">
                                    <span class="auther"><?php echo Labels::getLabel('Lbl_By', $siteLangId); ?> <?php echo $blogPostData['post_author_name']; ?></span>
                                    <span class="time"><?php echo FatDate::format($blogPostData['post_added_on']); ?></span><span class="time"><?php $categoryIds = !empty($blogPostData['categoryIds']) ? explode(',', $blogPostData['categoryIds']) : array();
                                                                                                                                                $categoryNames = !empty($blogPostData['categoryNames']) ? explode('~', $blogPostData['categoryNames']) : array();
                                                                                                                                                $categories = array_combine($categoryIds, $categoryNames); ?>
                                        <?php if (!empty($categories)) {
                                            echo Labels::getLabel('Lbl_in', $siteLangId);
                                            foreach ($categories as $id => $name) {
                                                if ($name == end($categories)) { ?>
                                                    <a href="<?php echo UrlHelper::generateUrl('Blog', 'category', array($id)); ?>" class="text--dark"><?php echo $name; ?></a>
                                                <?php break;
                                                } ?>
                                                <a href="<?php echo UrlHelper::generateUrl('Blog', 'category', array($id)); ?>" class="text--dark"><?php echo $name; ?></a>,
                                        <?php }
                                        } ?></span>


                                    <!-- <div class="dropdown">
                                        <a class="dropdown-toggle no-after share-icon" href="javascript:void(0)" data-toggle="dropdown"><i class="icn">
                                                <svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share"></use>
                                                </svg>
                                            </i></a>
                                        <div class="dropdown-menu dropdown-menu-anim">
                                            <ul class="social-sharing">
                                                <li class="social-facebook">
                                                    <a class="social-link st-custom-button" data-network="facebook" data-url="<?php echo UrlHelper::generateFullUrl('Blog', 'postDetail', array($blogPostData['post_id'])); ?>/">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"></use>
                                                            </svg></i>
                                                    </a>
                                                </li>
                                                <li class="social-twitter">
                                                    <a class="social-link st-custom-button" data-network="twitter">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"></use>
                                                            </svg></i>
                                                    </a>
                                                </li>
                                                <li class="social-pintrest">
                                                    <a class="social-link st-custom-button" data-network="pinterest">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"></use>
                                                            </svg></i>
                                                    </a>
                                                </li>
                                                <li class="social-email">
                                                    <a class="social-link st-custom-button" data-network="email">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"></use>
                                                            </svg></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div> -->

                                    <!-- Button trigger modal -->

                                    <a href="javascript:void(0);" class="no-after share-icon" data-toggle="modal" data-target="#shareModal">
                                        <i class="icn icn-share">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share">
                                                </use>
                                            </svg>
                                        </i>
                                    </a>

                                    <!-- Modal -->
                                    <div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModal" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLongTitle">
                                                        <?php echo Labels::getLabel('LBL_Share', $siteLangId); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul class="social-sharing">
                                                        <li class="social-facebook">
                                                            <a class="st-custom-button" data-network="facebook" data-url="<?php echo UrlHelper::generateFullUrl('Blog', 'postDetail', array($blogPostData['post_id'])); ?>/">
                                                                <i class="icn"><svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb">
                                                                        </use>
                                                                    </svg></i>
                                                            </a>
                                                        </li>
                                                        <li class="social-twitter">
                                                            <a class="st-custom-button" data-network="twitter">
                                                                <i class="icn"><svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw">
                                                                        </use>
                                                                    </svg></i>
                                                            </a>
                                                        </li>
                                                        <li class="social-pintrest">
                                                            <a class="st-custom-button" data-network="pinterest">
                                                                <i class="icn"><svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt">
                                                                        </use>
                                                                    </svg></i>
                                                            </a>
                                                        </li>
                                                        <li class="social-email">
                                                            <a class="st-custom-button" data-network="email">
                                                                <i class="icn"><svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope">
                                                                        </use>
                                                                    </svg></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <?php /*<ul class="likes-count">
                                    <!--<li><i class="icn-like"><img src="<?php echo CONF_WEBROOT_URL; ?>images/eye.svg"></i>500 Views</li>-->
                                <?php if ($blogPostData['post_comment_opened']) { ?>
                                <li><i class="icn-msg"><img src="<?php echo CONF_WEBROOT_URL; ?>images/comments.svg"></i><?php echo $commentsCount,' ',Labels::getLabel('Lbl_Comments', $siteLangId); ?></li>
                                <?php  } ?>
                                </ul>*/ ?>
                            </div>
                            <div class="divider"></div>
                        <?php } ?>
                        <div class="post__detail">
                            <?php echo FatUtility::decodeHtmlEntities($blogPostData['post_description']); ?>
                        </div>
                    </div>
                    <?php
                    if (false === CommonHelper::isAppUser()) {
                        if ($blogPostData['post_comment_opened']) { ?>
                            <?php echo $srchCommentsFrm->getFormHtml(); ?>
                            <div class="gap"></div>
                            <div class="comments rounded border" id="container--comments">
                                <h2><?php echo ($commentsCount) ? sprintf(Labels::getLabel('Lbl_Comments(%s)', $siteLangId), $commentsCount) : Labels::getLabel('Lbl_Comments', $siteLangId); ?></h2>
                                <div id="comments--listing"> </div>
                                <div class="text-center m-4" id="loadMoreCommentsBtnDiv"></div>
                            </div>
                        <?php } ?>
                        <?php if ($blogPostData['post_comment_opened'] && UserAuthentication::isUserLogged() && isset($postCommentFrm)) { ?>
                            <div class="gap"></div>
                            <div id="respond" class="comment-respond rounded">
                                <h2><?php echo Labels::getLabel('Lbl_Leave_A_Comment', $siteLangId); ?></h2>
                                <?php
                                $postCommentFrm->setFormTagAttribute('class', 'form');
                                $postCommentFrm->setFormTagAttribute('onsubmit', 'setupPostComment(this);return false;');
                                $postCommentFrm->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_POSITION_NONE);
                                $postCommentFrm->developerTags['colClassPrefix'] = 'col-md-';
                                $postCommentFrm->developerTags['fld_default_col'] = 12;
                                $nameFld = $postCommentFrm->getField('bpcomment_author_name');
                                $nameFld->addFieldTagAttribute('readonly', true);
                                $nameFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Name', $siteLangId));
                                $nameFld->developerTags['col'] = 6;
                                $emailFld = $postCommentFrm->getField('bpcomment_author_email');
                                $emailFld->addFieldTagAttribute('readonly', true);
                                $emailFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Email_Address', $siteLangId));
                                $emailFld->developerTags['col'] = 6;
                                $commentFld = $postCommentFrm->getField('bpcomment_content');
                                $commentFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Message', $siteLangId));

                                $btnSubmitFld = $postCommentFrm->getField('btn_submit');
                                $btnSubmitFld->setFieldTagAttribute('class', 'btn btn-brand btn-wide');

                                echo $postCommentFrm->getFormHtml(); ?>
                            </div>

                    <?php }
                    } ?>

                </div>
            </div>
            <?php if (false === CommonHelper::isAppUser()) { ?>
                <div class="col-xl-3 col-lg-4">
                    <?php $this->includeTemplate('_partial/blogSidePanel.php', array('popularPostList' => $popularPostList, 'featuredPostList' => $featuredPostList, 'siteLangId' => $siteLangId)); ?>
                </div>
            <?php } ?>
            <!--<div class="col-md-3 colums__right">
            <div class="wrapper--adds" >
              <div class="grids" id="div--banners"> </div>
            </div>
          </div>-->
        </div>
    </div>
</section>
<?php if (false === CommonHelper::isAppUser()) { ?>
    <script>
        var boolLoadComments = (<?php echo FatUtility::int($blogPostData['post_comment_opened']); ?>) ? true : false;
        /* for social sticky */
        $(window).scroll(function() {
            body_height = $(".post-data").position();
            scroll_position = $(window).scrollTop();
            if (body_height.top < scroll_position)
                $(".post-data").addClass("is-fixed");
            else
                $(".post-data").removeClass("is-fixed");

        });

        $('#shareModal').insertAfter('.post-detail');
    </script>
    <?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>
<?php } ?>