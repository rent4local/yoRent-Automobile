<?php

class SitemapController extends AdminBaseController
{

    private $siteMapLanguages = [];
    private $defaultLangId = 0;
    private $siteMapIndexArr = [];
    private $sitemapListInc = 1;
    private $recordCountInc = 0;
    private $limit = 2000;
    private $sitemapDir = 'sitemap';

    public function __construct($action)
    {
        parent::__construct($action);
        $this->defaultLangId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
        $recordId = 0;
        if (!FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0)) {
            $recordId = $this->defaultLangId;
        }
        $this->siteMapLanguages = Language::getAllNames(false, $recordId);
    }

    public function generate()
    {
        set_time_limit(0);

        $structure = $this->getStructure();
        foreach ($structure as $val) {
            $this->sitemapListInc = 1;
            $this->recordCountInc = 0;
            $this->writeSitemapIndex($val);
        }

        $this->writeStructureIndex();


        if (1 < count($this->siteMapLanguages)) {
            $this->writeSitemapLangSpecific();
        }

        $this->writePrimarySitemapIndex();

        Message::addMessage(Labels::getLabel('MSG_SITEMAP_HAS_BEEN_UPDATED_SUCCESSFULLY', $this->siteLangId));
        CommonHelper::redirectUserReferer();
    }

    private function writeSitemapIndex($type)
    {
        switch ($type) {
            case 'products':
                $this->writeProductSitemap($type);
                break;
            case 'categories':
                $this->writeCategorySitemap($type);
                break;
            case 'brands':
                $this->writeBrandSitemap($type);
                break;
            case 'shops':
                $this->writeShopSitemap($type);
                break;
            case 'cms':
                $this->writeCmsSitemap($type);
            case 'blogs':
                $this->writeBlogsSitemap($type);
            case 'blogCategories':
                $this->writeBlogsCategoriesSitemap($type);    
                break;
        }
    }

    private function getProductSrchObj($langId = 0)
    {
        $prodSrchObj = new ProductSearch($langId);
        $prodSrchObj->setDefinedCriteria(1);
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->joinSellerSubscription();
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->doNotLimitRecords();
        return $prodSrchObj;
    }

    private function writeProductSitemap($type)
    {
        $prodSrch = $this->getProductSrchObj();

        foreach ($this->siteMapLanguages as $language) {
            $this->startSitemapXml();
            
            $url = UrlHelper::getUrlScheme(). '/' . strtolower($language['language_code']);
            $file = $this->sitemapDir . '/' . strtolower($language['language_code']).'/products';
            
            $prodSrch->addMultipleFields(array('selprod_id'));
            $prodSrch->addGroupBy('selprod_id');
            $prodSrch->doNotCalculateRecords();
            $prodSrch->doNotLimitRecords();
            $rs = $prodSrch->getResultSet();
            while ($row = FatApp::getDb()->fetch($rs)) {
                $this->writeSitemapUrl(UrlHelper::generateFullUrl('products', 'view', array($row['selprod_id']), CONF_WEBROOT_FRONT_URL, null, false, false, true, $language['language_id']), $file);
            }

            $this->endSitemapXml($file);
            $this->resetSiteMapListInc($type);
        }
    }

    private function writeCategorySitemap($type)
    {
        foreach ($this->siteMapLanguages as $language) {
            $this->startSitemapXml();
            
            $url = UrlHelper::getUrlScheme(). '/' . strtolower($language['language_code']);
            $file = $this->sitemapDir . '/' . strtolower($language['language_code']). '/categories';
            
            $categoriesArr = productCategory::getProdCatParentChildWiseArr($language['language_id'], 0, true, false, true);
            foreach ($categoriesArr as $key => $val) {
                $this->writeSitemapUrl(UrlHelper::generateFullUrl('category', 'view', array($val['prodcat_id']), CONF_WEBROOT_FRONT_URL, null, false, false, true, $language['language_id']), $file);
            }

            $this->endSitemapXml($file);
            $this->resetSiteMapListInc($type);
        }
    }

    private function writeBrandSitemap($type)
    {
        foreach ($this->siteMapLanguages as $language) {
            $prodSrchObj = $this->getProductSrchObj($language['language_id']);
            $this->startSitemapXml();
            
            $url = UrlHelper::getUrlScheme() .'/' . strtolower($language['language_code']);
            $file = $this->sitemapDir . '/' . strtolower($language['language_code']). '/brands';
            
            $brandSrch = clone $prodSrchObj;
            $brandSrch->addMultipleFields(array('brand_id'));
            $brandSrch->addGroupBy('brand_id');
            $brandSrch->addOrder('brand_name');
            $brandSrch->doNotCalculateRecords();
            $brandSrch->doNotLimitRecords();
            $brandRs = $brandSrch->getResultSet();
            while ($row = FatApp::getDb()->fetch($brandRs)) {
                $this->writeSitemapUrl(UrlHelper::generateFullUrl('brands', 'view', array($row['brand_id']), CONF_WEBROOT_FRONT_URL, null, false, false, true, $language['language_id']), $file);
            }

            $this->endSitemapXml($file);
            $this->resetSiteMapListInc($type);
        }
    }

    private function writeShopSitemap($type)
    {
        foreach ($this->siteMapLanguages as $language) {
            $this->startSitemapXml();
            
            $url = UrlHelper::getUrlScheme(). '/' . strtolower($language['language_code']);
            $file = $this->sitemapDir . '/' . strtolower($language['language_code']). '/shops';
            
            $shopSrch = new ShopSearch();
            $shopSrch->setDefinedCriteria();
            $shopSrch->joinShopCountry();
            $shopSrch->joinShopState();
            $shopSrch->joinSellerSubscription();
            $shopSrch->doNotCalculateRecords();
            $shopSrch->doNotLimitRecords();
            $shopSrch->addMultipleFields(array('shop_id'));
            $rs = $shopSrch->getResultSet();
            while ($row = FatApp::getDb()->fetch($rs)) {
                $this->writeSitemapUrl(UrlHelper::generateFullUrl('shops', 'view', array($row['shop_id']), CONF_WEBROOT_FRONT_URL, null, false, false, true, $language['language_id']), $file);
            }

            $this->endSitemapXml($file);
            $this->resetSiteMapListInc($type);
        }
    }

    private function writeCmsSitemap($type)
    {
        foreach ($this->siteMapLanguages as $language) {
            $this->startSitemapXml();
            $url = UrlHelper::getUrlScheme(). '/' . strtolower($language['language_code']);
            $file = $this->sitemapDir . '/' . strtolower($language['language_code']). '/cms';
            
            $cmsSrch = new NavigationLinkSearch();
            $cmsSrch->joinNavigation();
            $cmsSrch->joinProductCategory();
            $cmsSrch->joinContentPages();
            $cmsSrch->doNotCalculateRecords();
            $cmsSrch->doNotLimitRecords();
            $cmsSrch->addOrder('nav_id');
            $cmsSrch->addOrder('nlink_display_order');

            $cmsSrch->addCondition('nlink_deleted', '=', '0');
            $cmsSrch->addCondition('nav_active', '=', applicationConstants::ACTIVE);
            $cmsSrch->addMultipleFields(array('nlink_cpage_id, nlink_type'));
            $rs = $cmsSrch->getResultSet();
            while ($row = FatApp::getDb()->fetch($rs)) {
                if ($row['nlink_type'] == NavigationLinks::NAVLINK_TYPE_CMS && $row['nlink_cpage_id']) {
                    $this->writeSitemapUrl(UrlHelper::generateFullUrl('Cms', 'view', array($row['nlink_cpage_id']), CONF_WEBROOT_FRONT_URL, null, false, false, true, $language['language_id']), $file);
                }
            }
            $this->endSitemapXml($file);
            $this->resetSiteMapListInc($type);
        }
    }
    
    private function writeBlogsSitemap($type)
    {
        foreach ($this->siteMapLanguages as $language) {
            $this->startSitemapXml();
            $url = UrlHelper::getUrlScheme(). '/' . strtolower($language['language_code']);
            $file = $this->sitemapDir . '/' . strtolower($language['language_code']). '/blogs';
            
            $srch = BlogPost::getSearchObject($this->adminLangId, true, false, true);
            $srch->addMultipleFields(array('post_id'));
            $srch->addCondition('postlang_post_id', 'is not', 'mysql_func_null', 'and', true);
            $srch->addCondition('post_published', '=', applicationConstants::YES);
            $srch->addGroupby('post_id');
            $srch->addOrder('post_id', 'ASC');
            $srch->doNotCalculateRecords();
			$srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            while ($row = FatApp::getDb()->fetch($rs)) {
                $this->writeSitemapUrl(UrlHelper::generateFullUrl('blog', 'postDetail', array($row['post_id']), CONF_WEBROOT_FRONT_URL, null, false, false, true, $language['language_id']), $file);
            }
            $this->endSitemapXml($file);
            $this->resetSiteMapListInc($type);
        }
    }
    
    private function writeBlogsCategoriesSitemap($type)
    {
        foreach ($this->siteMapLanguages as $language) {
            $this->startSitemapXml();
            
            $url = UrlHelper::getUrlScheme(). '/' . strtolower($language['language_code']);
            $file = $this->sitemapDir . '/' . strtolower($language['language_code']). '/blogCategories';
            
            $bpcatObj = new BlogPostCategory();
            $postCategories = $bpcatObj->getCategoriesForSelectBox($this->adminLangId);
            foreach ($postCategories as $key => $val) {
                $this->writeSitemapUrl(UrlHelper::generateFullUrl('blog', 'category', array($val['bpcategory_id']), CONF_WEBROOT_FRONT_URL, null, false, false, true, $language['language_id']), $file);
            }

            $this->endSitemapXml($file);
            $this->resetSiteMapListInc($type);
        }
    }

    private function startSitemapXml()
    {
        ob_start();
        echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    }

    private function writeSitemapUrl($url, $file, $freq = 'weekly')
    {
        $this->recordCountInc++;
        if ($this->recordCountInc > $this->limit) {
            $this->endSitemapXml($file, true);
            $this->startSitemapXml();
            $this->recordCountInc = 0;
        }
        echo "
			<url>
				<loc>" . $url . "</loc>
                <lastmod>" . date('Y-m-d') . "</lastmod>
                <changefreq>" . $freq . "</changefreq>
                <priority>0.8</priority>
			</url>";
        echo "\n";
    }

    private function endSitemapXml($file, $changeList = false)
    {
        $file = $file . $this->sitemapListInc;
        if ($changeList) {
            $this->sitemapListInc++;
        }

        echo '</urlset>' . "\n";
        $contents = ob_get_clean();
        $rs = '';
        CommonHelper::writeFile($file . '.xml', $contents, $rs);
    }

    private function writePrimarySitemapIndex()
    {
        ob_start();
        echo "<?xml version='1.0' encoding='UTF-8'?>
		<sitemapindex xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd' xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";

        if (1 < count($this->siteMapLanguages)) {
            foreach ($this->siteMapLanguages as $language) {
                $url = UrlHelper::getUrlScheme(). '/' . strtolower($language['language_code']);
                echo "<sitemap><loc>" . $url . "/sitemap.xml</loc></sitemap>\n";
            }
        } else {
            $structure = $this->getStructure();
            foreach ($structure as $val) {
                /* echo "<sitemap><loc>" . UrlHelper::getUrlScheme() . '/' . $this->sitemapDir . '/' . strtolower($val) . ".xml</loc></sitemap>\n"; */
                echo "<sitemap><loc>" . UrlHelper::getUrlScheme() . '/' . strtolower($val) . ".xml</loc></sitemap>\n";
            }
        }

        echo "</sitemapindex>";
        $contents = ob_get_clean();
        $rs = '';
        CommonHelper::writeFile('sitemap.xml', $contents, $rs);
    }

    private function writeSitemapLangSpecific()
    {
        $structure = $this->getStructure();
        foreach ($this->siteMapLanguages as $language) {
            ob_start();
            echo "<?xml version='1.0' encoding='UTF-8'?>
    <sitemapindex xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd' xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
            foreach ($structure as $val) {
                $url = UrlHelper::getUrlScheme(). '/' . strtolower($language['language_code']). "/" . strtolower($val) . '.xml';
                echo "<sitemap><loc>" . $url . "</loc></sitemap>\n";
            }

            echo "</sitemapindex>";
            $contents = ob_get_clean();
            $rs = '';

            $file = $this->sitemapDir . '/' . strtolower($language['language_code']);
            CommonHelper::writeFile($file . '/sitemap.xml', $contents, $rs);
        }
    }

    private function writeStructureIndex()
    {
        foreach ($this->siteMapLanguages as $language) {
            foreach ($this->siteMapIndexArr as $type => $listingCount) {
                /* if (1 == $listingCount) {
                    $url = CONF_UPLOADS_PATH . '/'. strtolower($language['language_code']). '/' . strtolower($type);
                    rename($url . '1.xml', $url . '.xml');
                    continue;
                } */

                ob_start();
                echo "<?xml version='1.0' encoding='UTF-8'?>
		<sitemapindex xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd' xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";

                for ($i = 1; $i <= $listingCount; $i++) {
                    $url = UrlHelper::getUrlScheme() .'/' . strtolower($language['language_code']);
                    echo "<sitemap><loc>" . $url . '/' . strtolower($type) . $i . ".xml</loc></sitemap>\n";
                }

                echo "</sitemapindex>";
                $contents = ob_get_clean();
                $rs = '';

                $file = $this->sitemapDir . '/' . strtolower($language['language_code']);
                
                CommonHelper::writeFile($file . '/' . strtolower($type) . '.xml', $contents, $rs);
            }
        }
    }

    private function getStructure()
    {
        return [
            'products',
            'categories',
            'brands',
            'shops',
            'cms',
            'blogs',
            'blogCategories'
        ];
    }

    private function resetSiteMapListInc($type)
    {
        $this->siteMapIndexArr[$type] = $this->sitemapListInc;
        $this->sitemapListInc = 1;
    }

}
