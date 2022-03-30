<?php

class Collections extends MyAppModel
{

    public const DB_TBL = 'tbl_collections';
    public const DB_TBL_PREFIX = 'collection_';
    public const DB_TBL_LANG = 'tbl_collections_lang';
    public const DB_TBL_LANG_PREFIX = 'collectionlang_';
    public const DB_TBL_COLLECTION_TO_RECORDS = 'tbl_collection_to_records';
    public const DB_TBL_COLLECTION_TO_RECORDS_PREFIX = 'ctr_';

    //public const TYPE_PRODUCT = 1;
    public const COLLECTION_TYPE_PRODUCT = 1;
    public const COLLECTION_TYPE_CATEGORY = 2;
    public const COLLECTION_TYPE_SHOP = 3;
    public const COLLECTION_TYPE_BRAND = 4;
    public const COLLECTION_TYPE_BLOG = 5;
    public const COLLECTION_TYPE_SPONSORED_PRODUCTS = 6;
    public const COLLECTION_TYPE_SPONSORED_SHOPS = 7;
    public const COLLECTION_TYPE_BANNER = 8;
    public const COLLECTION_TYPE_FAQ = 9;
    public const COLLECTION_TYPE_TESTIMONIAL = 10;
    public const COLLECTION_TYPE_CONTENT_BLOCK = 11;
    public const COLLECTION_TYPE_REVIEWS = 12;
    
    public const COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON = 13;
    public const COLLECTION_TYPE_SUB_COLLECTION = 14;

    //public const SUBTYPE_PRODUCT_LAYOUT1 = 1;
    public const TYPE_PRODUCT_LAYOUT1 = 1;
    public const TYPE_PRODUCT_LAYOUT2 = 2;
    public const TYPE_PRODUCT_LAYOUT3 = 3;
    public const TYPE_CATEGORY_LAYOUT1 = 4;
    public const TYPE_CATEGORY_LAYOUT2 = 5;
    public const TYPE_SHOP_LAYOUT1 = 6;
    public const TYPE_BRAND_LAYOUT1 = 7;
    public const TYPE_BLOG_LAYOUT1 = 8;
    public const TYPE_SPONSORED_PRODUCT_LAYOUT = 9;
    public const TYPE_SPONSORED_SHOP_LAYOUT = 10;
    public const TYPE_BANNER_LAYOUT1 = 11;
    public const TYPE_BANNER_LAYOUT2 = 12;
    public const TYPE_BANNER_LAYOUT3 = 13;
    
    public const TYPE_FAQ_LAYOUT1 = 14;
    public const TYPE_TESTIMONIAL_LAYOUT1 = 15;
    public const TYPE_CONTENT_BLOCK_LAYOUT1 = 16;
    public const TYPE_PENDING_REVIEWS1 = 17; // Applicable For Apps only.

    /* [ YO!RENT V2 SPECIFIC LAYOUTS */
    public const TYPE_SHOP_LAYOUT2 = 18;
    public const TYPE_BRAND_LAYOUT2 = 19;
    public const TYPE_BLOG_LAYOUT2 = 20;
    public const TYPE_BANNER_LAYOUT4 = 21;
    public const TYPE_FAQ_LAYOUT2 = 22;
    public const TYPE_TESTIMONIAL_LAYOUT2 = 23;
    public const TYPE_PRODUCT_LAYOUT4 = 24;
    public const TYPE_BANNER_LAYOUT5 = 25;
    public const TYPE_CATEGORY_LAYOUT3 = 26;
    public const TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT1 = 27;
    public const TYPE_CATEGORY_LAYOUT4 = 28;
    public const TYPE_PRODUCT_LAYOUT5 = 29;
    public const TYPE_SUB_COLLECTION_LAYOUT1 = 30;
	public const TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT2 = 31;
	public const TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT3 = 32;
    public const TYPE_BANNER_LAYOUT_DETAIL = 33;
	/* ] */

    public const COLLECTION_CRITERIA_PRICE_LOW_TO_HIGH = 1;
    public const COLLECTION_CRITERIA_PRICE_HIGH_TO_LOW = 2;

    public const BANNER_POSITION_LEFT = 1;
    public const BANNER_POSITION_RIGHT = 2;

    public const GRID_VIEW_ORDER_1_MIN_WIDTH = 682;
    public const GRID_VIEW_ORDER_1_MIN_HEIGHT = 470;

    public const GRID_VIEW_ORDER_2_MIN_WIDTH = 516;
    public const GRID_VIEW_ORDER_2_MIN_HEIGHT = 470;

    public const GRID_VIEW_ORDER_3_MIN_WIDTH = 682;
    public const GRID_VIEW_ORDER_3_MIN_HEIGHT = 960;

    public const GRID_VIEW_ORDER_4_MIN_WIDTH = 516;
    public const GRID_VIEW_ORDER_4_MIN_HEIGHT = 470;

    public const GRID_VIEW_ORDER_5_MIN_WIDTH = 784;
    public const GRID_VIEW_ORDER_5_MIN_HEIGHT = 470;

    
    

    /* public const COLLECTION_WITHOUT_MEDIA = [
        self::COLLECTION_TYPE_SHOP,
        self::COLLECTION_TYPE_BRAND,
        self::COLLECTION_TYPE_BLOG,
        self::COLLECTION_TYPE_SPONSORED_PRODUCTS,
        self::COLLECTION_TYPE_SPONSORED_SHOPS,
        self::COLLECTION_TYPE_BANNER,
        self::COLLECTION_TYPE_FAQ,
        self::COLLECTION_TYPE_TESTIMONIAL,
        self::COLLECTION_TYPE_CONTENT_BLOCK,
        self::COLLECTION_TYPE_REVIEWS,
        self::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON,
    ]; */

    public const COLLECTION_WITHOUT_RECORDS = [
        self::COLLECTION_TYPE_SPONSORED_PRODUCTS,
        self::COLLECTION_TYPE_SPONSORED_SHOPS,
        self::COLLECTION_TYPE_BANNER,
        self::COLLECTION_TYPE_CONTENT_BLOCK,
        self::COLLECTION_TYPE_REVIEWS,
        self::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON,
    ];

    public const APP_COLLECTIONS_ONLY = [
        self::TYPE_BANNER_LAYOUT3,
        self::TYPE_PENDING_REVIEWS1
    ];
    
    public const LAYOUT_WITH_MEDIA = [
        self::TYPE_CATEGORY_LAYOUT4,
        self::TYPE_TESTIMONIAL_LAYOUT2,
		self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT3,
		self::TYPE_BLOG_LAYOUT1,
    ];
    

    /**
     * __construct
     *
     * @param  int $id
     * @return void
     */
    public function __construct(int $id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

	public static function layoutArrByTheme()
	{
		return [ /* key = layout id, value = record limit */
			applicationConstants::THEME_DEFAULT =>[
				static::TYPE_PRODUCT_LAYOUT1 => 12,
				static::TYPE_PRODUCT_LAYOUT2 => 6,
				static::TYPE_PRODUCT_LAYOUT3 => 12,
				static::TYPE_CATEGORY_LAYOUT1 => 8,
				static::TYPE_CATEGORY_LAYOUT2 => 4,
				static::TYPE_SHOP_LAYOUT1 => 4,
				static::TYPE_BRAND_LAYOUT1 => 5,
				static::TYPE_BLOG_LAYOUT1 => 3,
				static::TYPE_SPONSORED_PRODUCT_LAYOUT => 12,
				static::TYPE_SPONSORED_SHOP_LAYOUT => 12,
				static::TYPE_BANNER_LAYOUT1 => 2,
				static::TYPE_BANNER_LAYOUT2 => 2,
				static::TYPE_BANNER_LAYOUT3 => 2,
				static::TYPE_FAQ_LAYOUT1 => 6,
				static::TYPE_TESTIMONIAL_LAYOUT1 => 10,
				static::TYPE_CONTENT_BLOCK_LAYOUT1 => 1,
				static::TYPE_PENDING_REVIEWS1 => 1,
			],
			applicationConstants::THEME_FASHION => [
                static::TYPE_SPONSORED_PRODUCT_LAYOUT => 12,
				static::TYPE_SPONSORED_SHOP_LAYOUT => 12,
				static::TYPE_SHOP_LAYOUT2 => 8,
				static::TYPE_BRAND_LAYOUT2 => 10,
				static::TYPE_BLOG_LAYOUT2 => 3,
				static::TYPE_BANNER_LAYOUT4 => 2,
				static::TYPE_FAQ_LAYOUT2 => 5,
				static::TYPE_TESTIMONIAL_LAYOUT2 => 10,
				static::TYPE_PRODUCT_LAYOUT4 => 6,
				static::TYPE_BANNER_LAYOUT5 => 2,
				static::TYPE_CATEGORY_LAYOUT3 => 10,
				static::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT1 => 4,
				static::TYPE_CATEGORY_LAYOUT4 => 5,
				static::TYPE_PRODUCT_LAYOUT5 => 5,
				static::TYPE_SUB_COLLECTION_LAYOUT1 => 4,
			],
			applicationConstants::THEME_HEAVY_EQUIPMENT => [
				static::TYPE_PRODUCT_LAYOUT1 => 12,
				static::TYPE_PRODUCT_LAYOUT5 => 4,
                static::TYPE_SUB_COLLECTION_LAYOUT1 => 4,
				
				static::TYPE_CATEGORY_LAYOUT2 => 4,
                static::TYPE_CATEGORY_LAYOUT4 => 5,
				
				static::TYPE_SHOP_LAYOUT1 => 4,
				static::TYPE_BRAND_LAYOUT1 => 12,
				static::TYPE_BLOG_LAYOUT1 => 4,
				static::TYPE_SPONSORED_PRODUCT_LAYOUT => 12,
				static::TYPE_SPONSORED_SHOP_LAYOUT => 12,
				/* static::TYPE_BANNER_LAYOUT2 => 2, */
				static::TYPE_BANNER_LAYOUT4 => 2,
				static::TYPE_FAQ_LAYOUT1 => 12,
				static::TYPE_TESTIMONIAL_LAYOUT1 => 12,
			],
			applicationConstants::THEME_AUTOMOBILE => [
				static::TYPE_PRODUCT_LAYOUT1 => 12,
				static::TYPE_PRODUCT_LAYOUT2 => 12,
				static::TYPE_PRODUCT_LAYOUT3 => 3,
				static::TYPE_PRODUCT_LAYOUT5 => 5,
				static::TYPE_SPONSORED_PRODUCT_LAYOUT => 12,
				static::TYPE_SPONSORED_SHOP_LAYOUT => 12,
				static::TYPE_SUB_COLLECTION_LAYOUT1 => 1,
				
				static::TYPE_CATEGORY_LAYOUT4 => 6,
				
				static::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT1 => 6,
				static::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT2 => 4,
				static::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT3 => 6,
				
				static::TYPE_BANNER_LAYOUT1 => 1,
				static::TYPE_BRAND_LAYOUT2 => 10,
				static::TYPE_BLOG_LAYOUT1 => 3,
				static::TYPE_TESTIMONIAL_LAYOUT1 => 10,
				static::TYPE_FAQ_LAYOUT1 => 12,
			],
		];
	}
	
	/**
     * getSearchObject
     *
     * @param  bool $isActive
     * @param  int $langId
     * @return object
     */
    public static function getSearchObject(bool $isActive = true, int $langId = 0): object
    {
        $srch = new SearchBase(static::DB_TBL, 'c');

        $srch->addCondition('c.' . static::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        if ($isActive == true) {
            $srch->addCondition('c.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                    static::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_l.' . static::DB_TBL_LANG_PREFIX . 'collection_id = c.' . static::tblFld('id') . ' and
			    c_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'c_l'
            );
        }

        $srch->addCondition('collection_type', '!=', self::COLLECTION_TYPE_CONTENT_BLOCK);

        return $srch;
    }

    /**
     * getTypeArr
     *
     * @param  int $langId
     * @return array
     */
    public static function getTypeArr(int $langId): array
    {
        if (1 > $langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        return [
            self::COLLECTION_TYPE_PRODUCT => Labels::getLabel('LBL_Product', $langId),
            self::COLLECTION_TYPE_CATEGORY => Labels::getLabel('LBL_Category', $langId),
            self::COLLECTION_TYPE_SHOP => Labels::getLabel('LBL_Shop', $langId),
            self::COLLECTION_TYPE_BRAND => Labels::getLabel('LBL_Brand', $langId),
            self::COLLECTION_TYPE_BLOG => Labels::getLabel('LBL_Blog', $langId),
            self::COLLECTION_TYPE_SPONSORED_PRODUCTS => Labels::getLabel('LBL_Sponsored_Products', $langId),
            self::COLLECTION_TYPE_SPONSORED_SHOPS => Labels::getLabel('LBL_Sponsored_Shops', $langId),
            self::COLLECTION_TYPE_BANNER => Labels::getLabel('LBL_Banner', $langId),
            self::COLLECTION_TYPE_FAQ => Labels::getLabel('LBL_FAQ', $langId),
            self::COLLECTION_TYPE_TESTIMONIAL => Labels::getLabel('LBL_Testimonial', $langId),
            self::COLLECTION_TYPE_CONTENT_BLOCK => Labels::getLabel('LBL_Content_Blocks', $langId),
            self::COLLECTION_TYPE_REVIEWS => Labels::getLabel('LBL_REVIEWS', $langId),
            self::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON => Labels::getLabel('LBL_CONTENT_BLOCKS_WITH_ICON', $langId),
        ];
    }

    /**
     * getLayoutTypeArr
     *
     * @param  int $langId
     * @return array
     */
    public static function getLayoutTypeArr(int $langId): array
    {
        if (1 > $langId) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }

        $layoutArr = [
            self::TYPE_PRODUCT_LAYOUT1 => Labels::getLabel('LBL_Product_Layout1', $langId),
            self::TYPE_PRODUCT_LAYOUT2 => Labels::getLabel('LBL_Product_Layout2', $langId),
            self::TYPE_PRODUCT_LAYOUT3 => Labels::getLabel('LBL_Product_Layout3', $langId),
            self::TYPE_PRODUCT_LAYOUT4 => Labels::getLabel('LBL_Product_Layout4', $langId),
            self::TYPE_PRODUCT_LAYOUT5 => Labels::getLabel('LBL_Product_Layout5', $langId),
            self::TYPE_CATEGORY_LAYOUT1 => Labels::getLabel('LBL_Category_Layout1', $langId),
            self::TYPE_CATEGORY_LAYOUT2 => Labels::getLabel('LBL_Category_Layout2', $langId),
            self::TYPE_CATEGORY_LAYOUT3 => Labels::getLabel('LBL_Category_Layout3', $langId),
            self::TYPE_CATEGORY_LAYOUT4 => Labels::getLabel('LBL_Category_Layout4(Grid_Layout)', $langId),
            self::TYPE_SHOP_LAYOUT1 => Labels::getLabel('LBL_Shop_Layout1', $langId),
            self::TYPE_SHOP_LAYOUT2 => Labels::getLabel('LBL_Shop_Layout2', $langId),
            self::TYPE_BRAND_LAYOUT1 => Labels::getLabel('LBL_Brand_Layout1', $langId),
            self::TYPE_BRAND_LAYOUT2 => Labels::getLabel('LBL_Brand_Layout2', $langId),
            self::TYPE_BLOG_LAYOUT1 => Labels::getLabel('LBL_Blog_Layout1', $langId),
            self::TYPE_BLOG_LAYOUT2 => Labels::getLabel('LBL_Blog_Layout2', $langId),
            self::TYPE_SPONSORED_PRODUCT_LAYOUT => Labels::getLabel('LBL_Sponsored_Products', $langId),
            self::TYPE_SPONSORED_SHOP_LAYOUT => Labels::getLabel('LBL_Sponsored_Shops', $langId),
            self::TYPE_BANNER_LAYOUT1 => Labels::getLabel('LBL_Banner_Layout1', $langId),
            self::TYPE_BANNER_LAYOUT2 => Labels::getLabel('LBL_Banner_Layout2', $langId),
            self::TYPE_BANNER_LAYOUT4 => Labels::getLabel('LBL_Banner_Layout3', $langId),
            self::TYPE_BANNER_LAYOUT5 => Labels::getLabel('LBL_Banner_Layout4', $langId),
            self::TYPE_BANNER_LAYOUT3 => Labels::getLabel('LBL_Mobile_Banner_Layout', $langId),
            self::TYPE_FAQ_LAYOUT1 => Labels::getLabel('LBL_Faq_Layout1', $langId),
            self::TYPE_FAQ_LAYOUT2 => Labels::getLabel('LBL_Faq_Layout2', $langId),
            self::TYPE_TESTIMONIAL_LAYOUT1 => Labels::getLabel('LBL_Testimonial_Layout_1', $langId),
            self::TYPE_TESTIMONIAL_LAYOUT2 => Labels::getLabel('LBL_Testimonial_Layout_2', $langId),
            self::TYPE_CONTENT_BLOCK_LAYOUT1 => Labels::getLabel('LBL_Content_block_Layout1', $langId),
            self::TYPE_PENDING_REVIEWS1 => Labels::getLabel('LBL_PENDING_REVIEWS', $langId),
            self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT1 => Labels::getLabel('LBL_Content_Block_with_icon_layout1', $langId),
            self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT2 => Labels::getLabel('LBL_Content_Block_with_icon_layout2', $langId),
            self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT3 => Labels::getLabel('LBL_Content_Block_layout3', $langId),
        ];
        
        $activeTheme = applicationConstants::getActiveTheme();
		$activeThemeLayoutsArr = (isset(Collections::layoutArrByTheme()[$activeTheme])) ? Collections::layoutArrByTheme()[$activeTheme] : [];
        
        return array_intersect_key($layoutArr, $activeThemeLayoutsArr);
    }

    /**
     * getTypeSpecificLayouts
     *
     * @param  int $langId
     * @return array
     */
    public static function getTypeSpecificLayouts(int $langId): array
    {
        return [
            self::COLLECTION_TYPE_BANNER => [
                self::TYPE_BANNER_LAYOUT1 => Labels::getLabel('LBL_Banner_Layout', $langId),
                self::TYPE_BANNER_LAYOUT2 => Labels::getLabel('LBL_Banner_Layout', $langId),
                self::TYPE_BANNER_LAYOUT4 => Labels::getLabel('LBL_Banner_Layout', $langId),
                self::TYPE_BANNER_LAYOUT5 => Labels::getLabel('LBL_Banner_Layout', $langId),
                self::TYPE_BANNER_LAYOUT3 => Labels::getLabel('LBL_Mobile_Banner_Layout', $langId),
            ],
            self::COLLECTION_TYPE_BRAND => [
                self::TYPE_BRAND_LAYOUT1 => Labels::getLabel('LBL_Brand_Layout', $langId),
                self::TYPE_BRAND_LAYOUT2 => Labels::getLabel('LBL_Brand_Layout', $langId),
            ],
            self::COLLECTION_TYPE_BLOG => [
                self::TYPE_BLOG_LAYOUT1 => Labels::getLabel('LBL_Blog_Layout', $langId),
                self::TYPE_BLOG_LAYOUT2 => Labels::getLabel('LBL_Blog_Layout', $langId),
            ],
            self::COLLECTION_TYPE_CATEGORY => [
                self::TYPE_CATEGORY_LAYOUT1 => Labels::getLabel('LBL_Category_Layout', $langId),
                self::TYPE_CATEGORY_LAYOUT2 => Labels::getLabel('LBL_Category_Layout', $langId),
                self::TYPE_CATEGORY_LAYOUT3 => Labels::getLabel('LBL_Category_Layout', $langId),
                self::TYPE_CATEGORY_LAYOUT4 => Labels::getLabel('LBL_Category_Layout(Grid_Layout)', $langId),
            ],
            self::COLLECTION_TYPE_FAQ => [
                self::TYPE_FAQ_LAYOUT1 => Labels::getLabel('LBL_FAQ_layout', $langId),
                self::TYPE_FAQ_LAYOUT2 => Labels::getLabel('LBL_FAQ_layout', $langId),
            ],
            self::COLLECTION_TYPE_REVIEWS => [
                self::TYPE_PENDING_REVIEWS1 => Labels::getLabel('LBL_PENDING_REVIEWS', $langId),
            ],
            self::COLLECTION_TYPE_PRODUCT => [
                self::TYPE_PRODUCT_LAYOUT1 => Labels::getLabel('LBL_Product_Layout', $langId),
                self::TYPE_PRODUCT_LAYOUT2 => Labels::getLabel('LBL_Product_Layout', $langId),
                self::TYPE_PRODUCT_LAYOUT3 => Labels::getLabel('LBL_Product_Layout', $langId),
                self::TYPE_PRODUCT_LAYOUT4 => Labels::getLabel('LBL_Product_Layout', $langId),
                self::TYPE_PRODUCT_LAYOUT5 => Labels::getLabel('LBL_Product_Layout', $langId),
            ],
            self::COLLECTION_TYPE_SHOP => [
                self::TYPE_SHOP_LAYOUT1 => Labels::getLabel('LBL_Shop_Layout', $langId),
                self::TYPE_SHOP_LAYOUT2 => Labels::getLabel('LBL_Shop_Layout', $langId),
            ],
            self::COLLECTION_TYPE_SPONSORED_PRODUCTS => [
                self::TYPE_SPONSORED_PRODUCT_LAYOUT => Labels::getLabel('LBL_Sponsored_Products', $langId),
            ],
            self::COLLECTION_TYPE_SPONSORED_SHOPS => [
                self::TYPE_SPONSORED_SHOP_LAYOUT => Labels::getLabel('LBL_Sponsored_Shops', $langId),
            ],
            self::COLLECTION_TYPE_TESTIMONIAL => [
                self::TYPE_TESTIMONIAL_LAYOUT1 => Labels::getLabel('LBL_Testimonial_Layout', $langId),
                self::TYPE_TESTIMONIAL_LAYOUT2 => Labels::getLabel('LBL_Testimonial_Layout', $langId),
            ],
            /* self::COLLECTION_TYPE_CONTENT_BLOCK => [
              self::TYPE_CONTENT_BLOCK_LAYOUT1 => Labels::getLabel('LBL_Content_Block', $langId),
            ], */ 
            self::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON => [
                self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT1 => Labels::getLabel('LBL_Content_Block_with_icon_layout', $langId),
                self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT2 => Labels::getLabel('LBL_Content_Block_with_icon_layout', $langId),
                self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT3 => Labels::getLabel('LBL_Content_Block_layout', $langId),
            ]
        ];
    }

    /**
     * getBannersCount
     *
     * @return array
     */
    public static function getBannersCount(): array
    {
        return [
            self::TYPE_BANNER_LAYOUT1 => 1,
            self::TYPE_BANNER_LAYOUT2 => 2,
            self::TYPE_BANNER_LAYOUT3 => 1,
            self::TYPE_BANNER_LAYOUT4 => 2,
            self::TYPE_BANNER_LAYOUT5 => 1
        ];
    }

    /**
     * getLayoutImagesArr
     *
     * @return array
     */
    public static function getLayoutImagesArr(): array
    {
		$activeTheme = strtolower(applicationConstants::getActiveTheme());
        return [
            self::TYPE_PRODUCT_LAYOUT1 => $activeTheme.'/Product-Layout-1-.png',
            self::TYPE_PRODUCT_LAYOUT2 => $activeTheme.'/Product-Layout-2.png',
            self::TYPE_PRODUCT_LAYOUT3 => $activeTheme.'/Product-Layout-3.png',
            self::TYPE_PRODUCT_LAYOUT4 => $activeTheme.'/Product-Layout-4.png',
            self::TYPE_PRODUCT_LAYOUT5 => $activeTheme.'/Product-Layout-5.png',
            self::TYPE_CATEGORY_LAYOUT1 => $activeTheme.'/Category-Layout-1.png',
            self::TYPE_CATEGORY_LAYOUT2 => $activeTheme.'/Category-Layout-2.png',
            self::TYPE_CATEGORY_LAYOUT3 => $activeTheme.'/Category-Layout-3.png',
            self::TYPE_CATEGORY_LAYOUT4 => $activeTheme.'/Category-Layout-4.png',
            self::TYPE_SHOP_LAYOUT1 => $activeTheme.'/Shop-Layout-1.png',
            self::TYPE_SHOP_LAYOUT2 => $activeTheme.'/Shop-Layout-2.png',
            self::TYPE_BRAND_LAYOUT1 => $activeTheme.'/Brand-Layout-1.png',
            self::TYPE_BRAND_LAYOUT2 => $activeTheme.'/Brand-Layout-2.png',
            self::TYPE_BLOG_LAYOUT1 => $activeTheme.'/Blog-Layout-1.png',
            self::TYPE_BLOG_LAYOUT2 => $activeTheme.'/Blog-Layout-2.png',
            self::TYPE_SPONSORED_PRODUCT_LAYOUT => $activeTheme.'/Sponsored-Products.png',
            self::TYPE_SPONSORED_SHOP_LAYOUT => $activeTheme.'/Sponsored-Shops.png',
            self::TYPE_BANNER_LAYOUT1 => $activeTheme.'/Banner-Layout-1.png',
            self::TYPE_BANNER_LAYOUT2 => $activeTheme.'/Banner-Layout-2.png',
            self::TYPE_BANNER_LAYOUT3 => $activeTheme.'/Banner-Layout-3.png',
            self::TYPE_BANNER_LAYOUT4 => $activeTheme.'/Banner-Layout-4.png',
            self::TYPE_BANNER_LAYOUT5 => $activeTheme.'/Banner-Layout-5.png',
            self::TYPE_FAQ_LAYOUT1 => $activeTheme.'/Faq-Layout-1.png',
            self::TYPE_FAQ_LAYOUT2 => $activeTheme.'/Faq-Layout-2.png',
            self::TYPE_TESTIMONIAL_LAYOUT1 => $activeTheme.'/Testimonial-layout-1.png',
            self::TYPE_TESTIMONIAL_LAYOUT2 => $activeTheme.'/Testimonial-layout-2.png',
            self::TYPE_CONTENT_BLOCK_LAYOUT1 => $activeTheme.'/Content-Block-layout-1.png',
            self::TYPE_PENDING_REVIEWS1 => $activeTheme.'/Pending-Reviews-1.png',
            self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT1 => $activeTheme.'/Content-Block-with-icon-layout-1.png',
            self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT2 => $activeTheme.'/Content-Block-with-icon-layout-2.png',
            self::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT3 => $activeTheme.'/Content-Block-with-icon-layout-3.png',
        ];
    }

    /**
     * getCriteria
     *
     * @return array
     */
    public static function getCriteria()
    {
        return [
            static::COLLECTION_CRITERIA_PRICE_LOW_TO_HIGH => "Price Low to High",
            static::COLLECTION_CRITERIA_PRICE_HIGH_TO_LOW => "Price High to Low",
        ];
    }

    /**
     * addUpdateCollectionRecord
     *
     * @param  int $collectionId
     * @param  int $recordId
     * @return bool
     */
    public function addUpdateCollectionRecord(int $collectionId, int $recordId, int $displayOrder = 0): bool
    {
        if (!$collectionId || !$recordId) {
            $this->error = Labels::getLabel('MSG_Invalid_Request', $this->commonLangId);
            return false;
        }

        $record = new TableRecord(static::DB_TBL_COLLECTION_TO_RECORDS);
        $dataToSave = array();
        $dataToSave[static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id'] = $collectionId;
        $dataToSave[static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id'] = $recordId;
        $dataToSave[static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'display_order'] = $displayOrder;
        $record->assignValues($dataToSave);
        if (!$record->addNew(array(), $dataToSave)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    /**
     * updateCollectionRecordOrder
     *
     * @param  int $collectionId
     * @param  array $order
     * @return bool
     */
    public function updateCollectionRecordOrder(int $collectionId, array $order): bool
    {
        if (!$collectionId) {
            return false;
        }
        if (is_array($order) && sizeof($order) > 0) {
            foreach ($order as $i => $id) {
                if (FatUtility::int($id) < 1) {
                    continue;
                }
                FatApp::getDb()->updateFromArray(
                        static::DB_TBL_COLLECTION_TO_RECORDS, array(
                    static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'display_order' => $i
                        ), array(
                    'smt' => static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id = ? AND ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id = ?',
                    'vals' => array($collectionId, $id)
                        )
                );
            }
            return true;
        }
        return false;
    }

    /**
     * addUpdateData
     *
     * @param  array $data
     * @return bool
     */
    public function addUpdateData(array $data): bool
    {
        unset($data['collection_id']);
        $assignValues = $data;
        $assignValues['collection_deleted'] = 0;
        if ($this->mainTableRecordId > 0) {
            $assignValues['collection_id'] = $this->mainTableRecordId;
        }

        $this->assignValues($assignValues);
        if (!$this->save()) {
            $this->error = $this->getError();
            return false;
        }

        return true;
    }

    /**
     * removeCollectionRecord
     *
     * @param  int $collectionId
     * @param  int $recordId
     * @return bool
     */
    public function removeCollectionRecord(int $collectionId, int $recordId): bool
    {
        $db = FatApp::getDb();
        if (!$collectionId || !$recordId) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_COLLECTION_TO_RECORDS, array('smt' => static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id = ? AND ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id = ?', 'vals' => array($collectionId, $recordId)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    /**
     * canRecordMarkDelete
     *
     * @param  int $collection_id
     * @return bool
     */
    public function canRecordMarkDelete(int $collection_id): bool
    {
        $srch = static::getSearchObject(false);
        $srch->addCondition('collection_deleted', '=', applicationConstants::NO);
        $srch->addCondition('collection_id', '=', $collection_id);
        $srch->addFld('collection_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['collection_id'] == $collection_id) {
            return true;
        }
        return false;
    }

    /**
     * getSellProds
     *
     * @param  int $collection_id
     * @param  int $lang_id
     * @return array
     */
    public static function getSellProds(int $collection_id, int $lang_id): array
    {
        if (!$collection_id || !$lang_id) {
            trigger_error(Labels::getLabel('MSG_Arguments_not_specified.', $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collection_id);
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', SellerProduct::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id', 'sp');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', SellerProduct::DB_TBL_PREFIX . 'product_id = ' . Product::DB_TBL_PREFIX . 'id');

        $srch->joinTable(SellerProduct::DB_TBL . '_lang', 'LEFT JOIN', 'lang.selprodlang_selprod_id = ' . SellerProduct::DB_TBL_PREFIX . 'id AND selprodlang_lang_id = ' . $lang_id, 'lang');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');
        $srch->addMultipleFields(array('ctr_display_order', 'selprod_id as record_id', 'COALESCE(selprod_title, product_identifier) as record_title', 'credential_username', 'selprod_deleted as is_deleted'));
        $srch->addOrder('ctr_display_order', 'ASC');
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = array();
        while ($row = $db->fetch($rs)) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * getBanners
     *
     * @param  int $collection_id
     * @param  int $lang_id
     * @return array
     */
    public static function getBanners(int $collection_id, int $lang_id): array
    {
        if (!$collection_id || !$lang_id) {
            trigger_error(Labels::getLabel('MSG_Arguments_not_specified.', $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new BannerSearch($lang_id, false);
        $srch->joinCollectionToRecords();
        $srch->joinLocations();
        $srch->joinPromotions($lang_id, true);
        $srch->addPromotionTypeCondition();
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('COALESCE(promotion_name,promotion_identifier) as promotion_name', 'banner_id', 'banner_type', 'banner_url', 'banner_target', 'banner_active', 'banner_blocation_id', 'banner_title', 'banner_updated_on'));
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collection_id);

        $srch->addOrder('banner_active', 'DESC');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    /**
     * getCategories
     *
     * @param  int $collection_id
     * @param  int $lang_id
     * @return array
     */
    public static function getCategories(int $collection_id, int $lang_id, $keyOrderBy = false): array
    {
        if (!$collection_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collection_id);

        $srch->joinTable(ProductCategory::DB_TBL, 'INNER JOIN', ProductCategory::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id');

        $srch->joinTable(ProductCategory::DB_TBL_LANG, 'LEFT JOIN', 'lang.prodcatlang_prodcat_id = ' . ProductCategory::DB_TBL_PREFIX . 'id AND prodcatlang_lang_id = ' . $lang_id, 'lang');
        $srch->addMultipleFields(array('ctr_display_order', 'prodcat_id as record_id', 'IFNULL(prodcat_name, prodcat_identifier) as record_title'));
        $srch->addOrder('ctr_display_order', 'ASC');
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        if($keyOrderBy) {
            $data = $db->fetchAll($rs, 'ctr_display_order');
        } else {
            $data = $db->fetchAll($rs);
        }
        
        return $data;
    }

    /**
     * getShops
     *
     * @param  int $collection_id
     * @param  int $lang_id
     * @return array
     */
    public static function getShops(int $collection_id, int $lang_id): array
    {
        if (!$collection_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collection_id);

        $srch->joinTable(Shop::DB_TBL, 'INNER JOIN', Shop::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id');

        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT JOIN', 'lang.shoplang_shop_id = ' . Shop::DB_TBL_PREFIX . 'id AND shoplang_lang_id = ' . $lang_id, 'lang');
        $srch->addMultipleFields(array('ctr_display_order', 'shop_id as record_id', 'IFNULL(shop_name, shop_identifier) as record_title'));
        $srch->addOrder('ctr_display_order', 'ASC');
        $rs = $srch->getResultSet();

        $db = FatApp::getDb();
        $data = $db->fetchAll($rs);
        return $data;
    }

    /**
     * getBrands
     *
     * @param  int $collectionId
     * @param  int $langId
     * @return array
     */
    public static function getBrands(int $collectionId, int $langId): array
    {
        if (!$collectionId || !$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collectionId);
        $srch->joinTable(Brand::DB_TBL, 'INNER JOIN', Brand::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id');
        $srch->joinTable(Brand::DB_TBL_LANG, 'LEFT JOIN', 'lang.brandlang_brand_id = ' . Brand::DB_TBL_PREFIX . 'id AND brandlang_lang_id = ' . $langId, 'lang');
        $srch->addMultipleFields(array('ctr_display_order', 'brand_id as record_id', 'IFNULL(brand_name, brand_identifier) as record_title'));
        $srch->addOrder('ctr_display_order', 'ASC');
        $rs = $srch->getResultSet();

        $db = FatApp::getDb();
        return $db->fetchAll($rs);
    }

    /**
     * getBlogs
     *
     * @param  int $collectionId
     * @param  int $langId
     * @return array
     */
    public static function getBlogs(int $collectionId, int $langId): array
    {
        if (!$collectionId || !$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collectionId);

        $srch->joinTable(BlogPost::DB_TBL, 'INNER JOIN', BlogPost::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id');

        $srch->joinTable(BlogPost::DB_TBL_LANG, 'LEFT JOIN', 'lang.postlang_post_id = ' . BlogPost::DB_TBL_PREFIX . 'id AND postlang_lang_id = ' . $langId, 'lang');
        $srch->addMultipleFields(array('post_id as record_id', 'IFNULL(post_title, post_identifier) as record_title'));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetchAll($rs);
    }

    /**
     * getFaqs
     *
     * @param  int $collectionId
     * @param  int $langId
     * @return array
     */
    public static function getFaqs(int $collectionId, int $langId): array
    {
        if (!$collectionId || !$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collectionId);

        $srch->joinTable(Faq::DB_TBL, 'INNER JOIN', Faq::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id');
        $srch->joinTable(Faq::DB_TBL_LANG, 'LEFT JOIN', 'lang.faqlang_faq_id = ' . Faq::DB_TBL_PREFIX . 'id AND faqlang_lang_id = ' . $langId, 'lang');
        $srch->joinTable(
                FaqCategory::DB_TBL, 'INNER JOIN', 'faq_faqcat_id = faqcat_id', 'fc'
        );
        $srch->addCondition(FaqCategory::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        $srch->joinTable(FaqCategory::DB_TBL_LANG, 'LEFT OUTER JOIN', 'fc_l.' . FaqCategory::DB_TBL_LANG_PREFIX . 'faqcat_id = fc.' . FaqCategory::tblFld('id') . ' and fc_l.' . FaqCategory::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'fc_l');
        $srch->addMultipleFields(array('ctr_display_order', 'faq_id as record_id', 'CONCAT(IFNULL(faq_title, faq_identifier), " | ", IFNULL (faqcat_name, faqcat_identifier)) as record_title'));
        $srch->addOrder('ctr_display_order', 'ASC');
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetchAll($rs);
    }

    /**
     * getTestimonials
     *
     * @param  int $collectionId
     * @param  int $langId
     * @return array
     */
    public static function getTestimonials(int $collectionId, int $langId): array
    {
        if (!$collectionId || !$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collectionId);

        $srch->joinTable(Testimonial::DB_TBL, 'INNER JOIN', Testimonial::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id');

        $srch->joinTable(Testimonial::DB_TBL_LANG, 'LEFT JOIN', 'lang.testimoniallang_testimonial_id = ' . Testimonial::DB_TBL_PREFIX . 'id AND testimoniallang_lang_id = ' . $langId, 'lang');
        $srch->addMultipleFields(array('ctr_display_order', 'testimonial_id as record_id', 'IFNULL(testimonial_title, testimonial_identifier) as record_title'));
        $srch->addOrder('ctr_display_order', 'ASC');
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetchAll($rs);
    }

    /**
     * saveLangData
     *
     * @param  int $langId
     * @param  string $collectionName
     * @return bool
     */
    public function saveLangData(int $langId, string $collectionName, string $collectionDescription = '', string $collectionText = ''): bool
    {
        $langId = FatUtility::int($langId);
        if ($this->mainTableRecordId < 1 || $langId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $data = array(
            'collectionlang_collection_id' => $this->mainTableRecordId,
            'collectionlang_lang_id' => $langId,
            'collection_name' => $collectionName,
            'collection_description' => $collectionDescription,
            'collection_text' => $collectionText,
        );

        if (!$this->updateLangData($langId, $data)) {
            $this->error = $this->getError();
            return false;
        }
        return true;
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
     * getRecords
     *
     * @param  int $collectionId
     * @return array
     */
    public static function getRecords(int $collectionId): array
    {
        if (1 > $collectionId) {
            return [];
        }

        $srch = new SearchBase(self::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->addCondition(Collections::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collectionId);
        $srch->addMultipleFields(array('ctr_record_id', 'ctr_collection_id'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $res = $srch->getResultSet();
        return (array) FatApp::getDb()->fetchAllAssoc($res);
    }

    public static function getBannerPositionType(int $langId): array
    {
        return [
            static::BANNER_POSITION_LEFT => Labels::getLabel('LBL_Left_Banner', $langId),
            static::BANNER_POSITION_RIGHT => Labels::getLabel('ERR_Right_Banner', $langId),
        ];
    }

    public static function getContentBlocks(int $collectionId, int $langId): array
    {
        if (!$collectionId || !$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collectionId);
        $srch->joinTable(ContentBlockWithIcon::DB_TBL, 'INNER JOIN', ContentBlockWithIcon::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id');
        $srch->joinTable(ContentBlockWithIcon::DB_TBL_LANG, 'LEFT JOIN', 'lang.cbslang_cbs_id = ' . ContentBlockWithIcon::DB_TBL_PREFIX . 'id AND cbslang_lang_id = ' . $langId, 'lang');
        $srch->addMultipleFields(array('ctr_display_order', 'cbs_id as record_id', 'IFNULL(cbs_name, cbs_identifier) as record_title'));
        $srch->addOrder('ctr_display_order', 'ASC');
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetchAll($rs);
    }

    public static function gridViewImagesSizeArr()
    {
        return [
			1 => [
                'width' => static:: GRID_VIEW_ORDER_1_MIN_WIDTH,
                'height' => static:: GRID_VIEW_ORDER_1_MIN_HEIGHT,
            ],
            2 => [
                'width' => static:: GRID_VIEW_ORDER_2_MIN_WIDTH,
                'height' => static:: GRID_VIEW_ORDER_2_MIN_HEIGHT,
            ],
            3 => [
                'width' => static:: GRID_VIEW_ORDER_3_MIN_WIDTH,
                'height' => static:: GRID_VIEW_ORDER_3_MIN_HEIGHT,
            ],
            4 => [
                'width' => static:: GRID_VIEW_ORDER_4_MIN_WIDTH,
                'height' => static:: GRID_VIEW_ORDER_4_MIN_HEIGHT,
            ],
            5 => [
                'width' => static:: GRID_VIEW_ORDER_5_MIN_WIDTH,
                'height' => static:: GRID_VIEW_ORDER_5_MIN_HEIGHT,
        ]];
    }

    public static function getContentBlocksRecords(int $collectionId, int $displayOrder): array
    {
        if (!$collectionId ) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_TBL_COLLECTION_TO_RECORDS);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addCondition(static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'collection_id', '=', $collectionId);
        $srch->joinTable(ContentBlockWithIcon::DB_TBL, 'INNER JOIN', ContentBlockWithIcon::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_COLLECTION_TO_RECORDS_PREFIX . 'record_id');
        $srch->joinTable(ContentBlockWithIcon::DB_TBL_LANG, 'LEFT JOIN', 'lang.cbslang_cbs_id = ' . ContentBlockWithIcon::DB_TBL_PREFIX . 'id ', 'lang');
        $srch->addMultipleFields(array('ctr_display_order', 'cbs_id', 'cbs_name', 'cbs_identifier','cbslang_description','cbslang_lang_id','cbs_display_order'));
        $srch->addCondition('cbs_display_order','=', $displayOrder);
        $srch->addOrder('ctr_display_order', 'ASC');
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetchAll($rs);
    }

}
