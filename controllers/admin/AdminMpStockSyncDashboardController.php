<?php
class AdminMpStockSyncDashboardController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        
        $this->meta_title = 'Stock Sync Dashboard';
    }
    
    public function initContent()
    {
        parent::initContent();
        
        $module = Module::getInstanceByName('mpstocksync');
        
        $this->context->smarty->assign([
            'dashboard_url' => $this->context->link->getAdminLink('AdminMpStockSyncDashboard'),
            'module' => $module
        ]);
        
        $this->setTemplate('dashboard.tpl');
    }
}
