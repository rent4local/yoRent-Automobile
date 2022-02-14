<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frm->setFormTagAttribute('class', 'form form--horizontal');
    $frm->setFormTagAttribute('onsubmit', 'setupPromotion(this); return(false);');

    $frm->developerTags['colClassPrefix'] = 'col-md-';
    $frm->developerTags['fld_default_col'] = 12;
    /* if($promotionId)
    {
        $typeFld = $frm->getField('promotion_type');
        $typeFld->addFieldTagAttribute('disabled','disabled');
    } */

if (User::isSeller()) {
        $shopFld = $frm->getField('promotion_shop');
        $shopFld->setWrapperAttribute('class', 'promotion_shop_fld');
        $shopFld->htmlAfterField = '<p class="note">'.Labels::getLabel('LBL_Note:_Used_to_promote_shop.', $siteLangId).'</p>';

        $shopCpcFld = $frm->getField('promotion_shop_cpc');
        $shopCpcFld->setWrapperAttribute('class', 'promotion_shop_fld');
        $shopCpcFld->htmlAfterField = '<p class="note">'.Labels::getLabel('MSG_PPC_cost_per_click_for_shop', $siteLangId).'</p>';

        $productFld = $frm->getField('promotion_product');
        $productFld->setWrapperAttribute('class', 'promotion_product_fld');
        $productFld->htmlAfterField = '<p class="note">'.Labels::getLabel('LBL_Note:_Used_to_promote_product.', $siteLangId).'</p>';

        $productCpcFld = $frm->getField('promotion_product_cpc');
        $productCpcFld->setWrapperAttribute('class', 'promotion_product_fld');
        $productCpcFld->htmlAfterField = '<p class="note">'.Labels::getLabel('MSG_PPC_cost_per_click_for_Product', $siteLangId).'</p>';
}

    $locationFld = $frm->getField('banner_blocation_id');
    $locationFld->setFieldTagAttribute('id', 'banner_blocation_id');
    $locationFldId = $locationFld->getFieldTagAttribute('id');
    $locationFld->setWrapperAttribute('class', 'location_fld');
    
    /* $instructionFileUrl = UrlHelper::generateFileUrl().CONF_UPLOADS_FOLDER_NAME.'/cropped/screenshot/homescreen.png'; */
    $instructionUrl = UrlHelper::generateFullUrl('home', 'index'). '?isPreview=1';
    
    $locationFld->captionWrapper = array('<div>', '<ul class="actions ml-2"><li><a href="'. $instructionUrl .'" class="button small green" target="_blank" title="'. Labels::getLabel('LBL_Layout_Instruction', $siteLangId) .'"><i class="far fa-file-alt"></i></a></li></ul></div>');
        
    $bannerPosFld = $frm->getField('banner_position');
    $bannerPosFld->setWrapperAttribute('class', 'banner_position_fld');
    
    // $locationFld->htmlAfterField= '<a href="javascript:void(0)" onClick="viewWrieFrame($(\'#'.$locationFldId.'\').val())">'.Labels::getLabel('LBL_View_WireFrame', $siteLangId).'</a>';

    $slideUrlFld = $frm->getField('slide_url');
    $slideUrlFld->setWrapperAttribute('class', 'slide_url_fld');
    $slideUrlFld->htmlAfterField = '<p class="note">'.Labels::getLabel('LBL_Note:_Used_to_promote_through_slider.', $siteLangId).'</p>';

    /* $slideTargetUrlFld = $frm->getField('slide_target');
    $slideTargetUrlFld->setWrapperAttribute( 'class' , 'slide_url_fld'); */

    $slideCpcFld = $frm->getField('promotion_slides_cpc');
    $slideCpcFld->setWrapperAttribute('class', 'slide_url_fld');
    $slideCpcFld->htmlAfterField = '<p class="note">'.Labels::getLabel('MSG_PPC_cost_per_click_for_Slides', $siteLangId).'</p>';

    $urlFld = $frm->getField('banner_url');
    $urlFld->setWrapperAttribute('class', 'banner_url_fld');
    $urlFld->htmlAfterField = '<p class="note">'.Labels::getLabel('LBL_Note:_Used_to_promote_through_banner.', $siteLangId).'</p>';

    /* $bannerTargetUrlFld = $frm->getField('banner_target');Request Products Which Is Availble To All Sellers
    $bannerTargetUrlFld->setWrapperAttribute( 'class' , 'banner_url_fld'); */

    $btnSubmitFld = $frm->getField('btn_submit');
    $btnSubmitFld->setFieldTagAttribute('class', 'btn btn-brand btn-wide');
?>
<div class="tabs">
    <ul>
        <li class="is-active"><a href="javascript:void(0);" onClick="promotionForm(<?php echo $promotionId;?>)"><?php echo Labels::getLabel('LBL_General', $siteLangId);?></a></li>
        <li class="<?php echo (0 == $promotionId) ? 'fat-inactive' : ''; ?>">
            <a href="javascript:void(0);" <?php echo (0 < $promotionId) ? "onclick='promotionLangForm(" . $promotionId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
            </a>
        </li>
        <?php $inactive = ($promotionId==0)?'fat-inactive':'';?>
        <?php if ($promotionType == Promotion::TYPE_BANNER || $promotionType == Promotion::TYPE_SLIDES) {?>
        <li class="<?php echo $inactive; ?>"><a href="javascript:void(0)" <?php if ($promotionId>0) {
            ?> onClick="promotionMediaForm(<?php echo $promotionId;?>)" <?php
                   }?>><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        <?php }?>
    </ul>
</div>
<div class="tabs__content">
    <div class="row">
        <div class="col-md-6">
            <?php echo $frm->getFormHtml(); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    var bannerLayoutData = '<?php echo json_encode($bannerLayoutData);?>';
    jQuery('.time').datetimepicker({
      datepicker:false,
      format:'H:i'
    });

    $("document").ready(function(){
        var PROMOTION_TYPE_BANNER = <?php echo Promotion::TYPE_BANNER; ?>;
        var PROMOTION_TYPE_SHOP = <?php echo Promotion::TYPE_SHOP; ?>;
        var PROMOTION_TYPE_PRODUCT = <?php echo Promotion::TYPE_PRODUCT; ?>;
        var PROMOTION_TYPE_SLIDES = <?php echo Promotion::TYPE_SLIDES; ?>;

        $("select[name='promotion_type']").change(function(){
            var promotionType = $(this).val();
            $(".promotion_shop_fld").hide();
            $(".promotion_product_fld").hide();
            $(".banner_url_fld").hide();
            $(".location_fld").hide();
            $(".banner_position_fld").hide();
            $(".slide_url_fld").hide();

            if( promotionType == PROMOTION_TYPE_BANNER ){
                $(".banner_url_fld").show();
                $(".location_fld").show();
                /* $(".banner_position_fld").show(); */
            }

            if( promotionType == PROMOTION_TYPE_SHOP ){
                $(".promotion_shop_fld").show();
            }

            if( promotionType == PROMOTION_TYPE_PRODUCT ){
                $(".promotion_product_fld").show();
            }

            if( promotionType == PROMOTION_TYPE_SLIDES ){
                $(".slide_url_fld").show();
            }

            fcom.updateWithAjax(fcom.makeUrl('Advertiser', 'getTypeData', [<?php echo $promotionId;?>, promotionType ]), '', function(t) {
                $.mbsmessage.close();
                if(t.promotionType == PROMOTION_TYPE_SHOP){
                    $("input[name='promotion_shop']").val(t.label);
                }else if(t.promotionType == PROMOTION_TYPE_PRODUCT){
                    $("input[name='promotion_product']").val(t.label) ;
                }
                $("input[name='promotion_record_id']").val(t.value)    ;
            });
        });

        $("select[name='promotion_type']").trigger('change');
        $('input[name=\'promotion_product\']').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function(request, response) {
                $.ajax({
                    url: fcom.makeUrl('Advertiser', 'autoCompleteSelprods'),
                    data: {keyword: request['term'],fIsAjax:1},
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return { label: item['name'], value: item['name'], id: item['id'] };
                        }));
                    },
                });
            },
            'select': function (event, ui) {
                $("input[name='promotion_record_id']").val(ui.item.id);
            }
        });
        
        
        bannerLayoutData = $.parseJSON(bannerLayoutData);
        $('select[name="banner_blocation_id"]').on('change', function() {
            var positionId = parseInt($(this).val());
            if (positionId > 0) {
                var layoutType = bannerLayoutData[positionId].collection_layout_type;
                if (layoutType == <?php echo Collections::TYPE_BANNER_LAYOUT4; ?>)  {
                   $(".banner_position_fld").show();
                } else {
                    $(".banner_position_fld").hide();
                }
            }
        })
        $('select[name="banner_blocation_id"]').trigger('change');
        
    });
</script>
