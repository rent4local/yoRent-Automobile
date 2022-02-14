<?php

class SignatureController extends MyAppController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function view(){
        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }
        $this->set('recordId', FatApp::getPostedData('record_id', FatUtility::VAR_INT, 0));
        $this->set('updateController', FatApp::getPostedData('controllerName', FatUtility::VAR_STRING, 'Signature'));
        $this->_template->render(false, false);
    }

    public function store() {
        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }
        $cart = new Cart();
        $post = FatApp::getPostedData();
        if (empty($post) || !isset($post['imgData'])) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl());
        }

        $image_base64 = base64_decode($post['imgData'][1]);
        $arr = array(
            'afile_type' => AttachedFile::FILETYPE_SIGNATURE_IMAGE,
            'afile_record_id' => Cart::getCartUserId(),
            'afile_record_subid' => 0,
            'afile_lang_id' => $this->siteLangId,
            'afile_screen' => 0,
            'afile_unique' => 1,
            'afile_display_order' => 0,
        );
        $signatureData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, Cart::getCartUserId(), 0, -1, true, 0, false);
        if (!empty($signatureData)) {
            $path = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_SIGNATURE_IMAGE_PATH . $signatureData['afile_physical_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        $imageName = AttachedFile::uploadTempImage($image_base64, "signature", $arr, true);
        if (empty($imageName)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        // Message::addMessage(Labels::getLabel('LBL_Signature_Uploaded', $this->siteLangId));
        if(!$cart->setSetDigintalSign()){
            Message::addErrorMessage(Labels::getLabel('LBL_Somthing_Went_Wrong', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('redirectUrl', UrlHelper::generateUrl('Checkout'));
        $this->set('msg', Labels::getLabel('LBL_Signature_Uploaded', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');

    }
}


