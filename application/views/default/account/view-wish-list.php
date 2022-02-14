<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$randomId = rand(1, 1000);
$frm->setFormTagAttribute('class', 'custom-form setupWishList-Js');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('id', 'setupWishList_Js_' . $randomId);
$frm->setFormTagAttribute('onsubmit', 'setupWishList(this,event); return(false);');
$uwlist_title_fld = $frm->getField('uwlist_title');
$uwlist_title_fld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_New_List', $siteLangId));

$btn = $frm->getField('btn_submit');
$btn->setFieldTagAttribute('class', 'btn btn-brand');
?>

<div class="modal-dialog modal-dialog-centered" role="document" id="wish-list">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Your_List:', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">

            <?php if ($wishLists) { ?>
                <div class="collection__list">
                    <ul class="listing--check">
                        <?php foreach ($wishLists as $list) { ?>
                            <li onClick="addRemoveWishListProduct(<?php echo $selprod_id . ', ' . $list['uwlist_id']; ?>, event);" class="wishListCheckBox_<?php echo $list['uwlist_id']; ?> <?php echo array_key_exists($selprod_id, $list['products']) ? ' is-active' : ''; ?>">
                                <a href="javascript:void(0)">
                                    <?php echo ($list['uwlist_type'] == UserWishList::TYPE_DEFAULT_WISHLIST) ? Labels::getLabel('LBL_Default_list', $siteLangId) : $list['uwlist_title']; ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
            <div class="collection__form form">
                <?php
                echo $frm->getFormTag();
                echo $frm->getFieldHtml('uwlist_title');
                echo $frm->getFieldHtml('selprod_id');
                echo $frm->getFieldHtml('btn_submit');
                ?>
                </form>
                <?php echo $frm->getExternalJs(); ?> </div>

        </div>
    </div>
</div>