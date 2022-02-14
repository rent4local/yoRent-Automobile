<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
</div>
    <!--footer start here-->
    <footer id="footer">
        <p>
        <?php if (CommonHelper::demoUrl()) {
                $replacements = array(
                    '{YEAR}'=> '&copy; '.date("Y"),
                    '{PRODUCT}'=>'<a target="_blank" href="https://yo-rent.com">Yo!Rent</a>',
                    '{OWNER}'=> '<a target="_blank" href="https://www.fatbit.com/">FATbit Technologies</a>',
                );
    echo CommonHelper::replaceStringData(Labels::getLabel('LBL_COPYRIGHT_TEXT', $adminLangId), $replacements);
} else {
    echo FatApp::getConfig("CONF_WEBSITE_NAME_".$adminLangId, FatUtility::VAR_STRING, 'Copyright &copy; '.date('Y').' <a href="https://www.fatbit.com/">FATbit.com'); ?>
        <?php
}
        echo " ".CONF_WEB_APP_VERSION;
        ?>
        </p>

    </footer>
    <!--footer start here-->
</div>

<?php
    $alertClass = '';
    if (Message::getInfoCount()>0) {
        $alertClass = 'alert--info';
    } elseif (Message::getErrorCount()>0) {
        $alertClass = 'alert--danger';
    } elseif (Message::getMessageCount()>0) {
        $alertClass = 'alert--success';
    }
?>

<div class="system_message alert alert--positioned-bottom-center alert--positioned-small <?php echo $alertClass; ?>">
    <div class="close"></div>
    <div class="sysmsgcontent content">
        <?php
        $haveMsg = false;
        if (Message::getMessageCount() || Message::getErrorCount()) {
            $haveMsg = true;
            echo html_entity_decode(Message::getHtml());
        } ?>
    </div>
</div>

<?php if ($haveMsg) { ?>
<script type="text/javascript">
    $("document").ready(function(){
        if( CONF_AUTO_CLOSE_SYSTEM_MESSAGES == 1 ){
            var time = CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES * 1000;
            setTimeout(function(){
                $.systemMessage.close();
            }, time);
        }
    });
</script>
<?php } ?>

    <!--wrapper end here-->

    <?php if ($isAdminLogged) {?>
    <!--div class="color_pallete">
        <a href="#" class="pallete_control"><i class="ion-android-settings icon"></i></a>
        <div class="controlwrap">
            <h5>Color Palette</h5>
            <ul class="colorpallets">
                <li class="red"><a href="javascript:void(0)" class="color_red"></a></li>
                <li class="green"><a href="javascript:void(0)" class="color_green"></a></li>
                <li class="yellow"><a href="javascript:void(0)" class="color_yellow"></a></li>
                <li class="orange"><a href="javascript:void(0)" class="color_orange"></a></li>
                <li class="darkblue"><a href="javascript:void(0)" class="color_darkblue"></a></li>
                <li class="darkgrey"><a href="javascript:void(0)" class="color_darkgrey"></a></li>
                <li class="blue"><a href="javascript:void(0)" class="color_blue"></a></li>
                <li class="brown"><a href="javascript:void(0)" class="color_brown"></a></li>
            </ul>
        </div>
    </div-->

    <?php } ?>
    <?php if (CommonHelper::demoUrl()) {
            if (FatApp::getConfig('CONF_SITE_TRACKER_CODE', FatUtility::VAR_STRING, '')) {
                echo FatApp::getConfig('CONF_SITE_TRACKER_CODE', FatUtility::VAR_STRING, '');
            }
            if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) {
                $this->includeTemplate('restore-system/page-content.php');
            }
        }
    ?>
<?php if (FatApp::getConfig('CONF_PWA_SERVICE_WORKER', FatUtility::VAR_INT, 1)) {?>
    <script>
   $(document).ready(function(){
    if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
    navigator.serviceWorker.register('<?php echo CONF_WEBROOT_FRONTEND;?>sw.js?t=<?php echo filemtime(CONF_INSTALLATION_PATH . 'public/sw.js');?>&f').then(function(registration) {
    });
    });
    }
});
</script>
<?php }?>
<?php if (!isset($_SESSION['geo_location']) && FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '') != '') { ?>
<script type="text/javascript" src='https://maps.google.com/maps/api/js?key=<?php echo FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '');?>&libraries=places'></script>
<?php } ?>
<?php if (CommonHelper::demoUrl()) { 
    $requestController = (AdminAuthentication::isAdminLogged()) ? "home" : "AdminGuest";
    
    ?>
<script>
    demoRequestPopup = function() {
        fcom.ajax(fcom.makeUrl('<?php echo $requestController; ?>', 'getDemoRequestForm'), '', function (t) {
            $.facebox(function() {
               fcom.updateFaceboxContent(t, 'modal-content__getDemo');
            });
        });
    }
    <?php $isRequestSent = (isset($_COOKIE['yorent_request_submitted']) && $_COOKIE['yorent_request_submitted'] == 1) ? 1 : 0; ?>;
    <?php $formLoaded = (isset($_COOKIE['form_loaded']) && $_COOKIE['form_loaded'] == 1) ? 1 : 0; ?>;
    <?php if ($isRequestSent == 0 && $formLoaded == 0) { ?>
        setTimeout(() => {
            document.cookie = 'form_loaded=1';
            demoRequestPopup();
        }, 1000);
    <?php } ?>
    
    submitDemoRequest = function(frm, q = "v3") {  
        if (!$(frm).validate())
        return false;
            
        var data = fcom.frmData(frm);
        $.mbsmessage(langLbl.processing, false, 'alert--process'); 
        $.ajax({
            type: 'POST',
            url: 'https://www.yo-rent.com/send-demo-request.html', 
            data: data,
            success: function(res) {
                res = $.parseJSON(res);
                $.mbsmessage(res.message, false, 'alert--success');
                document.cookie = 'yorent_request_submitted=1';
                setTimeout(() => {
                    window.location.href = fcom.makeUrl('custom', 'thankYou', '', SITE_ROOT_URL, 1) + '?q='+ q;
                }, 1000);
            }
        });
    }
</script>
<?php } ?>
</body>
</html>


