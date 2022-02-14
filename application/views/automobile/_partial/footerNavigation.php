<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($footer_navigation)) { ?>
    <?php foreach ($footer_navigation as $nav) { ?>
		<div class="col-md-3">
			<div class="toggle-group">
				<h5 class="toggle__trigger toggle__trigger-js"><?php echo $nav['parent']; ?></h5>
				<div class="toggle__target toggle__target-js">
					<ul class="nav-vertical">
					<?php if ($nav['pages']) {
					$getOrgUrl = (CONF_DEVELOPMENT_MODE) ? true : false;
                    foreach ($nav['pages'] as $link) {
                        $navUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id']);
                        $OrgnavUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id'], $getOrgUrl); ?>
                        <li><a target="<?php echo $link['nlink_target']; ?>" data-org-url="<?php echo $OrgnavUrl; ?>" href="<?php echo $navUrl; ?>"><?php echo $link['nlink_caption']; ?></a></li>
                    <?php }
					} ?>
					</ul>
				</div>
			</div>
        </div>
    <?php }
} ?>
