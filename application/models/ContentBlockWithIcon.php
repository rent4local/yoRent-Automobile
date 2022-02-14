<?php

class ContentBlockWithIcon extends MyAppModel
{
    public const DB_TBL = 'tbl_content_block_sections';
    public const DB_TBL_PREFIX = 'cbs_';

    public const DB_TBL_LANG = 'tbl_content_block_sections_lang';
    public const DB_TBL_LANG_PREFIX = 'cbslang_';

    public function __construct($blockId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $blockId);
    }

    public static function getSearchObject($langId = 0, $isActive = true)
    {
        $srch = new SearchBase(static::DB_TBL, 'cbs');

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'cbs_l.' . static::DB_TBL_LANG_PREFIX . 'cbs_id = cbs.' . static::tblFld('id') . ' and
			cbs_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'cbs_l'
            );
        }

        if ($isActive) {
            $srch->addCondition('cbs_active', '=', applicationConstants::ACTIVE);
        }

        return $srch;
    }

   
    /**
     * saveTranslatedLangData
     *
     * @param  int $langId
     * @return bool
     */
    public function saveTranslatedLangData(int $langId): bool
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

    /**
     * getTranslatedData
     *
     * @param  array $data
     * @param  int $toLangId
     * @return bool
     */
    public function getTranslatedData(array $data, int $toLangId)
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

    public function saveLangData(int $langId, string $name, string $desc = ''): bool
    {
        $langId = FatUtility::int($langId);
        if ($this->mainTableRecordId < 1 || $langId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $data = array(
            'cbslang_cbs_id' => $this->mainTableRecordId,
            'cbslang_lang_id' => $langId,
            'cbs_name' => $name,
            'cbslang_description' => $desc,
        );

        if (!$this->updateLangData($langId, $data)) {
            $this->error = $this->getError();
            return false;
        }
        return true;
    }
}
