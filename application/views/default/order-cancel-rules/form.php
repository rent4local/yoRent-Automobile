<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'setupRule(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 3;

$fld1 = $frm->getField('ocrule_duration_min');
if (!empty($fld1)) {
    $fld1->developerTags['noCaptionTag'] = true;
}

if ($isInfinty == 0) {
    $fld2 = $frm->getField('ocrule_duration_max');
    if (!empty($fld2)) {
        $fld2->developerTags['noCaptionTag'] = true;
    }
}

if ($isInfinty) {
    $fld4 = $frm->getField('infinity_field');
    if (!empty($fld4)) {
        $fld4->developerTags['noCaptionTag'] = true;
    }
}

$fld3 = $frm->getField('ocrule_refund_amount');
$fld3->developerTags['noCaptionTag'] = true;

$btn = $frm->getField('btn_submit');
$btn->setFieldTagAttribute('class', 'btn btn-brand  ');
$btn->developerTags['noCaptionTag'] = true;
?>
<div class="card-body">
    <?php echo $frm->getFormHtml(); ?>
</div>