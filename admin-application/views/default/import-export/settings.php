<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onsubmit', 'updateSettings(this); return(false);');
$frm->setFormTagAttribute('class', 'web_form');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 6;

$noteFld = $frm->getField('id_setting_note');
$noteFld->developerTags['col'] = 12;

echo $frm->getFormHtml();

