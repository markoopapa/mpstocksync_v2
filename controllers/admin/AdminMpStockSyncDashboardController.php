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
        
        if (!$module) {
            $this->errors[] = $this->l('Module not found');
            $this->content = '<div class="alert alert-danger">Module not found</div>';
            $this->context->smarty->assign('content', $this->content);
            return;
        }
        
        // Statisztikák lekérése
        $stats = $module->getSyncStatistics();
        
        // Biztosítjuk, hogy minden kulcs létezik
        $stats = $this->ensureStatsStructure($stats);
        
        // JAVÍTVA: getLocalPath() használata _path helyett
        $this->context->smarty->assign([
            'stats' => $stats,
            'module_dir' => $module->getLocalPath()  // ← JAVÍTVA!
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
