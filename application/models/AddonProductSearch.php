<?php

class AddonProductSearch extends SearchBase
{

    public function __construct(int $langId = 0)
    {
        parent::__construct(AddonProduct::DB_TBL, 'ap');

        if ($langId > 0) {
            $this->joinTable(AddonProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', AddonProduct::DB_TBL_LANG_PREFIX . 'addonprod_id = addonprod_id AND ' . AddonProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'ap_lang');
        }
    }

    public function joinAddonProducts()
    {
        $this->joinTable(AddonProduct::DB_TBL, 'LEFT JOIN', 'ap.sps_selprod_id = selprod_id', 'ap');
    }

}
