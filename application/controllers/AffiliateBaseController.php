<?php

class AffiliateBaseController extends LoggedUserController
{

    public function __construct($action)
    {
        Message::addErrorMessage('Invalid Request');
        FatApp::redirectUser(UrlHelper::generateUrl(''));
        die();
        parent::__construct($action);
        if (!User::isAffiliate()) {
            if (FatUtility::isAjaxCall()) {
                Message::addErrorMessage(Labels::getLabel("LBL_Unauthorised_access", $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            FatApp::redirectUser(UrlHelper::generateUrl('account'));
        }
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'AFFILIATE';
        $this->set('bodyClass', 'is--dashboard');
    }

}
