<?php
// NINCS namespace!
// NINCS use statement!

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
        
        // Get module instance
        $module = Module::getInstanceByName('mpstocksync');
        
        // Get sync statistics
        $stats = [];
        if ($module) {
            $stats = $module->getSyncStatistics();
        }
        
        $this->context->smarty->assign([
            'dashboard_url' => $this->context->link->getAdminLink('AdminMpStockSyncDashboard'),
            'stats' => $stats,
            'module' => $module,
            'emag_configured' => Configuration::get('MP_EMAG_CLIENT_ID') && Configuration::get('MP_EMAG_CLIENT_SECRET'),
            'trendyol_configured' => Configuration::get('MP_TRENDYOL_API_KEY') && Configuration::get('MP_TRENDYOL_API_SECRET')
        ]);
        
        $this->setTemplate('dashboard.tpl');
    }
    
    public function initToolbar()
    {
        parent::initToolbar();
        
        $this->page_header_toolbar_btn['sync_emag'] = [
            'href' => self::$currentIndex . '&sync_emag&token=' . $this->token,
            'desc' => 'Sync eMAG',
            'icon' => 'process-icon-refresh'
        ];
        
        $this->page_header_toolbar_btn['sync_trendyol'] = [
            'href' => self::$currentIndex . '&sync_trendyol&token=' . $this->token,
            'desc' => 'Sync Trendyol',
            'icon' => 'process-icon-refresh'
        ];
    }
}
