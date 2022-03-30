<?php

require_once CONF_INSTALLATION_PATH . 'vendor/autoload.php';
require_once CONF_INSTALLATION_PATH . 'library/GoogleFonts.class.php';

use Curl\Curl;

class ThemeColorController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    private $apiKey;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewThemeColor($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditThemeColor($this->admin_id, true);
        $this->apiKey = FatApp::getConfig('CONF_GOOGLE_FONTS_API_KEY', FatUtility::VAR_STRING, '');
        $this->set("apiKey", $this->apiKey);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewThemeColor();
        $activeThemeId = FatApp::getConfig('CONF_ACTIVE_THEME_ID', FatUtility::VAR_INT, 1);
        $theme = new Theme($activeThemeId);
        $themeDetail = $theme->getDetail();

        if (array_key_exists('theme_font_family', $themeDetail) && ('' == $themeDetail['theme_font_family'] || 'Poppins' == $themeDetail['theme_font_family'])) {
            $themeDetail['theme_font_family'] = 'Poppins-regular';
        }

        $frm = $this->getFontsForm();
        $frm->fill($themeDetail);
        $this->set('frm', $frm);
        $this->set('themeDetail', $themeDetail);
        $this->set('activeThemeId', $activeThemeId);
        $this->set('themeArr', $theme->detailOfAllThemes());
        $this->set('formLayout', Language::getLayoutDirection($this->adminLangId));
        $this->_template->addJs(array('js/select2.js', 'js/jscolor.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        // $this->_template->addJs('js/slick.min.js');
        $this->_template->addJs(array('js/slick.js', 'js/modaal.js', 'js/product-detail.js', 'js/xzoom.js', 'js/magnific-popup.js'));
        $this->_template->render();
    }

    public function themeForm()
    {
        $themeId = FatApp::getPostedData('themeId', FatUtility::VAR_INT, 0);
        if ($themeId < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }

        $theme = new Theme($themeId);
        $themeDetail = $theme->getDetail();
        if (array_key_exists('theme_font_family', $themeDetail) && ('' == $themeDetail['theme_font_family'] || 'Poppins' == $themeDetail['theme_font_family'])) {
            $themeDetail['theme_font_family'] = 'Poppins-regular';
        }

        $frm = $this->getFontsForm();
        $frm->fill($themeDetail);
        $this->set('frm', $frm);
        $this->set('fontWeights', unserialize($themeDetail["theme_font_weights"]));
        $this->set('adminLangId', $this->adminLangId);
        $this->set('formLayout', Language::getLayoutDirection($this->adminLangId));

        $this->_template->render(false, false);
    }

    private function getFontsForm()
    {
        $frm = new Form('frmGoogleFonts');
        $frm->addHiddenField('', 'theme_id');
        $frm->addHiddenField("", 'theme_font_family_url');
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Select_FONT_FAMILY', $this->adminLangId), 'theme_font_family', [], '', array('placeholder' => Labels::getLabel('LBL_Select_font_family', $this->adminLangId)));
        $fld->requirement->setRequired(true);
        $link = "<a href='https://fonts.google.com' target='_blank'>https://fonts.google.com</a>";
        $url = CommonHelper::replaceStringData(Labels::getLabel('LBL_REFERENCE_:_{URL}', $this->adminLangId), ['{URL}' => $link]);
        $fld->htmlAfterField = '<small>' . $url . ' </small>';
        

        $frm->addSelectBox(Labels::getLabel('LBL_Select_Font_Weight', $this->adminLangId), 'theme_font_weight[]', [], '', [], Labels::getLabel('LBL_Select_font_weight', $this->adminLangId));
        $frm->addRequiredField(Labels::getLabel('LBL_THEME_COLOR', $this->adminLangId), 'theme_color');
        $frm->addRequiredField(Labels::getLabel('LBL_THEME_COLOR_INVERSE', $this->adminLangId), 'theme_color_inverse');
        $activeTheme = applicationConstants::getActiveTheme();
        if ($activeTheme == applicationConstants::THEME_HEAVY_EQUIPMENT) {
            $frm->addRequiredField(Labels::getLabel('LBL_THEME_SECONDARY_COLOR', $this->adminLangId), 'theme_secondary_color');
            /* $frm->addRequiredField(Labels::getLabel('LBL_THEME_SECONDARY_COLOR_INVERSE', $this->adminLangId), 'theme_secondary_color_inverse'); */
        }
        
        
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_&_activate_theme', $this->adminLangId));
        //$fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_RESET', $this->adminLangId), ['title' => Labels::getLabel('LBL_RESET_TO_DEFAULT_VALUES', $this->adminLangId)]);
        //$fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function getGoogleFonts()
    {
        $this->objPrivilege->canEditThemeColor();
        
        $srch = new SearchBase(Theme::DB_FONT_FAMILY, 'fnt');
        $srch->joinTable(Theme::DB_FONT_FAMILY_VARIANTS, 'LEFT JOIN', 'gfont_id = fvariant_gfont_id', 'fvar');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $googleFontArr = FatApp::getDb()->fetchAll($rs);
        
        if (empty($googleFontArr)) {
            FatUtility::dieJsonSuccess([]);
        }
        
        $fontVarArr = [];
        foreach($googleFontArr as $gfont) {
            $fontVarArr[$gfont['gfont_id']]['name'] = $gfont['gfont_name'];
            $fontVarArr[$gfont['gfont_id']]['variants'][] = $gfont['fvariant_name'];
        }

        $fonts = [];
        $variantsArr = [];
        foreach ($fontVarArr as $font) {
            $fontName = str_replace(' ', '+', $font['name']);
            $fonts[] = [
                'id' => $fontName,
                'name' => $font['name'],
                'text' => $fontName,
            ];
            $variantsArr[$fontName] = $font['variants'];
        }

        $data = array(
            'fonts' => $fonts,
            'variantsArr' => $variantsArr,
        );
    
        FatUtility::dieJsonSuccess($data);
    }

    public function getGoogleFonts_bkup()
    {
        $this->objPrivilege->canEditThemeColor();
        if (empty($this->apiKey)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_API_KEY_FOR_GOOGLE_FONTS_NOT_CONFIGURED', $this->adminLangId));
        }

        $curl = new Curl();
        $curl->get('https://www.googleapis.com/webfonts/v1/webfonts?key=' . $this->apiKey);
        if ($curl->error) {
            FatUtility::dieJsonError($curl->errorCode . ': ' . $curl->errorMessage);
        }

        if (!isset($curl->response->items)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_UNABLE_TO_LOAD_FONTS', $this->adminLangId));
        }

        $googleFontsResp = json_decode(json_encode($curl->response), true);

        $fonts = [];
        foreach ($googleFontsResp['items'] as $font) {
            $fontName = str_replace(' ', '+', $font['family']);
            $i = 1;
            $allWeights = [];
            $allSubsets = [];
            foreach ($font['variants'] as $variant) {
                $name = $fontName . '-' . $variant;
                $fonts[] = [
                    'id' => $name,
                    'name' => $font['family'] . ' - ' . ucwords($variant),
                    'text' => $name,
                    'weight' => $variant,
                    'subset' => implode(',', $font['subsets']),
                ];

                $allWeights[] = $variant;
                $allSubsets = array_merge($allSubsets, $font['subsets']);
                if (1 < count($font['variants']) && $i == count($font['variants'])) {
                    $fonts[] = [
                        'id' => $fontName . '-' . Labels::getLabel('LBL_ALL', $this->adminLangId),
                        'name' => $font['family'] . ' - ' . Labels::getLabel('LBL_ALL', $this->adminLangId),
                        'text' => $fontName . '-' . Labels::getLabel('LBL_ALL', $this->adminLangId),
                        'weight' => implode(',', $allWeights),
                        'subset' => implode(',', array_unique($allSubsets)),
                    ];
                }
                $i++;
            }
        }
        FatUtility::dieJsonSuccess(['fonts' => $fonts]);
    }

    public function loadGoogleFont()
    {
        if (empty(FatApp::getPostedData('name', FatUtility::VAR_STRING, ''))) {
            $json['html'] = '';
            FatUtility::dieJsonError($json);
        }

        $font = new GoogleFonts(FatApp::getPostedData(), true);
        $json['html'] = $font->load();
        FatUtility::dieJsonSuccess($json);
    }

    public function setup()
    {
        $this->objPrivilege->canEditThemeColor();
        $frm = $this->getFontsForm();
        $themeFontFamily = FatApp::getPostedData('theme_font_family', FatUtility::VAR_STRING, Theme::DEFAULT_FONT_FAMILY);
        $themeFontWeight = FatApp::getPostedData('theme_font_weight');
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            FatUtility::dieJsonError(current($frm->getValidationErrors()));
        }
        
        if ((FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl())) {
            $post['theme_id'] = FatApp::getConfig('CONF_ACTIVE_THEME_ID', FatUtility::VAR_INT, 1);
        }

        if (intval($post['theme_id']) < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $theme = new Theme($post['theme_id']);
        $themeDetail = $theme->getDetail();

        if (empty($themeDetail)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $confData = array('CONF_ACTIVE_THEME_ID' => $post['theme_id']);
        $record = new Configurations();
        if (!$record->update($confData)) {
            FatUtility::dieJsonError($record->getError());
        }

        if (empty($themeFontWeight)) {
            $themeFontWeight = [];
        }

        $post['theme_font_family'] = $themeFontFamily;
        $post['theme_font_weights'] = serialize($themeFontWeight);
        $record = new Theme($post['theme_id']);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_SETUP_SUCCESSFULLY', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function resetToDefault()
    {
        $this->objPrivilege->canEditThemeColor();

        $font = empty($this->apiKey) ? 'Poppins' : 'Poppins-regular';

        $data = [
            "CONF_THEME_FONT_FAMILY_URL" => "",
            "CONF_THEME_FONT_FAMILY" => $font,
            "CONF_THEME_COLOR" => "#ff3a59",
            "CONF_THEME_COLOR_INVERSE" => "#fff",
        ];

        $record = new Configurations();
        if (!$record->update($data)) {
            FatUtility::dieJsonError($record->getError());
        }

        $this->set('msg', Labels::getLabel('MSG_COMPLETED', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}