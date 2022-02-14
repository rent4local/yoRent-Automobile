<?php defined('SYSTEM_INIT') or die('Invalid Usage'); 

	$frm->setFormTagAttribute('class','form form--horizontal');
	$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
	$frm->developerTags['fld_default_col'] = 12;
	$frm->setFormTagAttribute('onSubmit', 'setupOrderMessage(this,"seller"); return false;');
	$fromFld = $frm->getField('send_message_from');
    $toFld = $frm->getField('send_message_to');
    
	$btnFld = $frm->getField('btn_submit');
	$btnFld->addFieldTagAttribute('class', 'btn btn-brand btn-wide');

	$fromFldHtml = new HtmlElement( 'div', array( 'class'=>'field-set' ));
	$fromFldCaptionWrapper = $fromFldHtml->appendElement('div', array('class' => 'caption-wraper'));
	$fromFldCaptionWrapper->appendElement( 'label', array('class'=>'field_label'), Labels::getLabel('LBL_From', $siteLangId) );

	$fromFldFieldWrapper = $fromFldHtml->appendElement('div', array('class' => 'field-wraper'));
	$fromFldFieldWrapper->appendElement( 'div', array('class' => 'field_cover'), $data['op_shop_owner_name'].' (<em>'.$data['op_shop_name'].'</em>)', true );

	$fromFld->value = $fromFldHtml->getHtml();

	$toFldHtml = new HtmlElement( 'div', array( 'class'=>'field-set' ));
	$toFldCaptionWrapper = $toFldHtml->appendElement('div', array('class' => 'caption-wraper'));
	$toFldCaptionWrapper->appendElement( 'label', array('class'=>'field_label'), Labels::getLabel('LBL_To', $siteLangId) );
	$toFldData = $userData['credential_username'].' (<em>'.$userData['user_name'].'</em>)';
	$toFldFieldWrapper = $toFldHtml->appendElement('div', array('class' => 'field-wraper'));
	$toFldFieldWrapper->appendElement( 'div', array('class' => 'field_cover'),$toFldData, true );

	$toFld->value = $toFldHtml->getHtml();

?>

<div class="modal-dialog modal-dialog-centered" role="document" id="send-order-message">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Send_Message_to_Buyer', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">

			<?php echo $frm->getFormHtml(); ?>

        </div>
    </div>
</div>

<!-- <div id="body" class="body">
  <section class="section">
    <div class="container">      
		  <div class="row justify-content-center">
			<div class="col-lg-7">
                <div class="section-head">
                    <div class="section__heading">
                        <h4><?php echo Labels::getLabel('LBL_Send_Message_to_order_owner', $siteLangId); ?></h4>
                    </div>
                </div>
                <div class="bg-gray rounded p-5"> <?php echo $frm->getFormHtml(); ?> </div>
			</div>
		  </div>
		 
    </div>
  </section>
	
</div>
 -->