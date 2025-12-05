<?php
// cron/supplier_sync.php
require_once dirname(__FILE__) . '../../config/config.inc.php';
require_once dirname(__FILE__) . '../../init.php';

// Only run from command line or cron
if (php_sapi_name() !== 'cli' && !strpos($_SERVER['REQUEST_URI'], 'cron')) {
    die('Access denied');
}

$module = Module::getInstanceByName('mpstocksync');

if (!$module) {
    die('Module not found');
}

// Get active suppliers with auto sync enabled
$suppliers = Db::getInstance()->executeS('
    SELECT * FROM `'._DB_PREFIX_.'mpstocksync_suppliers`
    WHERE active = 1 AND auto_sync = 1
    AND (
        last_sync IS NULL 
        OR DATE_ADD(last_sync, INTERVAL sync_interval MINUTE) < NOW()
    )
');

foreach ($suppliers as $supplier) {
    try {
        $service = new SupplierSyncService($supplier['id_supplier']);
        $result = $service->syncSupplierToShops($supplier['id_supplier']);
        
        // Log result
        Db::getInstance()->insert('mpstocksync_cron_log', [
            'id_supplier' => $supplier['id_supplier'],
            'total_products' => $result['total'],
            'updated' => $result['updated'],
            'errors' => $result['errors'],
            'status' => $result['errors'] == 0 ? 1 : 2,
            'date_add' => date('Y-m-d H:i:s')
        ]);
        
        echo "Supplier {$supplier['name']}: {$result['updated']}/{$result['total']} updated, {$result['errors']} errors\n";
        
    } catch (Exception $e) {
        echo "Error with supplier {$supplier['name']}: " . $e->getMessage() . "\n";
    }
}

echo "Cron job completed at " . date('Y-m-d H:i:s') . "\n";
