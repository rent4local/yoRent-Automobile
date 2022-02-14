<?php

class VerificationFields extends MyAppModel
{
    public const DB_TBL = 'tbl_verification_flds';
    public const DB_TBL_PREFIX = 'vflds_';

    public const DB_TBL_LANG = 'tbl_verification_flds_lang';
    public const DB_TBL_LANG_PREFIX = 'vfldslang_';

    public const FLD_TYPE_TEXTBOX = 1;   
    public const FLD_TYPE_FILE = 2;   

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $isActive = false)
    {
        $srch = new SearchBase(static::DB_TBL, 'vf');

        if ($isActive == true) {
            $srch->addCondition('vf.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        if($langId > 0){
			$srch->joinTable(static::DB_TBL_LANG,'LEFT OUTER JOIN',
			'vf_l.'.static::DB_TBL_LANG_PREFIX.'vflds_id = vf.'.static::tblFld('id').' and
			vf_l.'.static::DB_TBL_LANG_PREFIX.'lang_id = '.$langId,'vf_l');
		}

        $srch->addOrder('vf.' . static::DB_TBL_PREFIX . 'active', 'DESC');
        return $srch;
    }


    public static function getFldTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
            static::FLD_TYPE_TEXTBOX => Labels::getLabel('LBL_Textbox', $langId),
            static::FLD_TYPE_FILE => Labels::getLabel('LBL_File', $langId)
        );
    }

    public function addUpdateData($post,$lang_id)
    {
		if (empty($post)) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $lang_id);
            return false;
        }
        
		unset($post['vflds_id']);
		
		$data = [
			'vflds_identifier' => $post['vflds_name'][$lang_id],
			'vflds_type' =>  $post['vflds_type'],
			'vflds_required' =>  $post['vflds_required'],
			'vflds_active' =>  $post['vflds_active'],
		];
        $this->assignValues($data);
        if (!$this->save()) {
            $this->error = $this->getError();
            return false;
        }
		
        $autoUpdateOtherLangsData = isset($post['auto_update_other_langs_data']) ? FatUtility::int($post['auto_update_other_langs_data']) : 0;
        foreach ($post['vflds_name'] as $langId => $vfldsName) {
            if (empty($vfldsName) && $autoUpdateOtherLangsData > 0) {
                $this->saveTranslatedLangData($langId);
            } elseif (!empty($vfldsName)) {
                $data = array(
                     static::DB_TBL_LANG_PREFIX . 'vflds_id' => $this->mainTableRecordId,
                     static::DB_TBL_LANG_PREFIX . 'lang_id' => $langId,
                    'vflds_name' => $vfldsName,
                );
                if (!$this->updateLangData($langId, $data)) {
                    $this->error = $this->getError();
                    return false;
                }
            }
        }

        return true;
    }

    public function activateField($v = 1)
    {
        if (!($this->mainTableRecordId > 0)) {
            $this->error = Labels::getLabel('ERR_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        $db = FatApp::getDb();
        if (!$db->updateFromArray(
            static::DB_TBL,
            array(
                static::DB_TBL_PREFIX . 'active' => $v
            ),
            array(
                'smt' => static::DB_TBL_PREFIX . 'id = ?',
                'vals' => array(
                    $this->mainTableRecordId
                )
            )
        )) {
            $this->error = $db->getError();
            return false;
        }

        return true;
    }

    public function getTranslatedData($data, $toLangId)
    {
        $toLangId = FatUtility::int($toLangId);
        if (empty($data) || $toLangId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $translateLangobj = new TranslateLangData(static::DB_TBL_LANG);
        $translatedData = $translateLangobj->directTranslate($data, $toLangId);
        if (false === $translatedData) {
            $this->error = $translateLangobj->getError();
            return false;
        }
        return $translatedData;
    }

    public function saveTranslatedLangData($langId): bool
    {
        $langId = FatUtility::int($langId);
        if ($this->mainTableRecordId < 1 || $langId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $translateLangobj = new TranslateLangData(static::DB_TBL_LANG);
        if (false === $translateLangobj->updateTranslatedData($this->mainTableRecordId, 0, $langId)) {
            $this->error = $translateLangobj->getError();
            return false;
        }
        return true;
    }

}