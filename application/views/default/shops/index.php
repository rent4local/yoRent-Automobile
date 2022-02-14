<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div id="body" class="body">
    <section class="bg-second pt-3 pb-3">
        <div class="container">
            <div class="section-head section--white--head justify-content-center mb-0">
                    <h1 class="mb-0 section__heading"><?php echo $title; ?>
                    </h1>

            </div>
        </div>
    </section>
    <section class="section"> 
        <div class="container">
            <div class="interactive-stores">
                <div class="interactive-stores__map">                   
                        <div class="map-loader is-loading">                        
                            <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
                                <path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">
                                    <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"></animateTransform>
                                </path>
                            </svg>
                        </div>   
                        <div class="canvas-map" id="shopMap--js">
                        </div>
                    
                </div> 
            <div id="listing"> </div>              
            </div> 
        </div>
    </section>
</div>
<?php 
$userAddress = Address::getYkGeoData();
$lat = ($userAddress['ykGeoLat'] == '') ? FatApp::getConfig('CONF_GEO_DEFAULT_LAT', FatUtility::VAR_STRING, '') : $userAddress['ykGeoLat'];
$lng = ($userAddress['ykGeoLng'] == '') ? FatApp::getConfig('CONF_GEO_DEFAULT_LNG', FatUtility::VAR_STRING, '') : $userAddress['ykGeoLng'];     
?>
<script>
    var USER_LAT = "<?php echo $lat;?>";
    var USER_LNG = "<?php echo $lng;?>";
</script>    


<?php echo $searchForm->getFormHtml(); ?>
