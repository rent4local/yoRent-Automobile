<?php 
if(isset($includeEditor) && $includeEditor){ ?>

<script language="javascript" type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>assets/innovas/scripts/innovaeditor.js"></script>
<script src="<?php echo CONF_WEBROOT_URL; ?>assets/innovas/scripts/common/webfont.js" type="text/javascript"></script>
<?php  }  ?></head>
<body class="<?php echo $bodyClass;?>">
<?php 
if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) { 
    $isAdmin = true;
    
	$tplFile = str_replace( CONF_APPLICATION_PATH, CONF_INSTALLATION_PATH.CONF_FRONT_END_APPLICATION_DIR, CONF_THEME_PATH );
	$tplFile.='default/restore-system/top-header.php';
	include_once $tplFile;	
}?>
<div class="page-container"></div>
