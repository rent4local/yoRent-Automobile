<?php

class BadProductsReportController extends AdminBaseController
{
    private $canView;
    private $canEdit;
    
    public const REPORT_TYPE_TODAY = 1;
    public const REPORT_TYPE_WEEKLY = 2;
    public const REPORT_TYPE_MONTHLY = 3;
    public const REPORT_TYPE_YEARLY = 4;
    
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewPerformanceReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditPerformanceReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }
    
    private function getReportTypeArr()
    {
        return array( self::REPORT_TYPE_TODAY => 'Today',  self::REPORT_TYPE_WEEKLY => 'Weekly', self::REPORT_TYPE_MONTHLY => 'Monthly', self::REPORT_TYPE_YEARLY => 'Yearly');
    }
    
    public function index()
    {
        if(!FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) {
            FatUtility::exitWithErrorCode(404);
        }
        
        $this->objPrivilege->canViewPerformanceReport();
        $frmSearch = $this->getSearchForm(applicationConstants::PRODUCT_FOR_SALE);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }
    
    public function rental()
    {
        $this->objPrivilege->canViewPerformanceReport();
        $frmSearch = $this->getSearchForm(applicationConstants::PRODUCT_FOR_RENT);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render(true, true, 'bad-products-report/index.php');
    }
    
    /* public function export()
    {
        $this->search('export');
    } */
    
    private function getSearchForm($productFor)
    {
        $frm = new Form('frmBadProductsReportSearch');
        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'report_type', $this->getReportTypeArr(), '', array(), 'OverAll');
        $frm->addSelectBox(Labels::getLabel('LBL_Records_Per_Page', $this->adminLangId), 'pagesize', array( 10 => '10', 20 => '20', 30 => '30', 50 => '50'), '', array(), '');
        
        $financialYearDates = CommonHelper::getCurrentFinanceYearStartEndDates();
        $financialYearStart = $financialYearDates['start_date'];
        $financialYearEnd = $financialYearDates['end_date'];
        
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', $financialYearStart, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
   
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', $financialYearEnd, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
        
        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'top_perfomed', 0);
        $frm->addHiddenField('', 'product_for', $productFor);
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
