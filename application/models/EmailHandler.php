<?php

class EmailHandler extends FatModel
{
    public const ADD_ADDITIONAL_ALERTS = 1;
    public const NO_ADDITIONAL_ALERT = 0;
    public const ONLY_SUPER_ADMIN = 1;
    public const NOT_ONLY_SUPER_ADMIN = 0;

    private $commonLangId;
    public function __construct()
    {
        $this->commonLangId = CommonHelper::getLangId();
    }

    public static function getMailTpl($tpl, $langId = 1)
    {
        $langId = FatUtility::int($langId);
        //$langId=($langId>0)?$langId:1;

        $srch = new SearchBase('tbl_email_templates');
        $srch->addCondition('etpl_code', '=', $tpl);
        if (1 > $langId) {
            $srch->addOrder('etpl_lang_id');
            $srch->addCondition('etpl_lang_id', '!=', 0);
        } else {
            $srch->addCondition('etpl_lang_id', '=', $langId);
        }
        $srch->doNotCalculateRecords();
        //$srch->doNotLimitRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        if (!$row = FatApp::getDb()->fetch($rs)) {
            return false;
        }
        return $row;
    }

    public function sendSms($tpl, $phone, $arrReplacements, $langId)
    {
        $langId = 1 > FatUtility::int($langId) ? $this->commonLangId : FatUtility::int($langId);
        if (empty($phone) || empty($tpl) || empty($arrReplacements)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $langId);
            return false;
        }
        $phone = 0 < strpos($phone, '+') ? $phone : '+' . $phone;

        $smsArchive = new SmsArchive();
        $smsArchive->toPhone($phone);
        $smsArchive->setTemplate($langId, $tpl, $arrReplacements);
        if (!$smsArchive->send()) {
            $this->error = $smsArchive->getError();
            return false;
        }
        return true;
    }

    // Send mail to super Admin, Sub Admin and additonal alert emails.
    public function sendMailToAdminAndAdditionalEmails($tpl, $arrReplacements, $additonalAlerts = 1, $onlySuperAdmin = 0, $langId = 0)
    {
        $langId = FatUtility::int($langId);

        if (1 > $langId) {
            $langId = $this->commonLangId;
        }
        if (empty($tpl) || empty($arrReplacements)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST!!_FAILED_TO_SEND_MAIL_TO_ADMINS.', $langId);
            return false;
        }
        $onlySuperAdmin = FatUtility::int($onlySuperAdmin);
        if (0 < $onlySuperAdmin) {
            return self::sendMailTpl(FatApp::getConfig('CONF_SITE_OWNER_EMAIL', FatUtility::VAR_STRING, ""), $tpl, $langId, $arrReplacements);
        }


        $emails = array(FatApp::getConfig('CONF_SITE_OWNER_EMAIL', FatUtility::VAR_STRING, ""));

        $srch = AdminUsers::getSearchObject();
        $srch->addCondition('admin_id', '!=', Admin::SUPER);
        $srch->addCondition('admin_email_notification', '=', applicationConstants::YES);
        $srch->addMultipleFields(array('admin_id', 'admin_email'));
        $rs = $srch->getResultSet();

        if ($rs) {
            $subAdmins = FatApp::getDb()->fetchAll($rs);
            $emailTempPermissionArr = $this->getEmailTemplatePermissionsArr();
            if (count($subAdmins) && array_key_exists($tpl, $emailTempPermissionArr)) {
                $tplPermission = $emailTempPermissionArr[$tpl];
                $privilege = new AdminPrivilege();
                foreach ($subAdmins as $record) {
                    $userPermissions = array_filter($privilege->getAdminPermissionLevel($record['admin_id']));
                    if (array_key_exists($tplPermission, $userPermissions) && $privilege->canViewNotifications($record['admin_id'], true)) {
                        $emails[] = $record['admin_email'];
                    }
                }
            }
        }

        $additonalAlerts = FatUtility::int($additonalAlerts);
        if (0 < $additonalAlerts) {
            $additionalAlertEmails = FatApp::getConfig("CONF_ADDITIONAL_ALERT_EMAILS", FatUtility::VAR_STRING, '');
            $additionalAlertEmails = array_filter(explode(',', $additionalAlertEmails));
            if (count($additionalAlertEmails)) {
                $emails = array_merge($emails, $additionalAlertEmails);
            }
        }

        $superAdminResp = false;
        foreach ($emails as $index => $email) {
            $email = trim($email);
            if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
                $resp = self::sendMailTpl($email, $tpl, $langId, $arrReplacements);
                if (1 > $index) {
                    $superAdminResp = $resp;
                }
            }
        }
        return $superAdminResp;
    }

    public static function sendMailTpl($to, $tpl, $langId, $vars = array(), $extra_headers = '', $smtp = 0, $smtp_arr = array(), $bcc = array())
    {
        $langId = FatUtility::int($langId);
        if (!$row = static::getMailTpl($tpl, $langId)) {
            $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
            if (!$row = static::getMailTpl($tpl, $langId)) {
                if (!$row = static::getMailTpl($tpl, 0)) {
                    trigger_error(Labels::getLabel('ERR_Email_Template_Not_Found', CommonHelper::getLangId()), E_USER_ERROR);
                    return false;
                }
            }
        }

        if ($row['etpl_status'] != applicationConstants::ACTIVE) {
            return false;
        }

        if (!isset($row['etpl_body']) || $row['etpl_body'] == '') {
            return false;
        }
        $etpl = new FatTemplate('', '');
        $etpl->set('langId', $langId);
        $header = $etpl->render(false, false, '_partial/emails/email-header.php', true);
        /* */
        $ftpl = new FatTemplate('', '');
        $ftpl->set('langId', $langId);
        $footer = $ftpl->render(false, false, '_partial/emails/email-footer.php', true);
        $subject = $row['etpl_subject'];
        $body = $header . $row['etpl_body'] . $footer;

        $vars += static::commonVars($langId);

        foreach ($vars as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $body = str_replace($key, $val, $body);
        }

        if (FatApp::getConfig('CONF_SEND_SMTP_EMAIL', FatUtility::VAR_INT, 0)) {
            if (!$sendEmail = static::sendSmtpEmail($to, $subject, $body, $extra_headers, $tpl, $langId, '', $smtp_arr, $bcc)) {
                return static::sendMail($to, $subject, $body, $extra_headers, $tpl, $langId, $bcc);
            } else {
                return true;
            }
        } else {
            return static::sendMail($to, $subject, $body, $extra_headers, $tpl, $langId, $bcc);
        }
    }

    public static function sendSmtpEmail($toAdress, $Subject, $body, $extra_headers = '', $tpl_name = '', $langId = '', $attachment = "", $smtp_arr = array(), $bcc = array())
    {
        $langId = empty($langId) ? CommonHelper::getLangId() : $langId;

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        $headers .= 'From: ' . FatApp::getConfig("CONF_FROM_NAME_" . $langId, FatUtility::VAR_STRING, '') . "<" . FatApp::getConfig("CONF_FROM_EMAIL", FatUtility::VAR_STRING, "") . ">";

        if ($extra_headers != '') {
            $headers .= $extra_headers;
        }

        $headers .= "\r\nReply-to: " . FatApp::getConfig("CONF_REPLY_TO_EMAIL", FatUtility::VAR_STRING, "");


        if (!FatApp::getDb()->insertFromArray(
            'tbl_email_archives',
            array(
                'emailarchive_to_email' => $toAdress,
                'emailarchive_tpl_name' => $tpl_name,
                'emailarchive_subject' => $Subject,
                'emailarchive_body' => $body,
                'emailarchive_headers' => FatApp::getDb()->quoteVariable($headers),
                'emailarchive_sent_on' => date('Y-m-d H:i:s')
            )
        )) {
            return false;
        }

        if (false == ALLOW_EMAILS) {
            return true;
        }

        include_once CONF_INSTALLATION_PATH . 'library/PHPMailer/PHPMailerAutoload.php';
        $host = isset($smtp_arr["host"]) ? $smtp_arr["host"] : FatApp::getConfig("CONF_SMTP_HOST", FatUtility::VAR_STRING, "");
        $port = isset($smtp_arr["port"]) ? $smtp_arr["port"] : FatApp::getConfig("CONF_SMTP_PORT", FatUtility::VAR_STRING, "");
        $username = isset($smtp_arr["username"]) ? $smtp_arr["username"] : FatApp::getConfig("CONF_SMTP_USERNAME", FatUtility::VAR_STRING, "");
        $password = isset($smtp_arr["password"]) ? $smtp_arr["password"] : FatApp::getConfig("CONF_SMTP_PASSWORD", FatUtility::VAR_STRING, "");
        $secure = isset($smtp_arr["secure"]) ? $smtp_arr["secure"] : FatApp::getConfig("CONF_SMTP_SECURE", FatUtility::VAR_STRING, "");
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->IsHTML(true);
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = $secure;
        $mail->SMTPDebug = false;
        $mail->SetFrom(FatApp::getConfig('CONF_FROM_EMAIL', FatUtility::VAR_STRING, ""));
        $mail->FromName = FatApp::getConfig("CONF_FROM_NAME_" . $langId, FatUtility::VAR_STRING, "");
        $mail->addAddress($toAdress);
        $mail->Subject = '=?UTF-8?B?' . base64_encode($Subject) . '?=';
        $mail->MsgHTML($body);

        if (!empty($bcc)) {
            foreach ($bcc as $email => $name) {
                $mail->AddBCC($email, $name);
            }
        }

        if (!$mail->send()) {
            return false;
        }
        return true;
    }

    private static function sendMail($to, $subject, $body, $extra_headers = '', $tpl_name = '', $langId, $bcc)
    {
        $db = FatApp::getDb();
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        $headers .= 'From: ' . FatApp::getConfig("CONF_FROM_NAME_" . $langId, FatUtility::VAR_STRING, "") . "<" . FatApp::getConfig("CONF_FROM_EMAIL", FatUtility::VAR_STRING, "") . ">";

        if ($extra_headers != '') {
            $headers .= $extra_headers;
        }

        $headers .= "\r\nReply-to: " . FatApp::getConfig("CONF_REPLY_TO_EMAIL", FatUtility::VAR_STRING, "");

        if (!empty($bcc)) {
            $bccEmails = implode(", ", array_keys($bcc));
            $headers .= "\r\nBcc: " . $bccEmails;
        }

        if (!$db->insertFromArray(
            'tbl_email_archives',
            array(
                'emailarchive_to_email' => $to,
                'emailarchive_tpl_name' => $tpl_name,
                'emailarchive_subject' => $subject,
                'emailarchive_body' => $body,
                'emailarchive_headers' => $db->quoteVariable($headers),
                'emailarchive_sent_on' => date('Y-m-d H:i:s')
            )
        )) {
            return false;
        }

        if (FatApp::getConfig("CONF_SEND_EMAIL", FatUtility::VAR_INT, 0) && ALLOW_EMAILS) {
            $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            if (!mail($to, $subject, $body, $headers)) {
                return false;
            }
        }

        return true;
    }

    public function sendSignupVerificationLink($langId, $d)
    {
        $tpl = 'user_signup_verification';

        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{verification_url}' => $d['link'],
        );

        if (isset($d['user_id']) && $d['user_id'] > 0) {
            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $d['user_id'],
                'unotification_body' => Labels::getLabel('APP_VERIFY_YOUR_ACCCOUNT_FROM_REGISTERED_EMAIL', $langId),
                'unotification_type' => 'REGISTRATION_VERIFICATION',
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }

        if (!self::sendMailTpl($d['user_email'], $tpl, $langId, $vars)) {
            return false;
        }
        return true;
    }

    public function sendEmailVerificationLink($langId, $d)
    {
        $tpl = 'user_email_verification';

        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{verification_url}' => $d['link'],
        );

        if (!self::sendMailTpl($d['user_new_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, $d['user_phone'], $vars, $langId);
        return true;
    }

    public function sendChangeEmailRequestNotification($langId, $d)
    {
        $tpl = 'user_change_email_request_notification';
        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{new_email}' => $d['user_new_email'],
        );

        if (!self::sendMailTpl($d['user_new_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, $d['user_phone'], $vars, $langId);
        return true;
    }

    public function sendEmailChangedNotification($langId, $d)
    {
        $tpl = 'user_email_changed_notification';

        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{new_email}' => $d['user_new_email'],
        );

        if (!self::sendMailTpl($d['user_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, $d['user_phone'], $vars, $langId);
        return true;
    }

    public function sendNewRegistrationNotification($langId, $d)
    {
        $tpl = 'new_registration_admin';

        if (isset($d['user_is_affiliate']) && $d['user_is_affiliate']) {
            $tpl = 'new_affiliate_registration_admin';
        }

        $userType = !empty($d['user_type']) ? User::getUserTypesArr($langId)[$d['user_type']] : '';
        $vars = array(
            '{name}' => $d['user_name'],
            '{email}' => $d['user_email'],
            '{phone}' => $d['user_phone'],
            '{username}' => $d['user_username'],
            '{user_type}' => $userType
        );

        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }
        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $vars, $langId);
        return true;
    }

    public function sendNewCatalogNotification($langId, $d)
    {
        $tpl = 'new_catalog_request_admin';
        $vars = array(
            '{request_title}' => $d['request_title'],
            '{brand_name}' => $d['brand_name']
        );
        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }
        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $vars, $langId);
        return true;
    }

    public function sendNewCustomCatalogNotification($langId, $d)
    {
        $tpl = 'new_custom_catalog_request_admin';

        $vars = array(
            '{request_title}' => $d['request_title'],
            '{brand_name}' => $d['brand_name'],
            '{product_model}' => $d['product_model'],
        );
        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }
        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $vars, $langId);
        return true;
    }

    public function sendWelcomeEmailToGuestUser($langId, $d)
    {
        $tpl = 'guest_welcome_registration';

        $vars = array(
            '{name}' => $d['user_name'],
            '{contact_us_email}' => FatApp::getConfig('CONF_CONTACT_EMAIL', FatUtility::VAR_STRING, ""),
        );

        if (!self::sendMailTpl($d['user_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, $d['user_phone'], $vars, $langId);
        return true;
    }

    public function sendWelcomeEmail($langId, $d)
    {
        $tpl = 'welcome_registration';

        if (isset($d['user_is_affiliate']) && $d['user_is_affiliate']) {
            $tpl = 'affiliate_welcome_registration';
        }

        $vars = array(
            '{name}' => $d['user_name'],
            '{contact_us_email}' => FatApp::getConfig('CONF_CONTACT_EMAIL', FatUtility::VAR_STRING, ""),
        );

        if (isset($d['user_id']) && $d['user_id'] > 0) {
            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $d['user_id'],
                'unotification_body' => Labels::getLabel('APP_THANK_YOU_FOR_ACCOUNT_VERIFICATION', $langId),
                'unotification_type' => 'REGISTRATION',
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }

        if (!self::sendMailTpl($d['user_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, $d['user_phone'], $vars, $langId);
        return true;
    }

    public function sendForgotPasswordLinkEmail($langId, $d)
    {
        $tpl = 'forgot_password';
        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{reset_url}' => $d['link'],
        );

        if (!self::sendMailTpl($d['credential_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $phone = !empty($d['user_phone']) ? $d['user_dial_code'] . $d['user_phone'] : '';
        $this->sendSms($tpl, $phone, $vars, $langId);
        return true;
    }

    public function sendResetPasswordConfirmationEmail($langId, &$d)
    {
        $tpl = 'password_changed_successfully';
        $vars = array(
            '{full_name}' => $d['user_name'],
            '{login_link}' => $d['link'],
        );

        if (!self::sendMailTpl($d['credential_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $phone = !empty($d['user_phone']) ? $d['user_dial_code'] . $d['user_phone'] : '';
        $this->sendSms($tpl, $phone, $vars, $langId);
        return true;
    }

    public function sendSupplierApprovalNotification($langId, $d, $approval_request = 1)
    {
        if ($approval_request == 1) {
            $tpl = 'new_supplier_approval_admin';
        } else {
            $tpl = 'new_seller_approved_admin';
        }
        $vars = array(
            '{name}' => $d['user_name'],
            '{email}' => $d['user_email'],
            '{username}' => $d['username'],
            '{reference_number}' => $d['reference_number'],
        );

        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }

        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $vars, $langId);
        return true;
    }

    public function sendSupplierRequestStatusChangeNotification($langId, $d)
    {
        $tpl = 'supplier_request_status_change_buyer';

        $supplierRequestComments = '';
        if ($d['usuprequest_comments'] != '') {
            $supplierRequestComments = nl2br($d['usuprequest_comments']);
        }

        $statusArr = User::getSupplierReqStatusArr($langId);

        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{reference_number}' => $d['usuprequest_reference'],
            '{new_request_status}' => $statusArr[$d['usuprequest_status']],
            '{request_comments}' => $supplierRequestComments,
        );

        if (!self::sendMailTpl($d['credential_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $phone = !empty($d['user_phone']) ? $d['user_dial_code'] . $d['user_phone'] : '';
        $this->sendSms($tpl, $phone, $vars, $langId);
        return true;
    }

    public function sendCatalogRequestStatusChangeNotification($langId, $d)
    {
        $tpl = 'seller_catalog_request_status_change';

        $catalogRequestComments = '';
        if ($d['scatrequest_comments'] != '') {
            $catalogRequestComments = nl2br($d['scatrequest_comments']);
        }

        $statusArr = User::getCatalogReqStatusArr($langId);

        $vars = array(
            '{shop_name}' => $d['shop_name'],
            '{reference_number}' => $d['scatrequest_reference'],
            '{new_request_status}' => $statusArr[$d['scatrequest_status']],
            '{request_comments}' => $catalogRequestComments,
        );

        $receipentsInfo = User::getSubUsersReceipents($d['user_id'], 'canViewProducts');
        $bccEmails = $receipentsInfo['email'];
        if (!self::sendMailTpl($d['credential_email'], $tpl, $langId, $vars, '', 0, array(), $bccEmails)) {
            return false;
        }

        $phoneNumbers = $receipentsInfo['phone'];
        $phoneNumbers[] = !empty($d['user_phone']) ? $d['user_dial_code'] . $d['user_phone'] : '';
        foreach ($phoneNumbers as $phone) {
            $this->sendSms($tpl, $phone, $vars, $langId);
        }
        return true;
    }

    public function sendBrandRequestStatusChangeNotification($langId, $data)
    {
        $brandRequestComments = '';
        if (isset($data['brand_comments']) != '') {
            $brandRequestComments = nl2br($data['brand_comments']);
        }
        $userObj = new User($data['brand_seller_id']);
        $userInfo = $userObj->getSellerData($langId, array('user_id', 'ifnull(shop_name, shop_identifier) as shop_name', 'user_dial_code', 'user_phone', 'credential_email'));
        $statusArr = Brand::getBrandReqStatusArr($langId);

        $vars = array(
            '{shop_name}' => $userInfo['shop_name'],
            '{new_request_status}' => $statusArr[$data['brand_status']],
            '{brand_name}' => $data['brand_name'],
            '{brand_request_comments}' => '',
        );
        if (!empty($brandRequestComments)) {
            $tpl = new FatTemplate('', '');
            $tpl->set('brandRequestComments', $brandRequestComments);
            $requestCommentTableFormatHtml = $tpl->render(false, false, '_partial/emails/brand-request-comment-email.php', true);
            $vars["{brand_request_comments}"] = $requestCommentTableFormatHtml;
        }

        $receipentsInfo = User::getSubUsersReceipents($userInfo['user_id'], 'canViewProducts');

        $bccEmails = $receipentsInfo['email'];
        $phoneNumbers = $receipentsInfo['phone'];

        if (!self::sendMailTpl($userInfo['credential_email'], 'seller_brand_request_status_change', $langId, $vars, '', 0, array(), $bccEmails)) {
            return false;
        }
        $userPhone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
        $phoneNumbers[] = $userPhone;
        foreach ($phoneNumbers as $phone) {
            $this->sendSms('seller_brand_request_status_change', $phone, $vars, $langId);
        }

        return true;
    }

    public function sendCustomCatalogRequestStatusChangeNotification($langId, $d)
    {
        $tpl = 'seller_custom_catalog_request_status_change';

        $catalogRequestComments = '';
        if ($d['preq_comment'] != '') {
            $catalogRequestComments = nl2br($d['preq_comment']);
        } else {
            $catalogRequestComments = '{new_request_status} ' . Labels::getLabel('LBL_By_admin', $langId);
        }

        $statusArr = ProductRequest::getStatusArr($langId);

        $vars = array(
            '{shop_name}' => $d['shop_name'],
            '{request_comments}' => $catalogRequestComments,
            '{new_request_status}' => $statusArr[$d['preq_status']],
            '{prod_title}' => isset($d['preq_content']) ? (json_decode($d['preq_content'], true))['product_identifier'] : '',
        );

        $receipentsInfo = User::getSubUsersReceipents($d['preq_user_id'], 'canViewProducts');
        $bccEmails = $receipentsInfo['email'];
        if (!self::sendMailTpl($d['credential_email'], $tpl, $langId, $vars, '', 0, array(), $bccEmails)) {
            return false;
        }
        $phoneNumbers = $receipentsInfo['phone'];
        $userPhone = !empty($d['user_phone']) ? $d['user_dial_code'] . $d['user_phone'] : '';
        $phoneNumbers[] = $userPhone;
        foreach ($phoneNumbers as $phone) {
            $this->sendSms($tpl, $phone, $vars, $langId);
        }
        return true;
    }

    public function sendBrandRequestAdminNotification($langId, $data)
    {
        $tpl = 'seller_brand_request_admin_email';


        $userObj = new User($data['brand_seller_id']);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_dial_code', 'user_phone', 'credential_email'));
        $statusArr = Brand::getBrandReqStatusArr($langId);

        $vars = array(
            '{user_full_name}' => $userInfo['user_name'],
            '{brand_name}' => $data['brand_identifier'],
        );
        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }
        $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
        $this->sendSms($tpl, $userInfo['user_phone'], $vars, $langId);
        return true;
    }

    public function sendCategoryRequestAdminNotification($langId, $data)
    {
        $tpl = 'seller_category_request_admin_email';


        $userObj = new User($data['prodcat_seller_id']);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_dial_code', 'user_phone', 'credential_email'));

        $vars = array(
            '{user_full_name}' => $userInfo['user_name'],
            '{prodcat_name}' => $data['prodcat_identifier'],
        );
        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }
        $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
        $this->sendSms($tpl, $userInfo['user_phone'], $vars, $langId);
        return true;
    }

    public function sendContactRequestEmailToAdmin($langId, &$d)
    {
        $tpl = 'tpl_contact_request_received';
        $vars = array(
            '{requests_link}' => $d['link'],
        );

        $to = FatApp::getConfig('CONF_CONTACT_TO_EMAIL', FatUtility::VAR_STRING, '');
        if (strlen(trim($to)) < 1) {
            return $this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::ONLY_SUPER_ADMIN, $langId);
        }
        if (!self::sendMailTpl($to, $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $vars, $langId);
        return true;
    }

    public function sendContactFormEmail($to, $langId, $d)
    {
        $tpl = 'contact_us';
        $vars = array(
            '{name}' => $d['name'],
            '{email_address}' => $d['email'],
            '{phone_number}' => $d['phone'],
            '{message}' => nl2br($d['message'])
        );
        if (!self::sendMailTpl($to, $tpl, $langId, $vars)) {
            return false;
        }
        $d['phone'] = isset($d['phone']) ? $d['phone'] : '';
        $this->sendSms($tpl, $d['phone'], $vars, $langId);
        return true;
    }

    public function newOrderBuyerAdmin($order_id, $langId = 0, $includeAdmin = true, $pushNotification = true)
    {
        if ($order_id == '') {
            trigger_error(Labels::getLabel("MSG_Order_Id_not_specified", $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);

        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified', $this->commonLangId), E_USER_ERROR);
        }
        $orderObj = new Orders();
        $orderInfo = $orderObj->getOrderById($order_id, $langId);
        
        if ($orderInfo) {
            $order_discount_coupon = $orderInfo['order_discount_coupon_code'] != "" ? $orderInfo['order_discount_coupon_code'] : Labels::getLabel("LBL_-NA-", $langId);

            $orderProducts = $orderObj->getChildOrders(array('order_id' => $orderInfo['order_id']), $orderInfo['order_type'], $orderInfo['order_language_id'], true);
            $addresses = $orderObj->getOrderAddresses($orderInfo["order_id"]);

            $userObj = new User($orderInfo["order_user_id"]);
            $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));

            $billingArr = array();
            if (!empty($addresses[Orders::BILLING_ADDRESS_TYPE])) {
                $billingArr = $addresses[Orders::BILLING_ADDRESS_TYPE];
            }

            $shippingArr = array();
            if (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) {
                $shippingArr = $addresses[Orders::SHIPPING_ADDRESS_TYPE];
            }
            
            $addonProductArr = []; // 
            $orderProductsData = [];
            foreach ($orderProducts as $opID => $val) {
                $opChargesLog = new OrderProductChargeLog($opID);
                $taxOptions = $opChargesLog->getData($langId);
                $orderProducts[$opID]['taxOptions'] = $taxOptions;
                
                if ($val['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                    $addonProductArr[$val['op_attached_op_id']][] = $orderProducts[$opID];
                    unset($orderProducts[$opID]);
                } else {
                    $orderProductsData[$val["opshipping_pickup_addr_id"]][] = $val;
                    $pickUpAddress = $orderObj->getOrderAddresses($orderInfo['order_id'], $opID);
                    if (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) {
                        $orderProductsData[$val["opshipping_pickup_addr_id"]]['pickupAddress'] = $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE];
                    } else {
                        $orderProductsData[$val["opshipping_pickup_addr_id"]]['pickupAddress'] = array();
                    }
                }
            }
            
            /* foreach ($orderProducts as $opID => $val) {
                $orderProductsData[$val["opshipping_pickup_addr_id"]][] = $val;
                $pickUpAddress = $orderObj->getOrderAddresses($orderInfo['order_id'], $opID);
                if (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) {
                    $orderProductsData[$val["opshipping_pickup_addr_id"]]['pickupAddress'] = $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE];
                } else {
                    $orderProductsData[$val["opshipping_pickup_addr_id"]]['pickupAddress'] = array();
                }
            } */
            
            $tpl = new FatTemplate('', '');
            $tpl->set('orderInfo', $orderInfo);
            // $tpl->set('orderProducts', $orderProducts);
            $tpl->set('orderProductsData', $orderProductsData);
            $tpl->set('addonProductArr', $addonProductArr);
            $tpl->set('siteLangId', $langId);
            $tpl->set('billingAddress', $billingArr);
            $tpl->set('shippingAddress', $shippingArr);

            $order_products_table_format = $tpl->render(false, false, '_partial/emails/order-detail-email.php', true); 
            $arrReplacements = array(
                '{user_full_name}' => trim($userInfo['user_name']),
                '{order_invoice_number}' => $orderInfo['order_id'],
                //'{reference_number}' => $orderInfo['order_id'],
                //'{company_name}' => $orderInfo['order_company_name'],
                '{order_date}' => FatDate::format($orderInfo["order_date_added"], true),
                '{shipping_method}' => $orderInfo['order_shippingapi_name'],
                '{discount_coupon}' => CommonHelper::displayNotApplicable($langId, $orderInfo['order_discount_coupon_code']),
                '{coupon_discount}' => CommonHelper::displayMoneyFormat($orderInfo['order_discount_total'], true, true),
                //'{payment_method}' => $orderInfo['order_payment_method'],
                //'{order_cart_total}' => CommonHelper::displayMoneyFormat($orderInfo['order_cart_total']),
                //'{shipping}' => CommonHelper::displayMoneyFormat($orderInfo['order_shipping_charged']),
                //'{payment_fees}' => CommonHelper::displayMoneyFormat($orderInfo['order_net_amount']),
                '{discount}' => CommonHelper::displayMoneyFormat($orderInfo['order_discount_total'], true, true),
                //'{sub_order_total}' => CommonHelper::displayMoneyFormat($orderInfo['order_sub_total']),
                //'{tax_vat}' => CommonHelper::displayMoneyFormat($orderInfo['order_tax_charged']),
                '{total_paid}' => CommonHelper::displayMoneyFormat($orderInfo['order_net_amount'], true, true),
                //'{order_credits_used}' => CommonHelper::displayMoneyFormat($orderInfo['order_credits_charged']),
                //'{order_payment_made}' => CommonHelper::displayMoneyFormat($orderInfo['order_actual_paid']),
                //'{discount_code}' => CommonHelper::displayNotApplicable($langId, $orderInfo['order_discount_coupon_code']),
                '{order_products_table_format}' => $order_products_table_format,
            );

            if ($includeAdmin && FatApp::getConfig('CONF_NEW_ORDER_EMAIL', FatUtility::VAR_INT, 1)) {
                $this->sendMailToAdminAndAdditionalEmails("admin_order_email", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
                $this->sendSms("admin_order_email", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);
            }
            self::sendMailTpl($userInfo['credential_email'], "customer_order_email", $langId, $arrReplacements);
            $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
            $this->sendSms("customer_order_email", $phone, $arrReplacements, $langId);

            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $orderInfo["order_user_id"],
                'unotification_body' => CommonHelper::replaceStringData(Labels::getLabel('APP_YOUR_ORDER_{ORDERID}_HAVE_BEEN_PLACE', $langId), array('{ORDERID}' => $orderInfo['order_id'])),
                'unotification_type' => 'BUYER_ORDER',
                'unotification_data' => json_encode(array('orderId' => $orderInfo['order_id'])),
            );
            if (!$notificationObj->addNotification($notificationDataArr, $pushNotification)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }
        return true;
    }

    /* public function newDigitalOrderBuyer($orderId = 0, $opId = 0, $langId = 0)
    {
        if ($opId == '') {
            trigger_error(Labels::getLabel("MSG_OP_Id_not_specified", $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);

        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified', $this->commonLangId), E_USER_ERROR);
        }
        $orderObj = new Orders();
        $OrderInfo = $orderObj->getOrderById($orderId, $langId);
        $childOrderInfo = $orderObj->getOrderProductsByOpId($opId, $langId);

        $opChargesLog = new OrderProductChargeLog($opId);
        $taxOptions = $opChargesLog->getData($langId);
        $childOrderInfo['taxOptions'] = $taxOptions;

        //CommonHelper::printArray($childOrderInfo, true);
        if ($childOrderInfo) {
            $userObj = new User($OrderInfo["order_user_id"]);
            $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));
            $tpl = new FatTemplate('', '');
            $tpl->set('orderInfo', $OrderInfo);
            $tpl->set('orderProducts', $childOrderInfo);
            $tpl->set('siteLangId', $langId);
            $orderItemsTableFormatHtml = $tpl->render(false, false, '_partial/emails/child-order-detail-email.php', true);
            $arrReplacements = array(
                '{user_full_name}' => trim($userInfo['user_name']),
                '{order_items_table_format}' => $orderItemsTableFormatHtml,
                '{order_id}' => $orderId,
            );

            self::sendMailTpl($userInfo['credential_email'], "customer_digital_order_email", $langId, $arrReplacements);
            $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
            $this->sendSms("customer_digital_order_email", $phone, $arrReplacements, $langId);
        }
        return true;
    } */

    public function orderPaymentUpdateBuyerAdmin($orderId)
    {
        $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $orderObj = new Orders();
        $orderDetail = $orderObj->getOrderById($orderId);

        $userObj = new User($orderDetail["order_user_id"]);
        $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));

        $payementStatusArr = Orders::getOrderPaymentStatusArr($langId);

        if ($orderDetail) {
            $arrReplacements = array(
                '{user_full_name}' => trim($userInfo['user_name']),
                '{invoice_number}' => $orderDetail['order_id'],
                '{new_order_status}' => $payementStatusArr[$orderDetail['order_payment_status']],
            );

            $this->sendMailToAdminAndAdditionalEmails("primary_order_payment_status_change_admin", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
            //$this->sendSms("primary_order_payment_status_change_admin", FatApp::getConfig('CONF_SITE_PHONE'), $arrReplacements, $langId);// NEED TO CHECK

            $notiArrReplacements = array(
                '{ORDERID}' => $arrReplacements['{invoice_number}'],
                '{STATUS}' => $arrReplacements['{new_order_status}']
            );
            $appNotification = CommonHelper::replaceStringData(Labels::getLabel('APP_PAYMENT_STATUS_FOR_ORDER_{ORDERID}_UPDATED_{STATUS}', $langId), $notiArrReplacements);

            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $orderDetail["order_user_id"],
                'unotification_body' => $appNotification,
                'unotification_type' => 'ORDER_PAYMENT_STATUS',
                'unotification_data' => json_encode(array('orderId' => $arrReplacements['{invoice_number}'], 'status' => $arrReplacements['{new_order_status}'])),
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
            self::sendMailTpl($userInfo["credential_email"], "primary_order_payment_status_change_buyer", $orderDetail['order_language_id'], $arrReplacements);
            $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
            //$this->sendSms("primary_order_payment_status_change_buyer", $phone, $arrReplacements, $orderDetail['order_language_id']); // NEED TO CHECK
        }
        return true;
    }

    public function cashOnDeliveryOrderUpdateBuyerAdmin($orderId, $langId = 0)
    {
        $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $langId = FatUtility::int($langId);
        $orderObj = new Orders();
        $orderDetail = $orderObj->getOrderById($orderId, $langId);

        if (1 > $langId) {
            $langId = $orderDetail['order_language_id'];
        }

        $userObj = new User($orderDetail["order_user_id"]);
        $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));
        // $payementStatusArr = Orders::getOrderPaymentStatusArr($langId);

        if ($orderDetail) {
            $arrReplacements = array(
                '{user_full_name}' => trim($userInfo['user_name']),
                '{invoice_number}' => $orderDetail['order_id'],
                '{order_payment_method}' => (isset($orderDetail['plugin_name']) && !empty($orderDetail['plugin_name'])) ? $orderDetail['plugin_name'] : $orderDetail['plugin_identifier'],
            );

            $this->sendMailToAdminAndAdditionalEmails("primary_order_payment_status_admin", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
            $this->sendSms("primary_order_payment_status_admin", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);

            self::sendMailTpl($userInfo["credential_email"], "primary_order_payment_status_buyer", $orderDetail['order_language_id'], $arrReplacements);
            $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
            $this->sendSms("primary_order_payment_status_buyer", $phone, $arrReplacements, $orderDetail['order_language_id']);
        }
        return true;
    }

    public function bankTranferOrderUpdateBuyerAdmin($orderId, $langId = 0)
    {
        $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $langId = FatUtility::int($langId);
        $orderObj = new Orders();
        $orderDetail = $orderObj->getOrderById($orderId, $langId);

        if (1 > $langId) {
            $langId = $orderDetail['order_language_id'];
        }

        $userObj = new User($orderDetail["order_user_id"]);
        $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));

        $payementStatusArr = Orders::getOrderPaymentStatusArr($langId);

        if ($orderDetail) {
            $arrReplacements = array(
                '{user_full_name}' => trim($userInfo['user_name']),
                '{invoice_number}' => $orderDetail['order_id'],
                '{order_payment_method}' => !empty($orderDetail['plugin_name']) ? $orderDetail['plugin_name'] : $orderDetail['plugin_identifier'],
            );

            $this->sendMailToAdminAndAdditionalEmails("primary_order_bank_transfer_payment_status_admin", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
            $this->sendSms("primary_order_bank_transfer_payment_status_admin", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);

            self::sendMailTpl($userInfo["credential_email"], "primary_order_bank_transfer_payment_status_buyer", $orderDetail['order_language_id'], $arrReplacements);
            $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
            $this->sendSms("primary_order_bank_transfer_payment_status_buyer", $phone, $arrReplacements, $orderDetail['order_language_id']);
        }
        return true;
    }

    public function sendProductStockAlert($selprod_id, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        }

        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = sp.selprod_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'c.credential_user_id = u.user_id', 'c');
        $srch->addCondition('selprod_id', '= ', $selprod_id);
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', Shop::DB_TBL_PREFIX . 'user_id = u.user_id', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = ' . $langId, 's_l');

        $srch->addMultipleFields(array('selprod_title', 'selprod_product_id', 'user_id', 'user_name', 'user_dial_code', 'user_phone', 'credential_email', 'ifnull(shop_name, shop_identifier) as shop_name'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        if (!$rs) {
            return false;
        }

        $productInfo = FatApp::getDb()->fetch($rs);

        if (empty($productInfo)) {
            return false;
        }

        $frontEndUrl = (CONF_WEBROOT_FRONT_URL) ? CONF_WEBROOT_FRONT_URL : CONF_WEBROOT_URL;
        $url = UrlHelper::generateFullUrl('sellerInventories', 'products', array(), $frontEndUrl);
        $productAnchor = "<a href='" . $url . "'>" . Labels::getLabel('LBL_click_here', $langId) . "</a>";

        $arrReplacements = array(
            '{shop_name}' => $productInfo['shop_name'],
            '{prod_title}' => $productInfo["selprod_title"],
            '{click_here}' => $productAnchor,
        );

        $receipentsInfo = User::getSubUsersReceipents($productInfo['user_id'], 'canViewProducts');
        $bccEmails = $receipentsInfo['email'];
        self::sendMailTpl($productInfo["credential_email"], "threshold_notification_vendor", $langId, $arrReplacements, '', 0, array(), $bccEmails);

        $phoneNumbers = $receipentsInfo['phone'];
        $userPhone = !empty($productInfo['user_phone']) ? $productInfo['user_dial_code'] . $productInfo['user_phone'] : '';
        $phoneNumbers[] = $userPhone;
        foreach ($phoneNumbers as $phone) {
            $this->sendSms("threshold_notification_vendor", $phone, $arrReplacements, $langId);
        }
        return true;
    }

    public function newOrderVendor($orderId, $langId = 0, $paymentType = '')
    {
        $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $orderObj = new Orders();
        $orderDetail = $orderObj->getOrderById($orderId);
        if (1 > $langId) {
            $langId = $orderDetail['order_language_id'];
        }
        if ($orderDetail) {
            $addresses = $orderObj->getOrderAddresses($orderId);

            $billingArr = array();
            if (!empty($addresses[Orders::BILLING_ADDRESS_TYPE])) {
                $billingArr = $addresses[Orders::BILLING_ADDRESS_TYPE];
            }

            $shippingArr = array();
            if (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) {
                $shippingArr = $addresses[Orders::SHIPPING_ADDRESS_TYPE];
            } /*else {
                $shippingArr = $billingArr;
            }*/
            $orderVendors = $orderObj->getChildOrders(array("order" => $orderId), $orderDetail['order_type'], $orderDetail['order_language_id']);
            $groupShopOrdersArr = [];
            $addonProductArr = [];
            if (!empty($orderVendors)) {
                foreach ($orderVendors as $key => $val) {
                    $shippingHanldedBySeller = CommonHelper::canAvailShippingChargesBySeller($val['op_selprod_user_id'], $val['opshipping_by_seller_user_id']);
                    $opChargesLog = new OrderProductChargeLog($val['op_id']);
                    $taxOptions = $opChargesLog->getData($langId);
                    $val['taxOptions'] = $taxOptions;
       
                    $pickUpAddress = $orderObj->getOrderAddresses($orderId, $val['op_id']);
                    if (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) {
                        $val['pickupAddress'] = $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE];
                    } else {
                        $val['pickupAddress'] = array();
                    }
                    $val['is_shipping_by_seller'] = FatUtility::int($shippingHanldedBySeller);
                    if ($val['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                        $addonProductArr[$val['op_attached_op_id']][] = $val;
                    } else {
                        $groupShopOrdersArr[$val['op_shop_id']][] = $val;
                    }
                }
            }
            
            foreach ($groupShopOrdersArr as $shopId => $val) {
                $tpl = new FatTemplate('', '');
                $tpl->set('orderProducts', $val);
                $tpl->set('siteLangId', $langId);
                $tpl->set('userType', User::USER_TYPE_SELLER);
                $tpl->set('billingAddress', $billingArr);
                $tpl->set('shippingAddress', $shippingArr);
                $tpl->set('addonProductArr', $addonProductArr);
                $tpl->set('orderDetail', $orderDetail);
                
                $orderItemsTableFormatHtml = $tpl->render(false, false, '_partial/emails/child-order-detail-email-seller-test.php', true);
                $userObj = new User($orderDetail["order_user_id"]);
                $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));
                $arrReplacements = array(
                    '{vendor_name}' => trim($val[0]['op_shop_owner_name']),
                    '{order_items_table_format}' => $orderItemsTableFormatHtml,
                    '{order_shipping_information}' => '',
                    '{order_user_email}' => $userInfo['credential_email'],
                    '{order_id}' => $orderId,
                );

                if (!empty($paymentType) && in_array(strtolower($paymentType), ['cashondelivery', 'payatstore'])) {
                    $tpl = "vendor_cod_order_email";
                } else if (!empty($paymentType) && strtolower($paymentType) == 'transferbank') {
                    $tpl = "vendor_bank_transfer_order_email";
                } else {
                    $tpl = "vendor_order_email";
                }

                $receipentsInfo = User::getSubUsersReceipents($val[0]['op_selprod_user_id'], 'canViewSales');
                $bccEmails = $receipentsInfo['email'];
                self::sendMailTpl($val[0]["op_shop_owner_email"], $tpl, $langId, $arrReplacements, '', 0, array(), $bccEmails);

                if (!in_array(strtolower($paymentType), ['cashondelivery', 'payatstore', 'transferbank'])) {
                    $sellerInfo = User::getAttributesById($val[0]['op_selprod_user_id'], array('user_dial_code', 'user_phone'));
                    $sellerPhone = !empty($sellerInfo['user_phone']) ? $sellerInfo['user_dial_code'] . $sellerInfo['user_phone'] : '';
                    $this->sendSms($tpl, $sellerPhone, $arrReplacements, $langId);
                }
                $productNames = array_column($val, 'op_product_name');
                $notiArrReplacements = array(
                    '{PRODUCT}' => /* $val["op_product_name"] */ implode(', ', $productNames),
                    '{ORDERID}' => $orderDetail['order_id']
                );

                $appNotification = CommonHelper::replaceStringData(Labels::getLabel('INV_{PRODUCT}_ORDER_{ORDERID}_HAS_BEEN_PLACED', $langId), $notiArrReplacements);

                $notificationObj = new Notifications();
                $notificationDataArr = array(
                    'unotification_user_id' => $val[0]["op_selprod_user_id"],
                    'unotification_body' => $appNotification,
                    'unotification_type' => 'SELLER_ORDER',
                    'unotification_data' => json_encode(array('orderId' => $orderDetail['order_id'], 'productName' => /* $val["op_product_name"] */ implode(', ', $productNames))),
                );
                if (!$notificationObj->addNotification($notificationDataArr)) {
                    $this->error = $notificationObj->getError();
                    return false;
                }
            }
        }
        return true;
    }

    public function orderStatusUpdateBuyer($commentId, $langId, $buyerId = 0)
    {
        $langId = FatUtility::int($langId);
        $buyerId = FatUtility::int($buyerId);

        $orderObj = new Orders();
        $orderComment = $orderObj->getOrderComments($langId, array("id" => $commentId, "buyer_id" => $buyerId), 1); /*1 no of records*/

        if ($orderComment && $orderComment["oshistory_customer_notified"]) {
            $msgComments = '';

            if ($orderComment['oshistory_comments'] != "") {
                $msgComments = Labels::getLabel('MSG_Comments_for_your_order', $langId) . ":<br/><br/><em>" . $orderComment['oshistory_comments'] . ".</em><br/><br/>";
            }
            $shipmentInformation = '';
            if ($orderComment['oshistory_tracking_number'] != "") {
                // $shipmentInformation = Labels::getLabel('MSG_Shipment_Information', $langId) . ": " . Labels::getLabel('MSG_Tracking_Number', $langId) . " " . $orderComment['oshistory_tracking_number'] . " " . Labels::getLabel('LBL_Via', $langId) . " " . $orderComment["op_shipping_duration_name"] . "<br/>";
                $shipmentInformation = Labels::getLabel('MSG_Shipment_Information', $langId) . ": " . Labels::getLabel('MSG_Tracking_Number', $langId) . " " . $orderComment['oshistory_tracking_number'] . " " . Labels::getLabel('LBL_Via', $langId) . " " . $orderComment["op_shipping_duration_name"];
                if(!empty($orderComment['oshistory_tracking_url'])){
                    $shipmentInformation.=' <a href="'.$orderComment['oshistory_tracking_url'].'" target="_blank">'.$orderComment['oshistory_tracking_url'].'</a>';
                }                
                $shipmentInformation.="<br>"; 
            }

            $charges = $orderObj->getOrderProductChargesArr($orderComment['op_id']);
            $orderComment['charges'] = $charges;

            $opChargesLog = new OrderProductChargeLog($orderComment['op_id']);
            $taxOptions = $opChargesLog->getData($langId);
            $orderComment['taxOptions'] = $taxOptions;

            $tpl = new FatTemplate('', '');
            $tpl->set('orderProducts', $orderComment);
            $tpl->set('siteLangId', $langId);
            $orderItemsTableFormatHtml = $tpl->render(false, false, '_partial/emails/child-order-detail-email.php', true);
            $statuesArr = Orders::getOrderProductStatusArr($orderComment["order_language_id"]);
            if ($orderComment["oshistory_orderstatus_id"] == OrderStatus::ORDER_DELIVERED && $orderComment['opshipping_fulfillment_type'] == Shipping::FULFILMENT_PICKUP) {
                $statuesArr[$orderComment["oshistory_orderstatus_id"]] = Labels::getLabel('LBL_Picked', $langId);
            }
            
            $arrReplacements = array(
                '{user_full_name}' => trim($orderComment["buyer_name"]),
                '{new_order_status}' => $statuesArr[$orderComment["oshistory_orderstatus_id"]],
                '{invoice_number}' => $orderComment["op_invoice_number"],
                '{order_items_table_format}' => $orderItemsTableFormatHtml,
                '{order_admin_comments}' => nl2br($msgComments),
                '{shipment_information}' => "<br/><br/>" . $shipmentInformation,
            );
            self::sendMailTpl($orderComment["buyer_email"], "child_order_status_change", $langId, $arrReplacements);
            $this->sendSms("child_order_status_change", $orderComment["buyer_phone"], $arrReplacements, $langId);
            $replaceVal = array(
                '{INVOICE}' => $orderComment["op_invoice_number"],
                '{PRODUCT}' => $orderComment["op_product_name"],
                '{STATUS}' => $statuesArr[$orderComment["oshistory_orderstatus_id"]]
            );
            $appNotification = CommonHelper::replaceStringData(Labels::getLabel('APP_YOUR_ORDER_{INVOICE}_{PRODUCT}_STATUS_{STATUS}', $langId), $replaceVal, true);

            $notificationData = array(
                'invoiceNumber' => $orderComment["op_invoice_number"],
                'productName' => $orderComment["op_product_name"],
                'status' => $statuesArr[$orderComment["oshistory_orderstatus_id"]],
                'orderId' => $orderComment["op_order_id"],
                'orderProductId' => $orderComment["op_id"],
            );

            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $buyerId,
                'unotification_body' => $appNotification,
                'unotification_type' => 'BUYER_ORDER_STATUS',
                'unotification_data' => json_encode($notificationData),
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }

            return true;
        } else {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
        }
    }

    public function orderStatusUpdateSeller($commentId, $langId, $sellerId = 0)
    {
        $langId = FatUtility::int($langId);
        $sellerId = FatUtility::int($sellerId);

        $orderObj = new Orders();
        $orderComment = $orderObj->getOrderComments($langId, array("id" => $commentId, "seller_id" => $sellerId), 1);  //1 no of records

        if ($orderComment && $orderComment["oshistory_customer_notified"]) {
            $msgComments = '';

            if ($orderComment['oshistory_comments'] != "") {
                $msgComments = Labels::getLabel('MSG_Comments_for_your_order', $langId) . ":<br/><br/><em>" . $orderComment['oshistory_comments'] . ".</em><br/><br/>";
            }
            $shipmentInformation = '';
            if ($orderComment['oshistory_tracking_number'] != "") {
                $shipmentInformation = Labels::getLabel('MSG_Shipment_Information', $langId) . ": " . Labels::getLabel('MSG_Tracking_Number', $langId) . " " . $orderComment['oshistory_tracking_number'] . " " . Labels::getLabel('LBL_Via', $langId) . " " . $orderComment["op_shipping_duration_name"] . "<br/>";
            }

            $charges = $orderObj->getOrderProductChargesArr($orderComment['op_id']);
            $orderComment['charges'] = $charges;

            $opChargesLog = new OrderProductChargeLog($orderComment['op_id']);
            $taxOptions = $opChargesLog->getData($langId);
            $orderComment['taxOptions'] = $taxOptions;

            $shippingHanldedBySeller = CommonHelper::canAvailShippingChargesBySeller($orderComment['op_selprod_user_id'], $orderComment['opshipping_by_seller_user_id']);

            $tpl = new FatTemplate('', '');
            $tpl->set('orderProducts', $orderComment);
            $tpl->set('siteLangId', $langId);
            $tpl->set('shippingHanldedBySeller', $shippingHanldedBySeller);
            $tpl->set('userType', User::USER_TYPE_SELLER);
            $orderItemsTableFormatHtml = $tpl->render(false, false, '_partial/emails/child-order-detail-email-seller.php', true);
            $statuesArr = Orders::getOrderProductStatusArr($orderComment["order_language_id"]);

            $arrReplacements = array(
                '{user_full_name}' => trim($orderComment["seller_name"]),
                '{new_order_status}' => $statuesArr[$orderComment["oshistory_orderstatus_id"]],
                '{invoice_number}' => $orderComment["op_invoice_number"],
                '{order_items_table_format}' => $orderItemsTableFormatHtml,
                '{order_admin_comments}' => nl2br($msgComments),
                '{shipment_information}' => "<br/><br/>" . $shipmentInformation,
            );
            self::sendMailTpl($orderComment["seller_email"], "child_order_status_change", $langId, $arrReplacements);
            $this->sendSms("child_order_status_change", $orderComment["seller_phone"], $arrReplacements, $langId);
            return true;
        } else {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
        }
    }

    public function sendTaxApiOrderCreationFailure($data, $langId)
    {
        $adminEmail = FatApp::getConfig("CONF_SITE_OWNER_EMAIL", FatUtility::VAR_STRING, "");
        $tpl = "taxapi_order_creation_failure";
        $defaultSiteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $arrReplacements = array(
            '{invoice_number}' => $data['op_invoice_number'],
            '{error_message}' => $data['op_invoerror_messageice_number']
        );

        if (self::sendMailTpl($adminEmail, $tpl, $langId, $vars)) {
            return true;
        }
        return false;
    }

    public function sendTxnNotification($txnId, $langId)
    {
        $langId = FatUtility::int($langId);
        $txn = new Transactions($txnId);

        $txnDetail = $txn->getAttributesWithUserInfo(0, array('utxn_credit', 'utxn_debit', 'utxn_comments', 'user_name', 'user_dial_code', 'user_phone', 'credential_email', 'utxn_user_id'));
        $statusArr = Transactions::getStatusArr($langId);

        $txnAmount = $txnDetail["utxn_credit"] > 0 ? $txnDetail["utxn_credit"] : $txnDetail["utxn_debit"];
        $arrReplacements = array(
            '{user_name}' => trim($txnDetail["user_name"]),
            '{txn_id}' => Transactions::formatTransactionNumber($txnId),
            '{txn_type}' => ($txnDetail["utxn_credit"] > 0) ? Labels::getLabel('LBL_credited', $langId) : Labels::getLabel('L_debited', $langId),
            '{txn_amount}' => CommonHelper::displayMoneyFormat($txnAmount, true, true),
            '{txn_comments}' => Transactions::formatTransactionComments($txnDetail["utxn_comments"]),
        );
        self::sendMailTpl($txnDetail["credential_email"], "account_credited_debited", $langId, $arrReplacements);
        $phone = !empty($txnDetail['user_phone']) ? $txnDetail['user_dial_code'] . $txnDetail['user_phone'] : '';
        $this->sendSms("account_credited_debited", $phone, $arrReplacements, $langId);
        $notiArrReplacements = array(
            '{txnid}' => Transactions::formatTransactionNumber($txnId),
            '{txntype}' => ($txnDetail["utxn_credit"] > 0) ? Labels::getLabel('LBL_credited', $langId) : Labels::getLabel('L_debited', $langId),
            '{txnamount}' => CommonHelper::displayMoneyFormat($txnAmount, true, true),
        );

        $appNotification = CommonHelper::replaceStringData(Labels::getLabel('APP_AMOUNT_{txnamount}_WITH_{txnid}_HAS_BEEN_{txntype}', $langId), $notiArrReplacements, true);

        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $txnDetail["utxn_user_id"],
            'unotification_body' => $appNotification,
            'unotification_type' => 'TXN',
            'unotification_data' => json_encode(array('txnAmount' => $arrReplacements['{txn_amount}'], 'txnId' => $arrReplacements['{txn_id}'], 'txnType' => $arrReplacements['{txn_type}'])),
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }

        return true;
    }

    public function sendWithdrawRequestNotification($requestId, $langId, $adminOrUser = "A")
    {
        $langId = FatUtility::int($langId);
        if (1 > $langId) {
            return 'ERR_Invalid_Lang';
        }
        $this->langId = $langId;

        $srch = new WithdrawalRequestsSearch();
        $srch->joinUsers(true);
        $srch->joinForUserBalance();
        $srch->joinTable(User::DB_TBL_USR_WITHDRAWAL_REQ_SPEC, 'LEFT JOIN', User::DB_TBL_USR_WITHDRAWAL_REQ_SPEC_PREFIX . 'withdrawal_id = tuwr.withdrawal_id');
        $srch->addMultipleFields(
            array(
                'tuwr.*', 'GROUP_CONCAT(CONCAT(`uwrs_key`, ":", `uwrs_value`)) as payout_detail', 'user_name', 'credential_email as user_email', 'credential_username as user_username', 'user_dial_code', 'user_phone'
            )
        );
        $srch->addCondition('tuwr.withdrawal_id', '=', $requestId);

        $rs = $srch->getResultSet();
        if (!$rs) {
            return 'ERR_Invalid_Access';
        }

        $withdrawalRequestData = FatApp::getDb()->fetch($rs);
        if (!$withdrawalRequestData) {
            return 'ERR_No_Record_Found';
        }

        $formattedRequestValue = "#" . str_pad($requestId, 6, '0', STR_PAD_LEFT);
        $url = UrlHelper::generateFullUrl('account', 'messages', array(), CONF_WEBROOT_URL);
        $url = '<a href="' . $url . '">' . Labels::getLabel('Msg_click_here', $langId) . '</a>';

        $statusArr = Transactions::getWithdrawlStatusArr($langId);
        
        $tpl = new FatTemplate('', '');
        $tpl->set('siteLangId', $langId);
        $tpl->set('data', $withdrawalRequestData);
        $withdrawalDetailsTableFormatHtml = $tpl->render(false, false, '_partial/emails/withdrawal-request-details-email.php', true);
        
        $arrReplacements = array(
            '{request_id}' => $formattedRequestValue,
            '{username}' => $withdrawalRequestData['user_username'],
            '{request_amount}' => CommonHelper::displayMoneyFormat($withdrawalRequestData["withdrawal_amount"], true, true),

            '{request_bank}' => $withdrawalRequestData['withdrawal_bank'],
            '{request_account_holder}' => $withdrawalRequestData['withdrawal_account_holder_name'],
            '{request_account_number}' => $withdrawalRequestData['withdrawal_account_number'],
            '{request_ifsc_swift_number}' => $withdrawalRequestData['withdrawal_ifc_swift_code'],
            '{request_bank_address}' => $withdrawalRequestData['withdrawal_bank_address'],
            '{request_comments}' => $withdrawalRequestData['withdrawal_instructions'],

            '{request_status}' => $statusArr[$withdrawalRequestData['withdrawal_status']],
            '{withdrawal_detail_table_format_html}' => $withdrawalDetailsTableFormatHtml,
            '{user_name}' => $withdrawalRequestData['user_name'],
        );

        if ($adminOrUser == "A") {
            $this->sendMailToAdminAndAdditionalEmails("withdrawal_request_admin", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);

            $tpl = 'withdrawal_request_admin';
            $phone = FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, "");
        } else {
            self::sendMailTpl($withdrawalRequestData["user_email"], "withdrawal_request_approved_declined", $langId, $arrReplacements);

            $tpl = 'withdrawal_request_approved_declined';
            $phone = !empty($withdrawalRequestData['user_phone']) ? $withdrawalRequestData['user_dial_code'] . $withdrawalRequestData['user_phone'] : '';
        }

        if (!empty($phone)) {
            $this->sendSms($tpl, $phone, $arrReplacements, $langId);
        }

        $notiArrReplacements = array(
            '{requestid}' => $formattedRequestValue,
            '{requestamount}' => CommonHelper::displayMoneyFormat($withdrawalRequestData["withdrawal_amount"], true, true),
            '{requeststatus}' => $statusArr[$withdrawalRequestData['withdrawal_status']],
        );
        $appNotification = CommonHelper::replaceStringData(Labels::getLabel('APP_AMOUNT_{requestamount}_WITH_{requestid}_HAS_BEEN_{requeststatus}', $langId), $notiArrReplacements, true);

        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $withdrawalRequestData["withdrawal_user_id"],
            'unotification_body' => $appNotification,
            'unotification_type' => 'FUNDS_WITHDRAWAL_REQUEST_CHANGED',
            'unotification_data' => json_encode(array('requestAmount' => $arrReplacements['{request_amount}'], 'requestId' => $arrReplacements['{request_id}'], 'requestStatus' => $arrReplacements['{request_status}'])),
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }

        return true;
    }

    public function sendMessageNotification($messageId, $langId)
    {
        $messageId = FatUtility::int($messageId);
        $langId = FatUtility::int($langId);

        $srch = new MessageSearch();
        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->addMultipleFields(array('tth.*', 'ttm.message_text', 'ttm.message_to'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        $srch->addCondition('ttm.message_id', '=', $messageId);
        $rs = $srch->getResultSet();
        $message = FatApp::getDb()->fetch($rs);
        if ($message == false || empty($message)) {
            return false;
        }

        $url = UrlHelper::generateFullUrl('account', 'viewMessages', array($message['thread_id'], $messageId), CONF_WEBROOT_FRONT_URL);

        $url = '<a href="' . $url . '">' . Labels::getLabel('LBL_click_here', $langId) . '</a>';

        $arrReplacements = array(
            '{user_full_name}' => $message['message_to_name'],
            '{username}' => $message['message_from_username'],
            '{message_subject}' => $message['thread_subject'] != "" ? $message['thread_subject'] : "-NA-",
            '{message}' => nl2br($message['message_text']),
            '{click_here}' => $url,
        );
        self::sendMailTpl($message["message_to_email"], "send_message", $langId, $arrReplacements);

        $uData = User::getAttributesById($message["message_to"], ['user_dial_code', 'user_phone']);
        if (!empty($uData)) {
            $this->sendSms("send_message", implode("", $uData), $arrReplacements, $langId);
        }

        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $message["message_to"],
            'unotification_body' => CommonHelper::replaceStringData(Labels::getLabel('APP_YOU_HAVE_A_NEW_MESSAGE_FROM_{username}', $langId), array('{username}' => $message['message_from_username'])),
            'unotification_type' => 'MESSAGE',
            'unotification_data' => json_encode(array('username' => $message['message_from_username'], 'threadId' => $message['thread_id'], 'messageId' => $messageId)),
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }

        return true;
    }

    public function sendOrderCancellationNotification($ocrequest_id, $langId)
    {
        $ocRequestSrch = new OrderCancelRequestSearch();
        $ocRequestSrch->doNotCalculateRecords();
        $ocRequestSrch->doNotLimitRecords();
        $ocRequestSrch->joinOrderProducts();
        $ocRequestSrch->joinOrderSellerUser();
        //$ocRequestSrch->joinShops();
        $ocRequestSrch->joinOrderCancelReasons($langId);
        $ocRequestSrch->addCondition('ocrequest_id', '=', $ocrequest_id);
        $ocRequestSrch->addMultipleFields(array('op_id', 'op_invoice_number', 'op_shop_owner_name', 'op_shop_owner_phone_code', 'op_shop_owner_phone', 'op_shop_owner_email', 'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'ocrequest_message', 'seller.user_id as seller_id'));
        $ocRequestRs = $ocRequestSrch->getResultSet();
        $ocRequestRow = FatApp::getDb()->fetch($ocRequestRs);
        if (!$ocRequestRow) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        $sellerOrderDetailUrl = UrlHelper::generateFullUrl('Seller', 'ViewOrder', array($ocRequestRow["op_id"]));
        $sellerOrderAnchor = "<a href='" . $sellerOrderDetailUrl . "'>" . $ocRequestRow["op_invoice_number"] . "</a>";

        $arrReplacements = array(
            '{user_name}' => $ocRequestRow['op_shop_owner_name'],
            '{invoice_number}' => $sellerOrderAnchor,
            '{cancel_reason}' => $ocRequestRow['ocreason_title'],
            '{cancel_comments}' => nl2br($ocRequestRow['ocrequest_message']),
        );
        $tpl = "order_cancellation_notification";

        $receipentsInfo = User::getSubUsersReceipents($ocRequestRow["seller_id"], 'canViewCancellationRequests');
        $bccEmails = $receipentsInfo['email'];
        self::sendMailTpl($ocRequestRow["op_shop_owner_email"], $tpl, $langId, $arrReplacements, '', 0, array(), $bccEmails);

        $phoneNumbers = $receipentsInfo['phone'];
        $phoneNumbers[] = $ocRequestRow["op_shop_owner_phone"];
        foreach ($phoneNumbers as $phone) {
            $this->sendSms($tpl, $phone, $arrReplacements, $langId);
        }

        $adminOrderDetailUrl = UrlHelper::generateFullUrl('SellerOrders', 'View', array($ocRequestRow["op_id"]), CONF_WEBROOT_BACKEND);
        $adminOrderAnchor = "<a href='" . $adminOrderDetailUrl . "'>" . $ocRequestRow["op_invoice_number"] . "</a>";
        $arrReplacements['{invoice_number}'] = $adminOrderAnchor;

        $arrReplacements["{user_name}"] = Labels::getLabel("LBL_Admin", $langId);

        $this->sendMailToAdminAndAdditionalEmails($tpl, $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);

        $appNotification = CommonHelper::replaceStringData(Labels::getLabel('INV_RECEIVED_CANCELLATION_FOR_INVOICE_{invoicenumber}', $langId), array('{invoicenumber}' => $sellerOrderAnchor), true);

        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $ocRequestRow["seller_id"],
            'unotification_body' => $appNotification,
            'unotification_type' => 'ORDER_CANCELLATION_REQUEST',
            'unotification_data' => json_encode(array('invoiceNumber' => $ocRequestRow["op_invoice_number"])),
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }

        return true;
    }

    public function sendOrderReturnRequestNotification($orrmsg_id, $langId)
    {
        $langId = FatUtility::int($langId);
        $orrmsg_id = FatUtility::int($orrmsg_id);

        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }

        if (!$orrmsg_id) {
            trigger_error(Labels::getLabel('MSG_Message_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }

        $srch = new OrderReturnRequestMessageSearch();
        $srch->joinOrderReturnRequests();
        $srch->joinOrderProducts($langId);
        $srch->joinOrders($langId);
        $srch->joinOrderBuyerUser();
        $srch->joinReturnReason($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('orrmsg_id', '=', $orrmsg_id);
        $srch->addMultipleFields(
            array(
                'op_selprod_id', 'op_selprod_user_id', 'op_is_batch', 'orrmsg_orrequest_id', 'op_product_name', 'op_selprod_title',
                'op_shop_owner_name', 'buyer_cred.credential_username as buyer_username', 'orrequest_qty', 'orrequest_type', 'orrequest_reference',
                'IFNULL(orreason_title, orreason_identifier) as orreason_title', 'orrmsg_msg', 'op_shop_owner_email', 'op_shop_owner_phone_code', 'op_shop_owner_phone',
                'op_selprod_options', 'op_brand_name', 'op_invoice_number', 'orrequest_user_id'
            )
        );
        $rs = $srch->getResultSet();
        if (!$msgDetail = FatApp::getDb()->fetch($rs)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        if ($msgDetail['op_is_batch']) {
            $productUrl = UrlHelper::generateFullUrl('Products', 'batch', array($msgDetail['op_selprod_id']));
        } else {
            $productUrl = UrlHelper::generateFullUrl('Products', 'view', array($msgDetail['op_selprod_id']));
        }

        $productTitle = ($msgDetail['op_selprod_title'] != '') ? $msgDetail['op_selprod_title'] . ' (' . $msgDetail['op_product_name'] . ')' : $msgDetail['op_product_name'];

        $productExtraDetails = '';
        if ($msgDetail['op_selprod_options'] != '') {
            $productExtraDetails .= '<br/>' . $msgDetail['op_selprod_options'];
        }

        if ($msgDetail['op_brand_name'] != '') {
            $productExtraDetails .= '<br/>' . Labels::getLabel('LBL_Brand', $langId) . ': ' . $msgDetail['op_brand_name'];
        }

        $returnRequestArr = OrderReturnRequest::getRequestTypeArr($langId);
        $returnRequestTypeName = $returnRequestArr[$msgDetail['orrequest_type']];

        $prodTitleAnchor = "<a href='" . $productUrl . "'>" . $productTitle . "</a>" . $productExtraDetails;

        $arrReplacements = array(
            '{user_name}' => $msgDetail['op_shop_owner_name'],
            '{username}' => $msgDetail['buyer_username'],
            '{child_order_invoice_number}' => $msgDetail['op_invoice_number'],
            '{return_prod_title}' => $prodTitleAnchor,
            '{return_request_id}' => $msgDetail['orrequest_reference'], /* CommonHelper::formatOrderReturnRequestNumber( $msgDetail['orrmsg_orrequest_id'] ), */
            '{return_qty}' => $msgDetail['orrequest_qty'],
            '{return_request_type}' => $returnRequestTypeName,
            '{return_reason}' => $msgDetail['orreason_title'],
            '{return_comments}' => nl2br($msgDetail['orrmsg_msg']),
        );

        $receipentsInfo = User::getSubUsersReceipents($msgDetail['op_selprod_user_id'], 'canViewReturnRequests');
        $bccEmails = $receipentsInfo['email'];
        self::sendMailTpl($msgDetail["op_shop_owner_email"], "product_return", $langId, $arrReplacements, '', 0, array(), $bccEmails);

        $phoneNumbers = $receipentsInfo['phone'];
        $phoneNumbers[] = $msgDetail["op_shop_owner_phone"];
        foreach ($phoneNumbers as $phone) {
            $this->sendSms("product_return", $phone, $arrReplacements, $langId);
        }
        /**** Notification For Seller ***********/

        $notiArrReplacements = array(
            '{username}' => $msgDetail['buyer_username'],
            '{returnrequestid}' => $msgDetail['orrequest_reference'],
        );

        $appNotification = CommonHelper::replaceStringData(Labels::getLabel('INV_RECEIVED_RETURN_FROM_{username}_WITH_REFERENCE_NUMBER_{returnrequestid}', $langId), $notiArrReplacements, true);

        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $msgDetail['op_selprod_user_id'],
            'unotification_body' => $appNotification,
            'unotification_type' => 'SELLER_RETURN_REQUEST',
            'unotification_data' => json_encode(array('username' => $msgDetail['buyer_username'], 'returnRequestId' => $arrReplacements['{return_request_id}'])),
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }
        /**** End Notification For Seller ***********/


        /**** Notification For Buyer ***********/
        $notiArrReplacements = array(
            '{returnprodtitle}' => $prodTitleAnchor,
            '{returnrequestid}' => $msgDetail['orrequest_reference'],
        );
        $appNotification = CommonHelper::replaceStringData(Labels::getLabel('APP_RETURN_FOR_{returnprodtitle}_with_{returnrequestid}_SUBMITTED', $langId), $notiArrReplacements, true);

        $notificationDataArr = array(
            'unotification_user_id' => $msgDetail['orrequest_user_id'],
            'unotification_body' => $appNotification,
            'unotification_type' => 'BUYER_RETURN_REQUEST',
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }
        /**** End Notification For Buyer ***********/


        $arrReplacements["{user_name}"] = Labels::getLabel("LBL_Admin", $langId);

        $this->sendMailToAdminAndAdditionalEmails("product_return", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
        $this->sendSms("product_return", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);
        return true;
    }

    public function sendReturnRequestMessageNotification($orrmsg_id, $langId)
    {
        $langId = FatUtility::int($langId);
        $orrmsg_id = FatUtility::int($orrmsg_id);
        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        if (!$orrmsg_id) {
            trigger_error(Labels::getLabel('MSG_Message_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }

        $srch = new OrderReturnRequestMessageSearch();
        $srch->joinOrderReturnRequests();
        $srch->joinOrderProducts($langId);
        $srch->joinOrders($langId);
        $srch->joinOrderBuyerUser();
        $srch->joinMessageAdmin();
        $srch->joinReturnReason($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('orrmsg_id', '=', $orrmsg_id);
        $srch->addMultipleFields(
            array(
                'op_selprod_id', 'op_is_batch', 'op_product_name', 'op_selprod_title',
                'op_shop_owner_name', 'op_shop_owner_username', 'op_shop_owner_email', 'op_shop_owner_phone_code', 'op_shop_owner_phone',  'op_selprod_user_id',
                'buyer_cred.credential_username as buyer_username', 'buyer_cred.credential_email as buyer_email',
                'orrequest_id', 'orrequest_qty', 'orrequest_reference', 'orrequest_type', 'orrequest_user_id', 'orrmsg_from_user_id',
                'IFNULL(orreason_title, orreason_identifier) as orreason_title',
                'orrmsg_msg', 'orrequest_status', 'buyer.user_name as buyer_name', 'CONCAT(buyer.user_dial_code, buyer.user_phone) as buyer_phone', 'buyer.user_id as buyer_id', 'op_selprod_user_id as seller_id',
                'orrmsg_from_admin_id', 'admin_name', 'admin_username'
            )
        );
        $rs = $srch->getResultSet();
        if (!$msgDetail = FatApp::getDb()->fetch($rs)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        $requestDetailUrl = UrlHelper::generateFullUrl('Buyer', 'ViewOrderReturnRequest', array($msgDetail['orrequest_id']));
        $requestDetailUrl = '<a href="' . $requestDetailUrl . '">' . Labels::getLabel('LBL_Click_here', $langId) . '</a>';

        /* Buyer Notification [ */
        $arrReplacements = array(
            '{username}' => FatApp::getConfig('CONF_WEBSITE_NAME_' . $langId, FatUtility::VAR_STRING, ""),
            '{request_number}' => $msgDetail["orrequest_reference"], /* CommonHelper::formatOrderReturnRequestNumber($msgDetail['orrequest_id']), */
            '{message}' => nl2br($msgDetail["orrmsg_msg"]),
            '{user_full_name}' => $msgDetail["buyer_name"],
            '{click_here}' => $requestDetailUrl,
            '{prod_title}' => $msgDetail['op_selprod_title'],
        );

        if ($msgDetail["orrequest_user_id"] != $msgDetail["orrmsg_from_user_id"]) {
            $arrReplacements["{user_full_name}"] = $msgDetail["buyer_name"];

            $arrReplacements["{username}"] = $msgDetail['op_shop_owner_name'];

            if ($msgDetail['orrmsg_from_admin_id']) {
                $arrReplacements["{username}"] = FatApp::getConfig('CONF_WEBSITE_NAME_' . $langId, FatUtility::VAR_STRING, "");
            }
            self::sendMailTpl($msgDetail["buyer_email"], "return_request_message_user", $langId, $arrReplacements);
            $this->sendSms("return_request_message_user", $msgDetail['buyer_phone'], $arrReplacements, $langId);
        }
        /* ] */


        /* Vendor Notification [ */
        if ($msgDetail["op_selprod_user_id"] != $msgDetail["orrmsg_from_user_id"]) {
            $arrReplacements["{user_full_name}"] = $msgDetail["op_shop_owner_name"];
            $arrReplacements["{username}"] = $msgDetail["buyer_username"];
            if ($msgDetail['orrmsg_from_admin_id']) {
                $arrReplacements["{username}"] = FatApp::getConfig('CONF_WEBSITE_NAME_' . $langId, FatUtility::VAR_STRING, "");
            }
            $requestDetailUrl = UrlHelper::generateFullUrl('Seller', 'ViewOrderReturnRequest', array($msgDetail['orrequest_id']));
            $requestDetailUrl = '<a href="' . $requestDetailUrl . '">' . Labels::getLabel('LBL_Click_here', $langId) . '</a>';
            $arrReplacements['{click_here}'] = $requestDetailUrl;
            /* if ($return_request['refmsg_from_type']=="U"){
            $arr_replacements["{username}"] = $return_request["message_sent_by_username"];
            } */

            $receipentsInfo = User::getSubUsersReceipents($msgDetail["seller_id"], 'canViewReturnRequests');
            $bccEmails = $receipentsInfo['email'];

            self::sendMailTpl($msgDetail["buyer_email"], "return_request_message_user", $langId, $arrReplacements, '', 0, array(), $bccEmails);

            $phoneNumbers = $receipentsInfo['phone'];
            $phoneNumbers[] = $msgDetail['op_shop_owner_phone'];
            foreach ($phoneNumbers as $phone) {
                $this->sendSms("return_request_message_user", $phone, $arrReplacements, $langId);
            }
            $notification_user_id = $msgDetail["seller_id"];

            $notiArrReplacements = array(
                '{username}' => FatApp::getConfig('CONF_WEBSITE_NAME_' . $langId, FatUtility::VAR_STRING, ""),
                '{requestnumber}' => $msgDetail["orrequest_reference"],
            );

            $appNotification = CommonHelper::replaceStringData(Labels::getLabel('APP_NEW_MESSAGE_POSTED_BY_{username}_ON_RETURN_{requestnumber}', $langId), $notiArrReplacements, true);

            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $notification_user_id,
                'unotification_body' => $appNotification,
                'unotification_type' => 'MESSAGE_RETURN_REQUEST',
                'unotification_data' => json_encode(array('username' => $msgDetail["buyer_username"], 'requestNumber' => $msgDetail["orrequest_reference"])),
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }
        /* ] */



        /* To Admin[ */
        if ($msgDetail['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED) {
            $adminReturnRequestUrl = CommonHelper::getAdminUrl('OrderReturnRequests', 'View', array($msgDetail['orrequest_id']));
            $adminReturnRequestUrl = '<a href="' . $adminReturnRequestUrl . '">' . Labels::getLabel('LBL_Click_here', $langId) . '</a>';
            $arrReplacements["{user_full_name}"] = "Admin";
            $arrReplacements["{click_here}"] = $adminReturnRequestUrl;

            $this->sendMailToAdminAndAdditionalEmails("return_request_message_user", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
            $this->sendSms("return_request_message_user", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);
        }
        /* ] */
        return true;
        /* $p=new Products();
        $return_request=$p->getReturnRequestMessage($return_request_message);
        if ( $return_request ){
        $return_request_id = $return_request["refund_id"];
        $url = generateAbsoluteUrl('account', 'view_return_request',array($return_request_id),CONF_WEBROOT_URL);
        $url = '<a href="'.$url.'">'.getLabel('M_click_here').'</a>';

        //Buyer Notification
        $arr_replacements = array(
        '{site_domain}' => CONF_SERVER_PATH,
        '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
        '{username}' => Settings::getSetting("CONF_WEBSITE_NAME"),
        '{request_number}' => format_return_request_number($return_request_id),
        '{message}' => nl2br($return_request["refmsg_text"]),
        '{user_full_name}' => $return_request["buyer_name"],
        '{click_here}' => $url,
        );

        if ($return_request["refund_user_id"]!=$return_request["refmsg_from"]){
        $arr_replacements["{user_full_name}"] = $return_request["buyer_name"];
        if ($return_request['refmsg_from_type']=="U"){
        $arr_replacements["{username}"] = $return_request["opr_shop_owner_username"];
        }
        sendMailTpl($return_request["buyer_email"], "return_request_message_user", $arr_replacements);
        }
        //End Buyer Notification

        //Vendor Notification
        if ($return_request["shop_user_id"]!=$return_request["refmsg_from"]){
        $arr_replacements["{user_full_name}"] = $return_request["opr_shop_owner_name"];
        if ($return_request['refmsg_from_type']=="U"){
        $arr_replacements["{username}"] = $return_request["message_sent_by_username"];
        }
        sendMailTpl($return_request["opr_shop_owner_email"], "return_request_message_user", $arr_replacements);
        }
        //End Vendor Notification

        if (($return_request["refund_request_status"]==1) && ($return_request['refmsg_from_type']=="U")){
        $url = generateAbsoluteUrl('returnrequests', 'view_return_request',array($return_request_id),'/manager/');
        $url='<a href="'.$url.'">'.getLabel('M_click_here').'</a>';
        $arr_replacements["{user_full_name}"]="Admin";
        $arr_replacements["{click_here}"]=$url;
        sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "return_request_message_user", $arr_replacements);
        $emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS",FatUtility::VAR_STRING,''));
        foreach ($emails as $email) {
        if (strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
         sendMailTpl($email, "return_request_message_user", $langId, $arr_replacements);
        }
        }
        }
        return true;
        }else{
        $this->error = getLabel('M_INVALID_REQUEST');
        } */
    }

    public function sendCatalogRequestMessageNotification($scatrequestmsg_id, $langId)
    {
        $langId = FatUtility::int($langId);
        $scatrequestmsg_id = FatUtility::int($scatrequestmsg_id);
        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        if (!$scatrequestmsg_id) {
            trigger_error(Labels::getLabel('MSG_Message_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }

        $srch = new CatalogRequestMessageSearch();
        $srch->joinCatalogRequests();
        $srch->joinMessageUser();
        $srch->joinMessageAdmin();
        $srch->joinReceiverUser();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('scatrequestmsg_id', '=', $scatrequestmsg_id);
        $srch->addMultipleFields(
            array(
                'scatrequestmsg_from_user_id',
                'scatrequestmsg_msg', 'scatrequest_status', 'scatrequest_id', 'scatrequest_user_id',
                'scatrequestmsg_from_admin_id', 'admin_name', 'admin_username', 'receiver_user.user_name', 'receiver_user_cred.credential_email'
            )
        );
        $rs = $srch->getResultSet();
        if (!$msgDetail = FatApp::getDb()->fetch($rs)) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }
        $requestDetailUrl = UrlHelper::generateFullUrl('Seller', 'requestedCatalog', array(), CONF_WEBROOT_FRONT_URL);
        $requestDetailUrl = '<a href="' . $requestDetailUrl . '">' . Labels::getLabel('LBL_Click_here', $langId) . '</a>';

        /* Buyer Notification [ */
        $arrReplacements = array(
            '{username}' => FatApp::getConfig('CONF_WEBSITE_NAME_' . $langId, FatUtility::VAR_STRING, ""),
            '{message}' => nl2br($msgDetail["scatrequestmsg_msg"]),
            '{user_full_name}' => $msgDetail["user_name"],
            '{click_here}' => $requestDetailUrl,
        );

        if ($msgDetail["scatrequest_user_id"] != $msgDetail["scatrequestmsg_from_user_id"]) {
            $arrReplacements["{user_full_name}"] = $msgDetail["user_name"];

            if ($msgDetail['scatrequestmsg_from_admin_id']) {
                $arrReplacements["{username}"] = FatApp::getConfig('CONF_WEBSITE_NAME_' . $langId, FatUtility::VAR_STRING, "");
            }
            self::sendMailTpl($msgDetail["credential_email"], "catalog_request_message_user", $langId, $arrReplacements);
        }
        /* ] */


        /* To Admin[ */

        $adminCatRequestUrl = CommonHelper::getAdminUrl('Users', 'sellerCatalogRequests');
        $adminCatRequestUrl = '<a href="' . $adminCatRequestUrl . '">' . Labels::getLabel('LBL_Click_here', $langId) . '</a>';
        $arrReplacements["{user_full_name}"] = "Admin";
        $arrReplacements["{click_here}"] = $adminCatRequestUrl;

        $this->sendMailToAdminAndAdditionalEmails("catalog_request_message_user", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);

        /* ] */
        return true;
    }

    public function sendOrderReturnRequestStatusChangeNotification($orrequest_id, $langId)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $langId = FatUtility::int($langId);
        if (!$orrequest_id || !$langId) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed.', $this->commonLangId), E_USER_ERROR);
        }
        $db = FatApp::getDb();
        $srch = new OrderReturnRequestSearch();
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderSellerUser();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addMultipleFields(
            array(
                'orrequest_id', 'orrequest_user_id', 'orrequest_status', 'orrequest_reference',
                'buyer.user_name as buyer_name', 'buyer_cred.credential_email as buyer_email', 'op_selprod_user_id', 'seller.user_name as seller_name',
                'seller_cred.credential_email as seller_email'
            )
        );
        $rs = $srch->getResultSet();
        $request = $db->fetch($rs);
        if (!$request) {
            $this->error = Labels::getLabel(Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId), $langId);
            return false;
        }

        $msgSrch = new OrderReturnRequestMessageSearch();
        $msgSrch->joinMessageUser();
        $msgSrch->joinMessageAdmin();
        $msgSrch->joinOrderReturnRequests();
        $msgSrch->doNotCalculateRecords();
        $msgSrch->addCondition('orrmsg_orrequest_id', '=', $orrequest_id);
        $msgSrch->addOrder('orrmsg_id', 'DESC');
        $msgSrch->setPageNumber(1);
        $msgSrch->setPageSize(1);
        $msgSrch->addMultipleFields(
            array(
                'orrmsg_id', 'orrmsg_from_user_id', 'user_name', 'user_dial_code', 'user_phone',
                'orrmsg_from_admin_id', 'admin_name', 'admin_username'
            )
        );
        $msgRs = $msgSrch->getResultSet();
        $lastMsgRow = $db->fetch($msgRs);

        $arrReplacements = array(
            '{username}' => $lastMsgRow['user_name'],
            '{request_number}' => $request['orrequest_reference'], /* CommonHelper::formatOrderReturnRequestNumber( $request['orrequest_id'] ), */
            '{user_full_name}' => $request["buyer_name"],
            '{new_status_name}' => OrderReturnRequest::getRequestStatusArr($langId)[$request['orrequest_status']],
        );

        if ($lastMsgRow['orrmsg_from_admin_id']) {
            $arrReplacements['{username}'] = FatApp::getConfig('CONF_WEBSITE_NAME_' . $langId, FatUtility::VAR_STRING, "");
        }

        if ($lastMsgRow && $lastMsgRow['orrmsg_from_user_id'] != $request['orrequest_user_id']) {
            self::sendMailTpl($request["buyer_email"], "return_request_status_change_notification", $langId, $arrReplacements);
        }

        if ($lastMsgRow && $lastMsgRow["orrmsg_from_user_id"] != $request["op_selprod_user_id"]) {
            $arrReplacements["{user_full_name}"] = $request["seller_name"];
            self::sendMailTpl($request["seller_email"], "return_request_status_change_notification", $langId, $arrReplacements);
        }
        $phone = !empty($lastMsgRow['user_phone']) ? $lastMsgRow['user_dial_code'] . $lastMsgRow['user_phone'] : '';
        $this->sendSms("return_request_status_change_notification", $phone, $arrReplacements, $langId);

        /* code to send emails to admin accordingly and below code is not handled[  */
        if ($lastMsgRow['orrmsg_from_user_id'] > 0) {
            $arrReplacements["{user_full_name}"] = "Admin";

            $this->sendMailToAdminAndAdditionalEmails("return_request_status_change_notification", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
            $this->sendSms("return_request_status_change_notification", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);
        }
        /* ] */
        return true;
        /* global $return_status_arr;
        $p=new Products();
        $return_request=$p->getReturnRequest($return_request);
        $last_updated_by=$return_request["last_updated_by"]!=""?$return_request["last_updated_by"]:Settings::getSetting("CONF_WEBSITE_NAME");
        if ($return_request){
        $arr_replacements = array(
        '{site_domain}' => CONF_SERVER_PATH,
        '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
        '{username}' => $last_updated_by,
        '{request_number}' => format_return_request_number($return_request['refund_id']),
        '{user_full_name}' => $return_request["buyer_name"],
        '{new_status_name}' => $return_status_arr[$return_request["refund_request_status"]],
        );

        if ($return_request['refmsg_from_type']=="A") {
        $arr_replacements["{username}"]=Settings::getSetting("CONF_WEBSITE_NAME");
        }

        if ($return_request["refund_request_updated_by"]!=$return_request["refund_user_id"]){
        sendMailTpl($return_request["buyer_email"], "return_request_status_change_notification",$arr_replacements);
        }

        if ($return_request["refund_request_updated_by"]!=$return_request["shop_user_id"]){
        $arr_replacements["{user_full_name}"]=$return_request["vendor_name"];
        sendMailTpl($return_request["opr_shop_owner_email"], "return_request_status_change_notification",$arr_replacements);
        }

        if ($return_request['refund_request_action_by']=="U") {
        $arr_replacements["{user_full_name}"]="Admin";
        sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "return_request_status_change_notification", $arr_replacements);
        $emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS",FatUtility::VAR_STRING,''));
        foreach ($emails as $email) {
        if (mb_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
         sendMailTpl($email, "return_request_status_change_notification", $langId, $arr_replacements);
        }
        }

        }
        return true;
        } else{
        $this->error = getLabel('M_INVALID_REQUEST');
        } */
    }

    public function sendOrderCancellationRequestUpdateNotification($ocrequest_id, $langId)
    {
        $ocrequest_id = FatUtility::int($ocrequest_id);
        $langId = FatUtility::int($langId);
        if (!$ocrequest_id || !$langId) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed.', $this->commonLangId), E_USER_ERROR);
        }
        $db = FatApp::getDb();
        $srch = new OrderCancelRequestSearch();
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->addCondition('ocrequest_id', '=', $ocrequest_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array(
                'ocrequest_id', 'ocrequest_op_id', 'ocrequest_ocreason_id', 'ocrequest_status',
                'op_invoice_number', 'buyer.user_name as buyer_name', 'CONCAT(buyer.user_dial_code, buyer.user_phone) as buyer_phone', 'buyer_cred.credential_email as buyer_email', 'buyer.user_id as buyer_id'
            )
        );
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }
        $arrReplacements = array(
            '{invoice_number}' => $row["op_invoice_number"],
            '{request_status}' => OrderCancelRequest::getRequestStatusArr($langId)[$row['ocrequest_status']],
            '{user_name}' => $row['buyer_name'],
        );
        self::sendMailTpl($row['buyer_email'], "cancellation_request_approved_declined", $langId, $arrReplacements);
        $this->sendSms("cancellation_request_approved_declined", $row['buyer_phone'], $arrReplacements, $langId);
        $notiArrReplacements = array(
            '{invoicenumber}' => $row["op_invoice_number"],
            '{requeststatus}' => OrderCancelRequest::getRequestStatusArr($langId)[$row['ocrequest_status']],
        );

        $appNotification = CommonHelper::replaceStringData(Labels::getLabel('APP_STATUS_FOR_CANCELLATION_{invoicenumber}_UPDATED_{requeststatus}', $langId), $notiArrReplacements, true);

        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $row["buyer_id"],
            'unotification_body' => $appNotification,
            'unotification_type' => 'CANCELLATION_REQUEST_STATUS',
            'unotification_data' => json_encode(array('invoiceNumber' => $row["op_invoice_number"], 'requestStatus' => $arrReplacements["{request_status}"])),
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }

        return true;
    }

    public function sendShopReportNotification($sreport_id, $langId)
    {
        $sreport_id = FatUtility::int($sreport_id);
        $langId = FatUtility::int($langId);
        if (!$sreport_id || !$langId) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed.', $this->commonLangId), E_USER_ERROR);
        }

        $srch = new ShopReportSearch();
        $srch->doNotCalculateRecords();
        $srch->joinUser();
        $srch->joinShops($langId);
        $srch->addCondition('sreport_id', '=', $sreport_id);
        $srch->addMultipleFields(
            array(
                'sreport_id', 'sreport_reportreason_id', 'IFNULL(shop_name, shop_identifier) as shop_name',
                'credential_username', 'sreport_message'
            )
        );
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        $arrReportReasons = ShopReportReason::getReportReasonArr($langId);
        $arrReplacements = array(
            '{username}' => $row['credential_username'],
            '{shop_name}' => $row['shop_name'],
            '{report_reason}' => $arrReportReasons[$row['sreport_reportreason_id']],
            '{report_message}' => nl2br($row['sreport_message']),
        );

        $this->sendMailToAdminAndAdditionalEmails("report_shop", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
        $this->sendSms("report_shop", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);
        return true;
    }

    public function sendBlogContributionStatusChangeEmail($langId, $d)
    {
        $tpl = 'blog_contribution_status_changed';
        $statusArr = applicationConstants::getBlogContributionStatusArr(FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1));
        $vars = array(
            '{user_full_name}' => $d['bcontributions_author_first_name'],
            '{new_status}' => $statusArr[$d['bcontributions_status']],
            '{posted_on_datetime}' => $d['bcontributions_added_on'],
        );

        if (!self::sendMailTpl($d['bcontributions_author_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, $d['bcontributions_author_phone'], $vars, $langId);
        return true;
    }

    public function sendBlogCommentStatusChangeEmail($langId, $d)
    {
        $tpl = 'blog_comment_status_changed';
        $statusArr = applicationConstants::getBlogCommentStatusArr(FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1));
        $vars = array(
            '{user_full_name}' => $d['bpcomment_author_name'],
            '{new_status}' => $statusArr[$d['bpcomment_approved']],
            '{post_title}' => $d['post_title'],
            '{comment}' => $d['bpcomment_content'],
            '{posted_on_datetime}' => $d['bpcomment_added_on'],
        );

        if (!self::sendMailTpl($d['bpcomment_author_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $uData = User::getAttributesById($d['bpcomment_user_id'], ['user_dial_code', 'user_phone']);
        $this->sendSms($tpl, implode("", $uData), $vars, $langId);
        return true;
    }

    public function sendBuyerReviewNotification($opId, $langId = 0)
    {
        if ($opId == '') {
            trigger_error(Labels::getLabel('MSG_Order_Product_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);

        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        $orderObj = new Orders();
        $orderProduct = $orderObj->getOrderProductsByOpId($opId, $langId);

        $opChargesLog = new OrderProductChargeLog($opId);
        $taxOptions = $opChargesLog->getData($langId);
        $orderProduct['taxOptions'] = $taxOptions;

        if ($orderProduct) {
            $tpl = new FatTemplate('', '');
            $tpl->set('orderProducts', $orderProduct);
            $tpl->set('siteLangId', $langId);
            $orderItemsTableFormatHtml = $tpl->render(false, false, '_partial/emails/child-order-detail-email.php', true);

            $statuesArr = Orders::getOrderProductStatusArr($orderProduct["order_language_id"]);

            $userObj = new User($orderProduct["order_user_id"]);
            $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));

            $arrReplacements = array(
                '{user_full_name}' => trim($userInfo["user_name"]),
                '{new_order_status}' => $statuesArr[$orderProduct["op_status_id"]],
                '{invoice_number}' => $orderProduct["op_invoice_number"],
                '{order_items_table_format}' => $orderItemsTableFormatHtml,
                '{review_page_url}' => UrlHelper::generateFullUrl('Buyer', 'orderFeedback', array($orderProduct['op_id']), CONF_WEBROOT_FRONT_URL),
            );
            self::sendMailTpl($userInfo["credential_email"], "buyer_notification_review_order_product", $langId, $arrReplacements);
            $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
            $this->sendSms("buyer_notification_review_order_product", $phone, $arrReplacements, $langId);
            return true;
        } else {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
        }
    }

    public function sendBuyerReviewStatusUpdatedNotification($spreviewId, $langId = 0)
    {
        if ($spreviewId == '') {
            trigger_error(Labels::getLabel('MSG_Review_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);

        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }

        $schObj = new SelProdReviewSearch($langId);
        $schObj->joinUser();
        $schObj->joinProducts($langId);
        $schObj->joinSellerProducts($langId);
        $schObj->addCondition('spreview_id', '=', $spreviewId);
        $schObj->addCondition('spreview_status', '!=', SelProdReview::STATUS_PENDING);
        $schObj->addMultipleFields(array('spreview_selprod_id', 'spreview_status', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'user_name', 'user_dial_code', 'user_phone', 'credential_email'));
        $spreviewData = FatApp::getDb()->fetch($schObj->getResultSet());

        if (false == $spreviewData) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        $reviewStatusArr = SelProdReview::getReviewStatusArr($langId);
        $newStatus = $reviewStatusArr[$spreviewData['spreview_status']];

        $productUrl = UrlHelper::generateFullUrl('Products', 'View', array($spreviewData["spreview_selprod_id"]));
        $prodTitleAnchor = "<a href='" . $productUrl . "'>" . $spreviewData['selprod_title'] . "</a>";

        $arrReplacements = array(
            '{user_full_name}' => trim($spreviewData["user_name"]),
            '{new_status}' => $newStatus,
            '{product_link}' => $prodTitleAnchor
        );

        self::sendMailTpl($spreviewData["credential_email"], "buyer_notification_review_status_updated", $langId, $arrReplacements);
        $phone = !empty($spreviewData['user_phone']) ? $spreviewData['user_dial_code'] . $spreviewData['user_phone'] : '';
        $this->sendSms("buyer_notification_review_status_updated", $phone, $arrReplacements, $langId);
        return true;
    }

    public function sendAdminAbusiveReviewNotification($spreviewId, $langId = 0)
    {
        if ($spreviewId == '') {
            trigger_error(Labels::getLabel('MSG_Review_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        $langId = FatUtility::int($langId);

        if (!$langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }

        $schObj = new SelProdReviewSearch($langId);
        $schObj->joinUser();
        $schObj->addCondition('spreview_id', '=', $spreviewId);
        $spreviewData = FatApp::getDb()->fetch($schObj->getResultSet());
        if (false == $spreviewData) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        $arrReplacements = array(
            '{user_full_name}' => trim($spreviewData["user_name"]),
            '{review_url}' => CommonHelper::getAdminUrl('ProductReviews', 'index')
        );

        $to = FatApp::getConfig('CONF_CONTACT_EMAIL', FatUtility::VAR_STRING, '');
        if (strlen(trim($to)) < 1) {
            return $this->sendMailToAdminAndAdditionalEmails("admin_notification_abusive_review_posted", $arrReplacements, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId);
        }

        self::sendMailTpl($to, "admin_notification_abusive_review_posted", $langId, $arrReplacements);
        $this->sendSms("admin_notification_abusive_review_posted", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);
        return true;
    }

    public function sendRewardPointsNotification($langId, $urpId)
    {
        $langId = FatUtility::int($langId);
        $urpId = FatUtility::int($urpId);
        if (!$urpId || !$langId) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed.', $this->commonLangId), E_USER_ERROR);
        }

        $srch = new UserRewardSearch();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinUser();
        $srch->addCondition('urp_id', '=', $urpId);
        $srch->addMultipleFields(array('urp.*', 'u.user_name', 'u.user_dial_code', 'u.user_phone', 'uc.credential_email'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!$row) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        if (1 > abs($row['urp_points'])) {
            return true;
        }

        $arrReplacements = array(
            '{user_name}' => trim($row["user_name"]),
            '{debit_credit_type}' => $row['urp_points'] > 0 ? Labels::getLabel('LBL_credited', $langId) : Labels::getLabel('LBL_debited', $langId),
            '{reward_points}' => abs($row['urp_points']),
            '{comments}' => $row["urp_comments"],
            '{reward_point_balance}' => UserRewardBreakup::rewardPointBalance($row["urp_user_id"]),
        );

        $this->sendMailToAdminAndAdditionalEmails("reward_points_credited_debited", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
        $phone = !empty($row['user_phone']) ? $row['user_dial_code'] . $row['user_phone'] : '';
        $this->sendSms("reward_points_credited_debited", $phone, $arrReplacements, $langId);
        $notiArrReplacements = array(
            '{debitcredittype}' => $row['urp_points'] > 0 ? Labels::getLabel('LBL_credited', $langId) : Labels::getLabel('LBL_debited', $langId),
            '{rewardpoints}' => abs($row['urp_points']),
        );

        $appNotification = CommonHelper::replaceStringData(Labels::getLabel('APP_REWARDS_{rewardpoints}_HAS_BEEN_{debitcredittype}_ACCOUNT', $langId), $notiArrReplacements);

        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $row["urp_user_id"],
            'unotification_body' => $appNotification,
            'unotification_type' => 'REWARD_POINTS',
            'unotification_data' => json_encode(array('rewardPoints' => abs($row['urp_points']), 'debitCreditType' => $arrReplacements["{debit_credit_type}"])),
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }

        return true;
    }

    public function sendDiscountCouponNotification($couponId, $userId, $langId = 0)
    {
        $userId = FatUtility::int($userId);
        $couponId = FatUtility::int($couponId);
        $langId = FatUtility::int($langId);

        $userCoupons = DiscountCoupons::getCouponUsers($couponId);
        if (empty($userCoupons)) {
            return false;
        }

        foreach ($userCoupons as $row) {
            if (!isset($row['ctu_user_id']) || $row['ctu_user_id'] != $userId) {
                continue;
            }

            $discountValue = ($row['coupon_discount_in_percent'] == ApplicationConstants::PERCENTAGE) ? $row['coupon_discount_value'] . ' %' : CommonHelper::displayMoneyFormat($row['coupon_discount_value'], true, true);
            $arrReplacements = array(
                '{user_name}' => trim($row["user_name"]),
                '{coupon_code}' => $row['coupon_code'],
                '{discount_value}' => $discountValue,
                '{expired_on}' => FatDate::format($row["coupon_end_date"]),
            );
            self::sendMailTpl($row['credential_email'], 'user_discount_coupon_notification', $langId, $arrReplacements);
            $phone = !empty($row['user_phone']) ? $row['user_dial_code'] . $row['user_phone'] : '';
            $this->sendSms('user_discount_coupon_notification', $phone, $arrReplacements, $langId);
        }
    }

    public function sendMailShareEarn($senderId, $receiverEmail, $personalMsg, $langId)
    {
        if (empty($senderId)) {
            trigger_error(Labels::getLabel('MSG_Sender_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        if (empty($receiverEmail)) {
            trigger_error(Labels::getLabel('MSG_Receiver_Email_Address_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        $tpl = 'share_earn_invitation_email';
        $userObj = new User($senderId);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_dial_code', 'user_phone', 'user_referral_code'));

        $vars = array(
            '{user_full_name}' => $userInfo['user_name'],
            '{tracking_url}' => CommonHelper::referralTrackingUrl($userInfo['user_referral_code']),
            '{invitation_message}' => $personalMsg,
        );

        if (!self::sendMailTpl($receiverEmail, $tpl, $langId, $vars)) {
            return false;
        }

        $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
        $this->sendSms($tpl, $phone, $vars, $langId);
        return true;
    }

    public function sendAffiliateMailShare($senderId, $receiverEmail, $personalMsg, $langId)
    {
        if (empty($senderId)) {
            trigger_error(Labels::getLabel('MSG_Sender_Id_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        if (empty($receiverEmail)) {
            trigger_error(Labels::getLabel('MSG_Receiver_Email_Address_not_specified.', $this->commonLangId), E_USER_ERROR);
        }
        $tpl = 'affiliate_share_invitation_email';
        $userObj = new User($senderId);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_referral_code'));

        $vars = array(
            '{user_full_name}' => $userInfo['user_name'],
            '{tracking_url}' => CommonHelper::affiliateReferralTrackingUrl($userInfo['user_referral_code']),
            '{invitation_message}' => $personalMsg,
        );

        if (!self::sendMailTpl($receiverEmail, $tpl, $langId, $vars)) {
            return false;
        }

        $srch = User::getSearchObject(true);
        $srch->addMultipleFields(array('u.user_dial_code', 'u.user_phone'));
        $srch->addCondition('uc.credential_email', '=', $receiverEmail);
        $rs = $srch->getResultSet();
        $receiverData = FatApp::getDb()->fetch($rs);
        $this->sendSms($tpl, implode("", $receiverData), $vars, $langId);

        return true;
    }

    public function sendCancelSubscriptionNotification($user_id, $langId)
    {
        $tpl = 'cancel_subscription_email';
        $userObj = new User($user_id);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_dial_code', 'user_phone', 'credential_email'));


        $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($langId, $user_id, array('ossubs_subscription_name'));
        if (!$currentPlanData) {
            return false;
        }

        $spackage_name = $currentPlanData['ossubs_subscription_name'];
        $vars = array(
            '{user_full_name}' => $userInfo['user_name'],
            '{spackage_name}' => $spackage_name
        );

        if (!self::sendMailTpl($userInfo['credential_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
        $this->sendSms($tpl, $phone, $vars, $langId);
        return true;
    }

    public function sendSubscriptionReminderEmail($langId, $data)
    {
        if ($data['ossubs_type'] == SellerPackages::FREE_TYPE) {
            $tpl = 'subscription_free_package_reminder_email';
        } elseif ($data['ossubs_type'] == SellerPackages::PAID_TYPE) {
            $tpl = 'subscription_reminder_email';
        }
        $spackage_detail = OrderSubscription::getUserCurrentActivePlanDetails($langId, $data['user_id'], array('spackage_name', 'ossubs_till_date'));
        $pending_days = FatDate::diff(date("Y-m-d"), $spackage_detail[OrderSubscription::DB_TBL_PREFIX . 'till_date']);
        $vars = array(
            '{user_full_name}' => $data['user_name'],
            '{spackage_name}' => $spackage_detail['spackage_name'],
            '{pending_days}' => $pending_days
        );

        if (!self::sendMailTpl($data['credential_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $phone = !empty($data['user_phone']) ? $data['user_dial_code'] . $data['user_phone'] : '';
        $this->sendSms($tpl, $phone, $vars, $langId);
        return true;
    }

    public function orderPurchasedSubscriptionEmail($orderId)
    {
        $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $orderObj = new Orders();
        $orderDetail = $orderObj->getOrderById($orderId);

        $userObj = new User($orderDetail["order_user_id"]);
        $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));

        $payementStatusArr = Orders::getOrderPaymentStatusArr($langId);

        $srch = new OrderSubscriptionSearch($orderDetail['order_language_id'], true, true);
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addCondition('ossubs_order_id', '=', $orderId);
        $rs = $srch->getResultSet();
        $subsOrderDetail = FatApp::getDb()->fetch($rs);
        if ($orderDetail) {
            $tpl = new FatTemplate('', '');
            $tpl->set('orderDetail', $subsOrderDetail);
            $tpl->set('siteLangId', $langId);
            $orderItemsTableFormatHtml = $tpl->render(false, false, '_partial/emails/order-detail-subscription-email.php', true);

            $arrReplacements = array(
                '{user_full_name}' => trim($userInfo['user_name']),
                '{invoice_number}' => $orderDetail['order_id'],
                '{order_products_table_format}' => $orderItemsTableFormatHtml,
                '{new_order_status}' => $payementStatusArr[$orderDetail['order_payment_status']],
            );

            $this->sendMailToAdminAndAdditionalEmails("new_subscription_purchase_admin", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
            $this->sendSms("new_subscription_purchase_admin", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);

            self::sendMailTpl($userInfo["credential_email"], "new_subscription_purchase", $orderDetail['order_language_id'], $arrReplacements);
            $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
            $this->sendSms("new_subscription_purchase", $phone, $arrReplacements, $langId);
        }
        return true;
    }

    public function orderRenewSubscriptionEmail($orderId)
    {
        $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $orderObj = new Orders();
        $orderDetail = $orderObj->getOrderById($orderId);

        $userObj = new User($orderDetail["order_user_id"]);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_dial_code', 'credential_email', 'user_phone'));

        $payementStatusArr = Orders::getOrderPaymentStatusArr($langId);

        $srch = new OrderSubscriptionSearch($orderDetail['order_language_id'], true, true);
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addCondition('ossubs_order_id', '=', $orderId);
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);


        if ($orderDetail) {
            $tpl = new FatTemplate('', '');
            $tpl->set('orderDetail', $orderDetail);
            $tpl->set('siteLangId', $langId);
            $orderItemsTableFormatHtml = $tpl->render(false, false, '_partial/emails/order-detail-subscription-email.php', true);

            $arrReplacements = array(
                '{user_full_name}' => trim($userInfo['user_name']),
                '{invoice_number}' => $orderDetail['order_id'],
                '{order_products_table_format}' => $orderItemsTableFormatHtml,
                '{new_order_status}' => $payementStatusArr[$orderDetail['order_payment_status']],
            );

            $this->sendMailToAdminAndAdditionalEmails("subscription_renew_admin", $arrReplacements, static::ADD_ADDITIONAL_ALERTS, static::NOT_ONLY_SUPER_ADMIN, $langId);
            $this->sendSms("subscription_renew_admin", FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $arrReplacements, $langId);

            self::sendMailTpl($userInfo["credential_email"], "subscription_renew_user", $orderDetail['order_language_id'], $arrReplacements);
            $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
            $this->sendSms("subscription_renew_user", $phone, $arrReplacements, $orderDetail['order_language_id']);
        }
        return true;
    }

    private static function commonVars($langId)
    {
        $srch = SocialPlatform::getSearchObject($langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('splatform_user_id', '=', 0);
        $rs = $srch->getResultSet();
        $rows = FatApp::getDb()->fetchAll($rs);

        $social_media_icons = '';
        if (!empty($rows)) {
            $social_media_icons = '<table style="margin:0 auto"><tr>';
            foreach ($rows as $row) {
                $img = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_PLATFORM_IMAGE, $row['splatform_id']);
                $title = ($row['splatform_title'] != '') ? $row['splatform_title'] : $row['splatform_identifier'];
                $target_blank = ($row['splatform_url'] != '') ? 'target="_blank"' : '';
                $url = $row['splatform_url'] != '' ? $row['splatform_url'] : 'javascript:void(0)';
    
                if (!empty($img)) {
                    $uploadedTime = AttachedFile::setTimeParam($img['afile_updated_at']);
                    $imgSrc = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'SocialPlatform', array($row['splatform_id']), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                } elseif ($row['splatform_icon_class'] != '') {
                    $imgSrc = UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . 'images/' . $row['splatform_icon_class'] . '.png';
                }
                
                $social_media_icons .= '<td style="padding:5px"><a style="max-witdh:30px;" href="' . $url . '" ' . $target_blank . ' title="' . $title . '" ><img alt="' . $title . '"  src = "' . $imgSrc . '"/></a></td>';
            }
            $social_media_icons .= '</tr></table>';
        }
        
        $fileRow = AttachedFile::getAttachment(AttachedFile::FILETYPE_EMAIL_LOGO, 0, 0, $langId);
        $uploadedTime = AttachedFile::setTimeParam($fileRow['afile_updated_at']);

        return array(
            '{website_name}' => FatApp::getConfig('CONF_WEBSITE_NAME_' . $langId, FatUtility::VAR_STRING, ""),
            '{website_url}' => UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL),
            '{Company_Logo}' => '<img src="'. UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'emailLogo', array($langId), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') .'" style="display: block; margin: 0 auto; max-width: 120px" />',
            '{current_date}' => date('M d, Y'),
            '{social_media_icons}' => $social_media_icons,
            '{contact_us_url}' => UrlHelper::generateFullUrl('custom', 'contactUs', array(), CONF_WEBROOT_FRONT_URL),
        );
    }

    public static function sendSmtpTestEmail($langId, $smtpArr, $vars = array())
    {
        $tpl = 'test_email';
        $langId = FatUtility::int($langId);
        if (!$row = static::getMailTpl($tpl, $langId)) {
            $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
            if (!$row = static::getMailTpl($tpl, $langId)) {
                trigger_error(Labels::getLabel('ERR_Email_Template_Not_Found', CommonHelper::getLangId()), E_USER_ERROR);
                return false;
            }
        }

        if (!isset($row['etpl_body']) || $row['etpl_body'] == '') {
            return false;
        }

        $subject = $row['etpl_subject'];
        $body = $row['etpl_body'];

        $vars += static::commonVars($langId);

        foreach ($vars as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $body = str_replace($key, $val, $body);
        }

        try {
            $email = EmailHandler::sendSmtpEmail(FatApp::getConfig("CONF_SITE_OWNER_EMAIL", FatUtility::VAR_STRING, ""), $subject, $body, '', $tpl, $langId, '', $smtpArr);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function sendTestEmail($langId)
    {
        $tpl = 'test_email';
        $langId = FatUtility::int($langId);
        if (!$row = static::getMailTpl($tpl, $langId)) {
            $langId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
            if (!$row = static::getMailTpl($tpl, $langId)) {
                trigger_error(Labels::getLabel('ERR_Email_Template_Not_Found', CommonHelper::getLangId()), E_USER_ERROR);
                return false;
            }
        }

        if (!isset($row['etpl_body']) || $row['etpl_body'] == '') {
            return false;
        }

        $subject = $row['etpl_subject'];
        $body = $row['etpl_body'];

        $vars += static::commonVars($langId);

        foreach ($vars as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $body = str_replace($key, $val, $body);
        }

        try {
            $email = EmailHandler::sendSmtpEmail(FatApp::getConfig("CONF_SITE_OWNER_EMAIL", FatUtility::VAR_STRING, ""), $subject, $body, '', $tpl, $langId, '', $smtpArr);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function sendLowBalancePromotionalNotification($langId, $userId, $balanceRequired)
    {
        $userId = FatUtility::int($userId);
        $langId = FatUtility::int($langId);
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_dial_code', 'user_phone', 'credential_email'));
        $arrReplacements = array(
            '{user_name}' => trim($userInfo["user_name"]),
            '{requiredBalance}' => CommonHelper::displayMoneyFormat($balanceRequired, true, true),

        );
        self::sendMailTpl($userInfo["credential_email"], "low_balance_promotional_email", $langId, $arrReplacements);
        $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
        $this->sendSms("low_balance_promotional_email", $phone, $arrReplacements, $langId);
        return true;
    }

    public function sendLowBalanceSubscriptionNotification($langId, $userId, $balanceRequired)
    {
        $userId = FatUtility::int($userId);
        $langId = FatUtility::int($langId);
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_dial_code', 'user_phone', 'credential_email'));
        $arrReplacements = array(
            '{user_name}' => trim($userInfo["user_name"]),
            '{requiredBalance}' => CommonHelper::displayMoneyFormat($balanceRequired, true, true),
        );
        self::sendMailTpl($userInfo["credential_email"], "low_balance_subscription_email", $langId, $arrReplacements);
        $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
        $this->sendSms("low_balance_subscription_email", $phone, $arrReplacements, $langId);
        return true;
    }

    public static function sendPromotionStatusChangeNotification($langId, $userId, $d)
    {
        $tpl = 'promotion_request_status_change';
        $statusArr = Promotion::getPromotionReqStatusArr($langId);
        $promotionDetails = Promotion::getAttributesByLangId($langId, $d['promotion_id']);
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_name', 'user_dial_code', 'credential_email', 'user_phone'));

        $vars = array(
            '{user_full_name}' => $userInfo['user_name'],
            '{promotion_name}' => ((isset($promotionDetails['promotion_name'])) && $promotionDetails['promotion_name']) ? $promotionDetails['promotion_name'] : $d['promotion_identifier'],
            '{new_request_status}' => $statusArr[$d['promotion_approved']],
        );

        if (!self::sendMailTpl($userInfo['credential_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $phone = !empty($row['user_phone']) ? $row['user_dial_code'] . $row['user_phone'] : '';
        (new self())->sendSms($tpl, $phone, $vars, $langId);
        return true;
    }

    public function sendPromotionApprovalRequestAdmin($langId, $userId, $d)
    {
        $tpl = 'promotion_approval_required_to_admin';
        $promotionDetails = Promotion::getAttributesByLangId($langId, $d['promotion_id']);
        
        $userObj = new User($userId);
        $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'user_dial_code', 'user_phone'));

        $vars = array(
            '{user_full_name}' => $userInfo['user_name'],
            '{promotion_name}' => (isset($promotionDetails['promotion_name']) && $promotionDetails['promotion_name']) ? $promotionDetails['promotion_name'] : $d['promotion_identifier'],

        );
        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }
        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $vars, $langId);
        return true;
    }

    public function remindBuyerForCartItems($langId, $d)
    {
        $tpl = 'reminder_for_items_in_cart';

        $cartData = Cart::getCartData($d['user_id']);
        $cartInfo = json_decode($cartData, true);
        $selProdIds = array();
        unset($cartInfo['shopping_cart']);
        foreach ($cartInfo as $key => $quantity) {
            $keyDecoded = json_decode(base64_decode($key), true);

            if (strpos($keyDecoded, Cart::CART_KEY_PREFIX_PRODUCT) === false) {
                continue;
            }
            $selProdIds[] = FatUtility::int(str_replace(Cart::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
        }
        if (empty($selProdIds)) {
            return false;
        }
        $prodSrch = new ProductSearch($langId);
        $prodSrch->setDefinedCriteria(0, 0, array(), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', 'IN', $selProdIds);
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->doNotLimitRecords();
        $prodSrch->addMultipleFields(
            array(
                'product_id', 'product_identifier', 'IFNULL(product_name,product_identifier) as product_name', 'product_seller_id', 'product_model', 'product_type', 'prodcat_id', 'IFNULL(prodcat_name,prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn',
                'selprod_id', 'selprod_user_id', 'selprod_condition', 'selprod_price', 'special_price_found', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
                'theprice', 'selprod_stock', 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'user_name',
                'shop_id', 'shop_name',
                'splprice_display_dis_type', 'splprice_display_dis_val', 'splprice_display_list_price'
            )
        );
        $productRs = $prodSrch->getResultSet();
        $products = FatApp::getDb()->fetchAll($productRs);

        $fatTpl = new FatTemplate('', '');
        $fatTpl->set('products', $products);
        $fatTpl->set('siteLangId', $langId);
        $productsInCartFormatHtml = $fatTpl->render(false, false, '_partial/emails/products-in-cart-wishlist-email.php', true);

        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{checkout_url}' => $d['link'],
            '{products_in_cart_format}' => $productsInCartFormatHtml,
        );

        if (!self::sendMailTpl($d['user_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, $d['user_phone'], $vars, $langId);
        return true;
    }

    public function remindBuyerForWishlistItems($langId, $d)
    {
        $tpl = 'reminder_for_items_in_wishlist';

        $wListObj = new UserWishList();
        $srch = UserWishList::getSearchObject($d['user_id']);
        $wListObj->joinWishListProducts($srch);
        $srch->addMultipleFields(array('uwlp_selprod_id', 'uwlist_id'));
        $srch->addGroupBy('uwlp_selprod_id');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $selProdIds = FatApp::getDb()->fetchAllAssoc($rs);
        $selProdIds = array_keys($selProdIds);
        $prodSrch = new ProductSearch($langId);
        $prodSrch->setDefinedCriteria(0, 0, array(), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', 'IN', $selProdIds);
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->addGroupBy('selprod_id');
        $prodSrch->setPageSize(9);
        $prodSrch->addMultipleFields(
            array(
                'product_id', 'product_identifier', 'IFNULL(product_name,product_identifier) as product_name', 'product_seller_id', 'product_model', 'product_type', 'prodcat_id', 'IFNULL(prodcat_name,prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn',
                'selprod_id', 'selprod_user_id', 'selprod_condition', 'selprod_price', 'special_price_found', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
                'theprice', 'selprod_stock', 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'user_name',
                'shop_id', 'shop_name',
                'splprice_display_dis_type', 'splprice_display_dis_val', 'splprice_display_list_price'
            )
        );
        $productRs = $prodSrch->getResultSet();
        $products = FatApp::getDb()->fetchAll($productRs);

        $fatTpl = new FatTemplate('', '');
        $fatTpl->set('products', $products);
        $fatTpl->set('siteLangId', $langId);
        $productsInWishlistFormatHtml = $fatTpl->render(false, false, '_partial/emails/products-in-cart-wishlist-email.php', true);

        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{wishlist_url}' => $d['link'],
            '{products_in_wishlist_format}' => $productsInWishlistFormatHtml,
        );

        if (!self::sendMailTpl($d['user_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $this->sendSms($tpl, $d['user_phone'], $vars, $langId);
        return true;
    }

    public function failedLoginAttempt($langId, $data)
    {
        $tpl = 'failed_login_attempt';

        $vars = array(
            '{user_full_name}' => $data['user_name'],
        );

        if (!self::sendMailTpl($data['credential_email'], $tpl, $langId, $vars)) {
            return true;
        }
        $this->sendSms($tpl, $data['user_phone'], $vars, $langId);
        return false;
    }

    public function sendDataRequestNotification($data, $langId)
    {
        $tpl = 'data_request_notification_to_admin';
        $userObj = new User($data['user_id']);
        $userInfo = $userObj->getUserInfo(array('user_name', 'credential_email', 'credential_username', 'user_dial_code', 'user_phone'));

        $vars = array(
            '{user_full_name}' => $userInfo['user_name'],
            '{username}' => $userInfo['credential_username'],
            '{user_phone}' => $userInfo['user_phone'],
            '{ureq_purpose}' => $data['ureq_purpose'],

        );

        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::NOT_ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }
        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $vars, $langId);
        return true;
    }

    public function gdprRequestStatusUpdate($reqId, $langId)
    {
        $tpl = 'gdpr_request_status_update_notification_to_user';
        $reqData = UserGdprRequest::getAttributesById($reqId, array('ureq_user_id', 'ureq_type'));

        $reqTypeArr = UserGdprRequest::getUserRequestTypesArr($langId);
        $reqTypeName = $reqTypeArr[$reqData['ureq_type']];

        $userObj = new User($reqData['ureq_user_id']);
        $srch = $userObj->getUserSearchObj(array('credential_email', 'user_dial_code', 'user_phone', 'credential_username'), true, false);
        $rs = $srch->getResultSet();
        $userInfo = FatApp::getDb()->fetch($rs);
        $vars = array(
            '{username}' => $userInfo['credential_username'],
            '{request_type}' => $reqTypeName,
        );
        if (!self::sendMailTpl($userInfo['credential_email'], $tpl, $langId, $vars)) {
            return false;
        }
        $phone = !empty($userInfo['user_phone']) ? $userInfo['user_dial_code'] . $userInfo['user_phone'] : '';
        $this->sendSms($tpl, $phone, $vars, $langId);
        return true;
    }

    public function sendEmailToUser($langId, $data)
    {
        $tpl = 'user_send_email';

        $replacements = array(
            '{full_name}' => $data['user_name'],
            '{admin_subject}' => $data['mail_subject'],
            '{admin_message}' => $data["mail_message"]
        );

        if (!self::sendMailTpl($data['credential_email'], $tpl, $langId, $replacements)) {
            return false;
        }

        if (!empty($data['user_phone'])) {
            $this->sendSms($tpl, $data['user_phone'], $replacements, $langId);
        }
        return true;
    }

    public static function getEmailTemplatePermissionsArr()
    {
        return array(
            'new_registration_admin' => AdminPrivilege::SECTION_USERS,
            'new_catalog_request_admin' => AdminPrivilege::SECTION_CATALOG_REQUESTS,
            'new_custom_catalog_request_admin' => AdminPrivilege::SECTION_CATALOG_REQUESTS,
            'new_seller_approved_admin' => AdminPrivilege::SECTION_USERS,
            'new_supplier_approval_admin' => AdminPrivilege::SECTION_USERS,
            'seller_brand_request_admin_email' => AdminPrivilege::SECTION_BRAND_REQUESTS,
            'tpl_contact_request_received' => AdminPrivilege::SECTION_USERS,
            'admin_order_email' => AdminPrivilege::SECTION_ORDERS,
            'primary_order_payment_status_change_admin' => AdminPrivilege::SECTION_ORDERS,
            'primary_order_payment_status_admin' => AdminPrivilege::SECTION_ORDERS,
            'withdrawal_request_admin' => AdminPrivilege::SECTION_WITHDRAW_REQUESTS,
            'order_cancellation_notification' => AdminPrivilege::SECTION_ORDER_CANCELLATION_REQUESTS,
            'product_return' => AdminPrivilege::SECTION_ORDER_RETURN_REQUESTS,
            'return_request_message_user' => AdminPrivilege::SECTION_ORDER_RETURN_REQUESTS,
            'catalog_request_message_user' => AdminPrivilege::SECTION_CUSTOM_CATALOG_PRODUCT_REQUESTS,
            'return_request_status_change_notification' => AdminPrivilege::SECTION_ORDER_RETURN_REQUESTS,
            'report_shop' => AdminPrivilege::SECTION_SHOPS,
            'admin_notification_abusive_review_posted' => AdminPrivilege::SECTION_ABUSIVE_WORDS,
            'new_subscription_purchase_admin' => AdminPrivilege::SECTION_SUBSCRIPTION_ORDERS,
            'subscription_renew_admin' => AdminPrivilege::SECTION_SUBSCRIPTION_ORDERS,
            'promotion_approval_required_to_admin' => AdminPrivilege::SECTION_PROMOTIONS,
            'data_request_notification_to_admin' => AdminPrivilege::SECTION_USERS,
        );
    }

    /**
     * This function returns the path of file from where it calls
     *
     * @param string $fileName
     * @return integer $directory
     */
    public static function getTemplatePath($fileName = '', $directory = '_partial/emails')
    {
        if (empty($fileName)) {
            $fileName = current(debug_backtrace())['file'];
        }
        return CONF_VIEW_DIR_PATH . $directory . '/' . basename($fileName);
    }

    public function sendCodOtpVerification($langId, $d)
    {
        $tpl = 'COD_OTP_VERIFICATION';
        $vars = array(
            '{user_name}' => $d['user_name'],
            '{OTP}' => $d['otp'],
        );

        if (!self::sendMailTpl($d['credential_email'], $tpl, $langId, $vars)) {
            $this->error = Labels::getLabel("MSG_UNABLE_TO_SEND_EMAIL", $langId);
            return false;
        }
        return true;
    }

    /**
     * sendTransferBankNotification - This will trigger once buyer submitted bank transfer detail for order.
     *
     * @param  mixed $langId
     * @param  mixed $d
     * @return bool
     */
    public function sendTransferBankNotification($langId, $d)
    {
        $tpl = 'ADMIN_ORDER_PAYMENT_TRANSFERRED_TO_BANK';
        $vars = array(
            '{USER_NAME}' => $d['user_name'],
            '{ORDER_ID}' => $d['order_id'],
            '{PAYMENT_METHOD}' => $d['payment_method'],
            '{TRANSACTION_ID}' => $d['transaction_id'],
            '{AMOUNT}' => $d['amount'],
            '{COMMENTS}' => $d['comments'],
        );

        if (!$this->sendMailToAdminAndAdditionalEmails($tpl, $vars, static::NO_ADDITIONAL_ALERT, static::ONLY_SUPER_ADMIN, $langId)) {
            return false;
        }

        $this->sendSms($tpl, FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""), $vars, $langId);

        /* Send Success Notification To Buyer With Bank Transfer Order Placed.  */
        $msg = Labels::getLabel('APP_ORDER_#{ORDER-ID}_PLACED_USING_BANK_TRANFER_PAYMENT_METHOD', $langId);
        $msg = CommonHelper::replaceStringData($msg, ['{ORDER-ID}' => $d['order_id']]);
        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $d["order_user_id"],
            'unotification_body' => $msg,
            'unotification_type' => 'ORDER_PAYMENT_TRANSFERRED_TO_BANK'
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            $this->error = $notificationObj->getError();
            return false;
        }
        /* Send Success Notification To Buyer With Bank Transfer Order Placed.  */

        return true;
    }


    public function offerStatusUpdateNotification(int $langId, int $rfqId)
    {
        $tpl = 'offer_status_update_notification';
        $srch = RequestForQuote::getSearchObjForEmail($rfqId, $langId);
        $srch->joinTable(CounterOffer::DB_TBL, 'INNER JOIN', 'counter_offer_rfq_id = rfq_id', 'co');
        $srch->addOrder('co.counter_offer_id', 'DESC');
        $srch->addFld('co.*');
        $srchRs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($srchRs);

        if (empty($data)) {
            $this->error = Labels::getLabel('Msq_Invalid_Request', $langId);
            return false;
        }

        $etpl = new FatTemplate('', '');
        $etpl->set('siteLangId', $langId);
        $etpl->set('rfqDetails', $data);
        $rfqDetailTable = $etpl->render(false, false, '_partial/emails/rfq-details.php', true);

        $statusArr = RequestForQuote::statusArray($langId);
        $newStatus = (isset($statusArr[$data['rfq_status']])) ? $statusArr[$data['rfq_status']] : "";

        $action = 'requestView';
        $notificationUserId = $data['sender_id'];
        $labelKey = 'APP_OFFER_STATUS_UPDATED_BY_%s_AS_%s';
        $notificationType = Notifications::NEW_RFQ_OFFER_UPDATE_BY_SELLER;
        $userEmail = $data['sender_email'];
        $userName = $data['sender_name'];
        $notificationSenderName = $data['receiver_name'];

        if (in_array($data['rfq_status'], [RequestForQuote::REQUEST_CANCELLED_BY_BUYER, RequestForQuote::REQUEST_ACCEPTED_BY_BUYER])) {
            $action = 'view';
            $notificationUserId = $data['receiver_id'];
            $notificationType = Notifications::NEW_RFQ_OFFER_UPDATE_BY_BUYER;
            $userEmail = $data['receiver_email'];
            $userName = $data['receiver_name'];
            $notificationSenderName = $data['sender_name'];
        }

        $vars = array(
            '{new_status}' => $newStatus,
            '{receiver_user_name}' => $userName,
            '{sender_user_name}' => $notificationSenderName,
            '{rfq_details_table}' => $rfqDetailTable,
            '{rfq_link}' => CommonHelper::generateFullUrl('requestForQuotes', $action, [$rfqId]),
        );

        if (isset($notificationUserId) && $notificationUserId > 0) {
            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $notificationUserId,
                'unotification_body' => sprintf(Labels::getLabel($labelKey, $langId), $notificationSenderName, $newStatus),
                'unotification_type' => $notificationType,
                'unotification_data' => json_encode(['rfq_id' => $rfqId, 'user_name' => $notificationSenderName, 'new_status_id' => $data['rfq_status']])
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }

        if (!self::sendMailTpl($userEmail, $tpl, $langId, $vars)) {
            return false;
        }

        return true;
    }

    public function newRfqNotificationSeller(int $langId, int $rfqId)
    {
        $tpl = 'rfq_notification_seller';
        $srch = RequestForQuote::getSearchObjForEmail($rfqId, $langId);
        $srchRs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($srchRs);
        
        if (empty($data)) {
            $this->error = Labels::getLabel('Msq_Invalid_Request', $langId);
            return false;
        }

        $label = sprintf(Labels::getLabel('APP_NEW_QUOTATION_REQUEST_IS_SUBMITTED_FOR_YOUR_PRODUCT_%s', $langId), $data['selprod_title']);
        if ($data['rfq_parent_id'] > 0) {
            $tpl = 'rfq_requote_notification_seller';
            $label = sprintf(Labels::getLabel('APP_RE-QUOTATION_REQUEST_IS_SUBMITTED_FOR_YOUR_PRODUCT_%s', $langId), $data['selprod_title']);
        }

        $etpl = new FatTemplate('', '');
        $etpl->set('siteLangId', $langId);
        $etpl->set('rfqDetails', $data);
        $rfqDetailTable = $etpl->render(false, false, '_partial/emails/rfq-details.php', true);

        $vars = array(
            '{buyer_full_name}' => $data['sender_name'],
            '{seller_full_name}' => $data['receiver_name'],
            '{rfq_details_table}' => $rfqDetailTable,
            '{rfq_link}' => CommonHelper::generateFullUrl('requestForQuotes', 'view', [$rfqId]),
        );

        if (isset($data['receiver_id']) && $data['receiver_id'] > 0) {
            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $data['receiver_id'],
                'unotification_body' => $label,
                'unotification_type' => Notifications::NEW_RFQ_SUBMISSION,
                'unotification_data' => json_encode(['rfq_id' => $rfqId, 'rfq_parent_id' => $data['rfq_parent_id'], 'product_id' =>$data['rfq_selprod_id']])
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }

        if (!self::sendMailTpl($data['receiver_email'], $tpl, $langId, $vars)) {
            return false;
        }
        return true;
    }

    public function newRfqOfferNotification(int $langId, int $rfqId)
    {
        $tpl = 'new_rfq_offer_notification';
        $srch = RequestForQuote::getSearchObjForEmail($rfqId, $langId);
        $srch->joinTable(CounterOffer::DB_TBL, 'INNER JOIN', 'counter_offer_rfq_id = rfq_id', 'co');
        $srch->addMultipleFields(array(
            'rfq.*', 'selprod_title', 'IF (co.counter_offer_by = user.user_id, user.user_name, CONCAT(seller.user_name, " - ", IFNULL(shop_name, shop_identifier))) as sender_name',
            'product_updated_on', 'IFNULL(selprod_title, product_identifier) as selprod_title',
            'IF (co.counter_offer_by != seller.user_id, seller.user_name, user.user_name) as receiver_name',
            'IF (co.counter_offer_by != seller.user_id, seller_cred.credential_email, user_cred.credential_email) as receiver_email',
            'user.user_id as buyer_user_id', 'seller.user_id as seller_user_id'
        ));
        $srch->addOrder('co.counter_offer_id', 'DESC');

        $srch->addFld('co.*');
		$srchRs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($srchRs);

        if (empty($data)) {
            $this->error = Labels::getLabel('Msq_Invalid_Request', $langId);
            return false;
        }

        $etpl = new FatTemplate('', '');
        $etpl->set('siteLangId', $langId);
        $etpl->set('rfqDetails', $data);
        $rfqDetailTable = $etpl->render(false, false, '_partial/emails/rfq-details.php', true);

        $action = 'requestView';
        $notificationUserId = $data['buyer_user_id'];

        $labelKeyApp = 'APP_OFFER_IS_SUBMITTED_BY_%s_ON_YOUR_QUOTATION_REQUEST';
        $notificationType = Notifications::NEW_RFQ_OFFER_SUBMISSION_BY_SELLER;
        if ($data['counter_offer_by'] == $data['buyer_user_id']) {
            $action = 'view';
            $notificationUserId = $data['seller_user_id'];
            $labelKeyApp = 'APP_OFFER_IS_SUBMITTED_BY_%s_ON_YOUR_PRODUCT';
            $notificationType = Notifications::NEW_RFQ_OFFER_SUBMISSION_BY_BUYER;
        }

        $vars = array(
            '{receiver_user_name}' => $data['receiver_name'],
            '{sender_user_name}' => $data['sender_name'],
            '{rfq_details_table}' => $rfqDetailTable,
            '{rfq_link}' => CommonHelper::generateFullUrl('requestForQuotes', $action, [$rfqId]),
        );

        if (isset($notificationUserId) && $notificationUserId > 0) {
            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $notificationUserId,
                'unotification_body' => sprintf(Labels::getLabel($labelKeyApp, $langId), $data['sender_name']),
                'unotification_type' => $notificationType,
                'unotification_data' => json_encode(['rfq_id' => $rfqId, 'user_name' => $data['sender_name']])
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }

        if (!self::sendMailTpl($data['receiver_email'], $tpl, $langId, $vars)) {
            return false;
        }
        return true;
    }

    public function sendInvoiceLink($langId, $data)
    {
        $tpl = 'buyer_invoice_link';
        $vars = array(
            '{shop_Logo}' => '<img style="max-width:100px; " src="' . $data['shop_logo'] . '" />',
            '{user_full_name}' => $data['user_name'],
            '{invoice_url}' => $data['invoice_url'],
            '{invoice_details_table}' => $data['invoice_details_table'],
        );

        if (isset($data['user_id']) && $data['user_id'] > 0) {
            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $data['user_id'],
                'unotification_body' => Labels::getLabel('APP_NEW_INVOICE_IS_GENERATED_FOR_YOUR_REQUEST', $langId),
                'unotification_type' => Notifications::INVOICE_SHARED_BY_SELLER,
                'unotification_data' => json_encode(['order_id' => $data['order_id']]),
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }

        if (!self::sendMailTpl($data['user_email'], $tpl, $langId, $vars)) {
            return false;
        }

        return true;
    }
	
	public function requestForInvoice(int $langId, array $data): bool
    {
        if (1 > $langId || empty($data)) {
            $this->error = Lables::getLabel('LBL_INVALID_REQUEST', $langId);
            return false;
        }

        $tpl = 'invoice_regenrate_request';
        $vars = array(
            '{buyer_name}' => $data['user_name'],
            '{seller_name}' => $data['seller_name'],
            '{invoice_url}' => $data['invoice_url'],
            '{invoice_details_table}' => $data['invoice_details_table']
        );

        if (isset($data['seller_id']) && $data['seller_id'] > 0) {
            $notificationObj = new Notifications();
            $notificationDataArr = array(
                'unotification_user_id' => $data['seller_id'],
                'unotification_body' => sprintf(Labels::getLabel('APP_NEW_INVOICE_REGENRATION_REQUEST_IS_SUBMITTED_BY_%s_FOR_ORDER_%s', $langId), $data['user_name'], $data['order_id']),
                'unotification_type' => Notifications::INVOICE_REGENERATE_REQUEST_BY_BUYER,
                'unotification_data' => json_encode(['order_id' => $data['order_id'], 'user_name' => $data['user_name']]),
            );
            if (!$notificationObj->addNotification($notificationDataArr)) {
                $this->error = $notificationObj->getError();
                return false;
            }
        }

        if (!self::sendMailTpl($data['user_email'], $tpl, $langId, $vars)) {
            return false;
        }

        return true;
    }


    public function sendAdminNewUserCreationEmail($langId, $d)
    {
        $tpl = 'admin_new_user_creation_email';

        $vars = array(
            '{user_full_name}' => $d['user_name'],
            '{user_email}' => $d['user_name'],
            '{reset_url}' => $d['link'],
            '{days}' => $d['days'],
            '{account_type}' => $d['account_type'],
        );

        if (!self::sendMailTpl($d['user_email'], $tpl, $langId, $vars)) {
            return false;
        }
       
        return true;
    }
	
}
