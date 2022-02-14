<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);
$frm->setFormTagAttribute('onsubmit', 'setupCollection(); return(false);');
$fld = $frm->getField('auto_update_other_langs_data');
if (null != $fld) {
    $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $fld->developerTags['cbHtmlAfterCheckbox'] = '';
}
$siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
?>
<section class="section">
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <?php if ($collection_layout_type != Collections::TYPE_PENDING_REVIEWS1) { ?>
                        <ul class="tabs_nav">
                            <li>
                                <a href="javascript:void(0)" onclick="collectionForm(<?php echo $collection_parent_type; ?>, <?php echo $collection_parent_type_layout; ?>, <?php echo $collection_parent_id; ?>, 0);">
                                    <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                                </a>
                            </li>
                            <?php
                            if (!empty($subCollectionsList)) {
                                foreach ($subCollectionsList as $subCollection) {
                                    ?>
                                    <li>
                                        <a class="<?php echo ($collection_id == $subCollection['collection_id']) ? "active" : ""; ?>" href="javascript:void(0)"  onclick="tabsForm(<?php echo $collection_parent_id ?>, <?php echo $subCollection['collection_id']; ?>, <?php echo Collections::COLLECTION_TYPE_SUB_COLLECTION; ?>);" >
                                            <?php echo $subCollection['collection_name']; ?>
                                        </a>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                            <?php if (count($subCollectionsList) < $recordLimit) { ?>
                                <li>
                                    <a  class="<?php echo ($collection_id == 0) ? "active" : ""; ?>" href="javascript:void(0)"  onclick="tabsForm(<?php echo $collection_parent_id ?>, 0);">
                                        <?php echo Labels::getLabel('LBL_Tab_+', $adminLangId); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel" id="tabs_form">
                            <?php echo $frm->getFormTag(); ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
                                                $fld = $frm->getField('collection_name[' . $siteDefaultLangId . ']');
                                                echo $fld->getCaption();
                                                ?>
                                                <span class="spn_must_field">*</span></label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('collection_name[' . $siteDefaultLangId . ']'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php /* <div class="col-md-8">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
                                                $fld = $frm->getField('collection_description[' . $siteDefaultLangId . ']');
                                                echo $fld->getCaption();
                                                ?>
                                                <span class="spn_must_field">*</span>
                                            </label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('collection_description[' . $siteDefaultLangId . ']'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div> */ ?>
                            </div>
                            <?php
                            if (!empty($otherLangData)) {
                                foreach ($otherLangData as $langId => $data) {
                                    ?>
                                    <div class="accordians_container accordians_container-categories" defaultLang="<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>" onClick="translateData(this)">
                                        <div class="accordian_panel">
                                            <span class="accordian_title accordianhead accordian_title" id="collapse_<?php echo $langId; ?>">
                                                <?php
                                                echo $data . " ";
                                                echo Labels::getLabel('LBL_Language_Data', $adminLangId);
                                                ?>
                                            </span>
                                            <div class="accordian_body accordiancontent" style="display: none;">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="field-set">
                                                            <div class="caption-wraper">
                                                                <label class="field_label">
                                                                    <?php
                                                                    $fld = $frm->getField('collection_name[' . $langId . ']');
                                                                    echo $fld->getCaption();
                                                                    ?>
                                                                </label>
                                                            </div>
                                                            <div class="field-wraper">
                                                                <div class="field_cover">
                                                                    <?php echo $frm->getFieldHtml('collection_name[' . $langId . ']'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php /* <div class="col-md-12">
                                                        <div class="field-set">
                                                            <div class="caption-wraper">
                                                                <label class="field_label">
                                                                    <?php
                                                                    $fld = $frm->getField('collection_description[' . $langId . ']');
                                                                    echo $fld->getCaption();
                                                                    ?>
                                                                </label>
                                                            </div>
                                                            <div class="field-wraper">
                                                                <div class="field_cover">
                                                                    <?php echo $frm->getFieldHtml('collection_description[' . $langId . ']'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> */ ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field-set d-flex align-items-center">
                                        <div class="field-wraper w-auto">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('btn_submit'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            echo $frm->getFieldHtml('record_ids');
                            echo $frm->getFieldHtml('collection_id');
                            echo $frm->getFieldHtml('collection_active');
                            echo $frm->getFieldHtml('collection_type');
                            echo $frm->getFieldHtml('collection_layout_type');
                            echo $frm->getFieldHtml('collection_parent_id');
                            ?>
                            </form>
                            <?php echo $frm->getExternalJS(); ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                    $recordFrm->setFormTagAttribute('class', 'web_form form_horizontal');
                                    $recordFrm->developerTags['colClassPrefix'] = 'col-md-';
                                    $recordFrm->developerTags['fld_default_col'] = 12;
                                    $fld = $recordFrm->getField('collection_records');
                                    $fld->setWrapperAttribute('class', 'ui-front');
                                    echo $recordFrm->getFormHtml();
                                    ?>
                                    <div id="records_list">
                                        <table class="table table-responsive table--hovered" id="collection-record">
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $("document").ready(function () {
        var collectionId = $("input[name='collection_id']").val();
        var productIds = '';
        $("select[name='collection_records']").select2({
            closeOnSelect: true,
            dir: layoutDirection,
            allowClear: true,
            placeholder: $("select[name='collection_records']").attr('placeholder'),
            ajax: {
                url: fcom.makeUrl('Collections', 'autoCompleteSelprods'),
                dataType: 'json',
                delay: 250,
                method: 'post',
                data: function (params) {
                    return {
                        keyword: params.term,
                        page: params.page,
                        collection_id: collectionId,
                        productIds : $('input[name="record_ids"]').val()
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.products,
                        pagination: {
                            more: params.page < data.pageCount
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            templateResult: function (result)
            {
                return result.name;
            },
            templateSelection: function (result)
            {
                return result.name || result.text;
            }
        }).on('select2:selecting', function (e)
        {
            var item = e.params.args.data;
            if (collectionId > 0) {
                updateRecord(<?php echo $collection_id; ?>, item.id);
            } else {
                var oldRecords = $('input[name="record_ids"]').val();
                var apendHtml = '<tr><td><a class="text-dark" href="javascript:void(0)" title="Remove" onclick="deleteRow(this, ' + item.id + ');"><i class=" icon ion-close" data-record-id="1"></i></a> </td><td>' + item.name + '</td></tr>';
                if (oldRecords != '') {
                    var oldEntriesArr = oldRecords.split(",");
                    if (oldEntriesArr.length >= <?php echo $childRecordLimit; ?>) {
                        fcom.displayErrorMessage('<?php echo Labels::getLabel('MSG_Maximum_allowed_record_for_this_layout_is', $adminLangId) . ' ' . $childRecordLimit; ?>');
                    } else {
                        oldEntriesArr.push(item.id);
                        $('input[name="record_ids"]').val(oldEntriesArr.join(','));
                        $('#collection-record').append(apendHtml);
                    }
                } else {
                    $('input[name="record_ids"]').val(item.id);
                    $('#collection-record').append(apendHtml);
                }

            }
            setTimeout(function () {
                $("select[name='collection_records']").val('').trigger('change.select2');
            }, 200);

        });

        deleteRow = function (obj, recordId) {
            var oldRecords = $('input[name="record_ids"]').val();
            if (oldRecords != '') {
                var oldEntriesArr = oldRecords.split(",");
                var oldEntriesArr = oldEntriesArr.map(function (x) {
                    return parseInt(x, 10);
                });
                var index = oldEntriesArr.indexOf(parseInt(recordId));
                if (index > -1) {
                    oldEntriesArr.splice(index, 1);
                    $(obj).parents('tr').remove();
                }
                var newEntries = '';
                if (oldRecords.length > 0) {
                    newEntries = oldEntriesArr.join(',');
                }
                $('input[name="record_ids"]').val(newEntries);
            }
        };

    });
</script>