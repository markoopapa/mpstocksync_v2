<?php
class AdminMpStockSyncDashboardController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }
    
    public function initContent()
    {
        parent::initContent();
        
        // Betöltjük a modult
        $module = Module::getInstanceByName('mpstocksync');
        
        // Statisztikák lekérése - biztosan tömb legyen
        $stats = $module->getSyncStatistics();
        
        // Biztosítjuk, hogy minden kulcs létezik
        $stats = $this->ensureStatsStructure($stats);
        
        $this->context->smarty->assign([
            'stats' => $stats,
            'module_dir' => $module->_path
        ]);
        
        $this->setTemplate('dashboard/dashboard.tpl');
    }
    
    /**
     * Biztosítja, hogy a stats tömb megfelelő struktúrával rendelkezik
     */
    private function ensureStatsStructure($stats)
    {
        if (!is_array($stats)) {
            $stats = [];
        }
        
        // Alap struktúra
        $default_stats = [
            'emag' => [
                'total' => 0,
                'success' => 0,
                'failed' => 0
            ],
            'trendyol' => [
                'total' => 0,
                'success' => 0,
                'failed' => 0
            ],
            'suppliers' => [],
            'recent_syncs' => [],
            'last_sync_log' => 'No sync activities yet'
        ];
        
        // Összefésüljük a default értékekkel
        $stats = array_merge($default_stats, $stats);
        
        // Ellenőrizzük a mélyebb struktúrákat is
        if (!isset($stats['emag']['total'])) $stats['emag']['total'] = 0;
        if (!isset($stats['emag']['success'])) $stats['emag']['success'] = 0;
        if (!isset($stats['emag']['failed'])) $stats['emag']['failed'] = 0;
        
        if (!isset($stats['trendyol']['total'])) $stats['trendyol']['total'] = 0;
        if (!isset($stats['trendyol']['success'])) $stats['trendyol']['success'] = 0;
        if (!isset($stats['trendyol']['failed'])) $stats['trendyol']['failed'] = 0;
        
        if (!isset($stats['suppliers']) || !is_array($stats['suppliers'])) {
            $stats['suppliers'] = [];
        }
        
        if (!isset($stats['recent_syncs']) || !is_array($stats['recent_syncs'])) {
            $stats['recent_syncs'] = [];
        }
        
        if (!isset($stats['last_sync_log'])) {
            $stats['last_sync_log'] = 'No sync activities yet';
        }
        
        return $stats;
    }
}
