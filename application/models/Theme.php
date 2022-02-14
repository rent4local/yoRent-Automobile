<?php

class Theme extends MyAppModel
{
    public const DB_TBL = 'tbl_theme';
    public const DB_TBL_PREFIX = 'theme_';
    public const DEFAULT_FONT_FAMILY = 'Poppins';
    public const DEFAULT_FONT_FAMILY_URL = 'https://fonts.googleapis.com/css?family=Poppins:regular&subset=devanagari,latin,latin-ext';
    public const DEFAULT_FONT_FAMILY_FOR_FRONTEND = "'Poppins', sans-serif";
    public const DB_FONT_FAMILY = "tbl_google_fonts";
    public const DB_FONT_FAMILY_VARIANTS = "tbl_google_font_variants";

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($active = true)
    {
        $srch = new SearchBase(static::DB_TBL, 't');
        if ($active == true) {
            $srch->addCondition('theme_status', '=', applicationConstants::YES);
        }
        return $srch;
    }

    public static function getAllThemesArr()
    {
        $srch = SELF::getSearchObject();
    }

    public function detailOfAllThemes()
    {
        $srch = SELF::getSearchObject(true);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public function getDetail()
    {
        $srch = SELF::getSearchObject(false);
		$srch->doNotCalculateRecords();
        $srch->addCondition('theme_id', '=', $this->mainTableRecordId);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

}
