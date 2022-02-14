<?php defined('SYSTEM_INIT') or die('Invalid usage'); ?>

<ul class="breadcrumb flat">
  <li><a href="<?php echo UrlHelper::generateUrl('') ?>"><img alt="" src="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/home.png"> </a></li>
  <?php 
	if(!empty($this->variables['nodes'])){	
		foreach($this->variables['nodes'] as $nodes){?>
			<?php if(!empty($nodes['href'])){?>
					<li><a href="<?php echo $nodes['href'];?>" <?php echo (!empty($nodes['other']))?$nodes['other']:'';?>><?php echo $nodes['title'];?></a></li>
			<?php }else{?>
					<li><?php echo (isset($nodes['title']))?$nodes['title']:'';?></li>  			
		<?php 	} 
		} 
	}?>
</ul>
