<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use MpStockSync\Services\EmagService;
use MpStockSync\Services\TrendyolService;
use MpStockSync\Models\ProductMapping;

class MpStockSync extends Module
{
    private $emagService;
    private $trendyolService;
    
    public function __construct()
    {
        $this->name = 'mpstocksync';
        $this->tab = 'administration';
        $this->version = '2.0.0';
        $this->author = 'MP Team';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '9.9.9'
        ];
        $this->bootstrap = true;
        $this->module_key = 'mpstocksync2024';
        
        parent::__construct();
        
        $this->displayName = $this->l('MP Stock Sync Pro');
        $this->description = $this->l('Professional eMAG and Trendyol stock synchronization');
        $this->confirmUninstall = $this->l('Are you sure? All data will be lost!');
        
        // Initialize services
        $this->initServices();
    }
    
    private function initServices()
    {
        $this->emagService = new EmagService(
            Configuration::get('MP_EMAG_API_URL'),
            Configuration::get('MP_EMAG_CLIENT_ID'),
            Configuration::get('MP_EMAG_CLIENT_SECRET'),
            Configuration::get('MP_EMAG_USERNAME'),
            Configuration::get('MP_EMAG_PASSWORD')
        );
        
        $this->trendyolService = new TrendyolService(
            Configuration::get('MP_TRENDYOL_API_URL'),
            Configuration::get('MP_TRENDYOL_API_KEY'),
            Configuration::get('MP_TRENDYOL_API_SECRET'),
            Configuration::get('MP_TRENDYOL_SUPPLIER_ID')
        );
    }
    
    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        
        // Install database tables
        if (!$this->installDatabase()) {
            return false;
        }
        
        // Install admin tabs
        if (!$this->installTabs()) {
            return false;
        }
        
        // Register hooks
        $hooks = [
            'actionUpdateQuantity',
            'actionProductSave',
            'actionProductUpdate',
            'actionAdminControllerSetMedia',
            'displayBackOfficeHeader',
            'actionObjectStockAddAfter',
            'actionObjectStockUpdateAfter',
            'actionObjectStockDeleteAfter'
        ];
        
        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }
        
        // Set default configurations
        $defaults = [
            'MP_EMAG_AUTO_SYNC' => 1,
            'MP_TRENDYOL_AUTO_SYNC' => 1,
            'MP_SYNC_INTERVAL' => 300, // 5 minutes
            'MP_LOG_ENABLED' => 1,
            'MP_NOTIFY_ERRORS' => 1,
            'MP_EMAG_TEST_MODE' => 1,
            'MP_TRENDYOL_TEST_MODE' => 1,
        ];
        
        foreach ($defaults as $key => $value) {
            Configuration::updateValue($key, $value);
        }
        
        return true;
    }
    
    public function uninstall()
    {
        // Delete configurations
        $configs = [
            'MP_EMAG_API_URL', 'MP_EMAG_CLIENT_ID', 'MP_EMAG_CLIENT_SECRET',
            'MP_EMAG_USERNAME', 'MP_EMAG_PASSWORD', 'MP_EMAG_AUTO_SYNC',
            'MP_TRENDYOL_API_URL', 'MP_TRENDYOL_API_KEY', 'MP_TRENDYOL_API_SECRET',
            'MP_TRENDYOL_SUPPLIER_ID', 'MP_TRENDYOL_AUTO_SYNC',
            'MP_SYNC_INTERVAL', 'MP_LOG_ENABLED', 'MP_NOTIFY_ERRORS'
        ];
        
        foreach ($configs as $config) {
            Configuration::deleteByName($config);
        }
        
        // Uninstall tabs
        $this->uninstallTabs();
        
        // Drop database tables
        $this->uninstallDatabase();
        
        return parent::uninstall();
    }
    
    private function installDatabase()
    {
        $sql = [];
        
        // Main sync log table
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mpstocksync_log` (
            `id_log` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `api_name` VARCHAR(20) NOT NULL COMMENT "emag/trendyol",
            `id_product` INT(11) NOT NULL,
            `id_product_attribute` INT(11) DEFAULT 0,
            `action` VARCHAR(50) NOT NULL COMMENT "stock_update/price_update/both",
            `old_value` TEXT,
            `new_value` TEXT,
            `status` TINYINT(1) DEFAULT 0 COMMENT "0=failed,1=success",
            `error_message` TEXT,
            `response_data` TEXT,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_log`),
            INDEX `api_product` (`api_name`, `id_product`),
            INDEX `date_status` (`date_add`, `status`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        
        // Product mapping table
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mpstocksync_mapping` (
            `id_mapping` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(11) NOT NULL,
            `id_product_attribute` INT(11) DEFAULT 0,
            `api_name` VARCHAR(20) NOT NULL COMMENT "emag/trendyol",
            `external_id` VARCHAR(100) NOT NULL COMMENT "SKU for eMAG, Barcode for Trendyol",
            `external_reference` VARCHAR(100) COMMENT "External SKU/ID",
            `sync_stock` TINYINT(1) DEFAULT 1,
            `sync_price` TINYINT(1) DEFAULT 1,
            `last_sync` DATETIME,
            `sync_count` INT(11) DEFAULT 0,
            `active` TINYINT(1) DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_mapping`),
            UNIQUE KEY `unique_mapping` (`id_product`, `id_product_attribute`, `api_name`),
            INDEX `external_lookup` (`api_name`, `external_id`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        
        // Queue table for batch processing
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mpstocksync_queue` (
            `id_queue` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `api_name` VARCHAR(20) NOT NULL,
            `id_product` INT(11) NOT NULL,
            `id_product_attribute` INT(11) DEFAULT 0,
            `action_type` VARCHAR(50) NOT NULL COMMENT "stock/price/both",
            `payload` TEXT NOT NULL,
            `priority` TINYINT(1) DEFAULT 1 COMMENT "1=low,2=medium,3=high",
            `status` TINYINT(1) DEFAULT 0 COMMENT "0=pending,1=processing,2=completed,3=failed",
            `attempts` TINYINT(1) DEFAULT 0,
            `last_attempt` DATETIME,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_queue`),
            INDEX `pending_items` (`status`, `priority`, `date_add`),
            INDEX `api_status` (`api_name`, `status`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        
        // API configuration table
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mpstocksync_api_config` (
            `id_config` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `api_name` VARCHAR(20) NOT NULL,
            `api_url` VARCHAR(255),
            `api_key` VARCHAR(255),
            `api_secret` VARCHAR(255),
            `username` VARCHAR(100),
            `password` VARCHAR(100),
            `supplier_id` VARCHAR(50),
            `seller_id` VARCHAR(50),
            `test_mode` TINYINT(1) DEFAULT 1,
            `auto_sync` TINYINT(1) DEFAULT 1,
            `sync_interval` INT(11) DEFAULT 300,
            `last_sync_date` DATETIME,
            `status` TINYINT(1) DEFAULT 1,
            `settings` TEXT COMMENT "JSON additional settings",
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_config`),
            UNIQUE KEY `api_name` (`api_name`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        
        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                $this->_errors[] = 'Database error: ' . Db::getInstance()->getMsgError();
                return false;
            }
        }
        
        // Insert default API configurations
        $this->initApiConfigs();
        
        return true;
    }
    
    private function initApiConfigs()
    {
        // eMAG config
        Db::getInstance()->insert('mpstocksync_api_config', [
            'api_name' => 'emag',
            'api_url' => 'https://marketplace-api.emag.ro',
            'test_mode' => 1,
            'auto_sync' => 1,
            'status' => 0, // disabled by default
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ]);
        
        // Trendyol config
        Db::getInstance()->insert('mpstocksync_api_config', [
            'api_name' => 'trendyol',
            'api_url' => 'https://api.trendyol.com/sapigw',
            'test_mode' => 1,
            'auto_sync' => 1,
            'status' => 0, // disabled by default
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function uninstallDatabase()
    {
        $tables = [
            'mpstocksync_log',
            'mpstocksync_mapping',
            'mpstocksync_queue',
            'mpstocksync_api_config'
        ];
        
        foreach ($tables as $table) {
            Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.$table.'`');
        }
        
        return true;
    }
    
    private function installTabs()
    {
        $tabs = [
            [
                'class_name' => 'AdminMpStockSync',
                'name' => 'Stock Sync',
                'parent' => 'AdminCatalog',
                'icon' => 'sync'
            ],
            [
                'class_name' => 'AdminMpStockSyncDashboard',
                'name' => 'Dashboard',
                'parent' => 'AdminMpStockSync',
                'icon' => 'dashboard'
            ],
            [
                'class_name' => 'AdminMpStockSyncProducts',
                'name' => 'Product Mapping',
                'parent' => 'AdminMpStockSync',
                'icon' => 'link'
            ],
            [
                'class_name' => 'AdminMpStockSyncLogs',
                'name' => 'Sync Logs',
                'parent' => 'AdminMpStockSync',
                'icon' => 'list'
            ],
            [
                'class_name' => 'AdminMpStockSyncSettings',
                'name' => 'Settings',
                'parent' => 'AdminMpStockSync',
                'icon' => 'settings'
            ],
            [
                'class_name' => 'AdminMpStockSyncApi',
                'name' => 'API Settings',
                'parent' => 'AdminMpStockSync',
                'icon' => 'api'
            ]
        ];
        
        foreach ($tabs as $tabData) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $tabData['class_name'];
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName($tabData['parent']);
            
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $this->l($tabData['name']);
            }
            
            if (!$tab->add()) {
                return false;
            }
        }
        
        return true;
    }
    
    private function uninstallTabs()
    {
        $classes = [
            'AdminMpStockSync',
            'AdminMpStockSyncDashboard',
            'AdminMpStockSyncProducts',
            'AdminMpStockSyncLogs',
            'AdminMpStockSyncSettings',
            'AdminMpStockSyncApi'
        ];
        
        foreach ($classes as $className) {
            $id_tab = Tab::getIdFromClassName($className);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
        
        return true;
    }
    
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMpStockSyncDashboard'));
    }
    
    // HOOKS
    public function hookActionUpdateQuantity($params)
    {
        $id_product = (int)$params['id_product'];
        $id_product_attribute = isset($params['id_product_attribute']) ? (int)$params['id_product_attribute'] : 0;
        $new_quantity = (int)$params['quantity'];
        
        // Get old quantity
        $old_quantity = StockAvailable::getQuantityAvailableByProduct(
            $id_product,
            $id_product_attribute
        );
        
        // Auto-sync if enabled
        if (Configuration::get('MP_EMAG_AUTO_SYNC')) {
            $this->syncToEmag($id_product, $id_product_attribute, $new_quantity, 'stock');
        }
        
        if (Configuration::get('MP_TRENDYOL_AUTO_SYNC')) {
            $this->syncToTrendyol($id_product, $id_product_attribute, $new_quantity, 'stock');
        }
        
        // Log the change
        $this->logChange('quantity_update', $id_product, $id_product_attribute, [
            'old' => $old_quantity,
            'new' => $new_quantity
        ]);
    }
    
    public function hookActionProductSave($params)
    {
        $product = $params['product'];
        $this->handleProductSync($product->id, 0, 'product_update');
    }
    
    public function hookActionProductUpdate($params)
    {
        $id_product = (int)$params['id_product'];
        $product = new Product($id_product);
        
        // Check if price changed
        $old_price = Product::getPriceStatic($id_product, false);
        $new_price = $product->price;
        
        if ($old_price != $new_price) {
            if (Configuration::get('MP_EMAG_AUTO_SYNC')) {
                $this->syncToEmag($id_product, 0, $new_price, 'price');
            }
            
            if (Configuration::get('MP_TRENDYOL_AUTO_SYNC')) {
                $this->syncToTrendyol($id_product, 0, $new_price, 'price');
            }
            
            $this->logChange('price_update', $id_product, 0, [
                'old' => $old_price,
                'new' => $new_price
            ]);
        }
    }
    
    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('controller') == 'AdminMpStockSync' || 
            strpos(Tools::getValue('configure'), $this->name) !== false) {
            
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
            $this->context->controller->addJS($this->_path . 'views/js/sync.js');
            
            Media::addJsDef([
                'mpstocksync_ajax_url' => $this->context->link->getAdminLink('AdminMpStockSync'),
                'mpstocksync_token' => Tools::getAdminTokenLite('AdminMpStockSync'),
                'mpstocksync_messages' => [
                    'sync_success' => $this->l('Sync completed successfully'),
                    'sync_error' => $this->l('Sync failed'),
                    'confirm_sync_all' => $this->l('Are you sure you want to sync all products?')
                ]
            ]);
        }
    }
    
    // SYNC FUNCTIONS
    private function syncToEmag($id_product, $id_product_attribute, $value, $type = 'stock')
    {
        try {
            $mapping = ProductMapping::getByProduct($id_product, $id_product_attribute, 'emag');
            
            if (!$mapping || !$mapping['active']) {
                return false;
            }
            
            $product = new Product($id_product);
            $sku = $mapping['external_id'];
            
            if ($type == 'stock') {
                $result = $this->emagService->updateStock($sku, $value);
            } else {
                $price = $this->calculateEmagPrice($value, $product->id_tax_rules_group);
                $result = $this->emagService->updatePrice($sku, $price);
            }
            
            $this->logSync('emag', $id_product, $id_product_attribute, $type, $result);
            
            return $result['success'] ?? false;
            
        } catch (Exception $e) {
            $this->logError('emag', $id_product, $id_product_attribute, $e->getMessage());
            return false;
        }
    }
    
    private function syncToTrendyol($id_product, $id_product_attribute, $value, $type = 'stock')
    {
        try {
            $mapping = ProductMapping::getByProduct($id_product, $id_product_attribute, 'trendyol');
            
            if (!$mapping || !$mapping['active']) {
                return false;
            }
            
            $product = new Product($id_product);
            $barcode = $mapping['external_id'];
            
            if ($type == 'stock') {
                $price = Product::getPriceStatic($id_product, true, $id_product_attribute);
                $result = $this->trendyolService->updateStockAndPrice($barcode, $value, $price);
            } else {
                $stock = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
                $result = $this->trendyolService->updateStockAndPrice($barcode, $stock, $value);
            }
            
            $this->logSync('trendyol', $id_product, $id_product_attribute, $type, $result);
            
            return $result['success'] ?? false;
            
        } catch (Exception $e) {
            $this->logError('trendyol', $id_product, $id_product_attribute, $e->getMessage());
            return false;
        }
    }
    
    private function calculateEmagPrice($price, $tax_rules_group_id)
    {
        // Calculate price with VAT for eMAG
        $tax_manager = TaxManagerFactory::getManager($this->context->address, $tax_rules_group_id);
        $tax_calculator = $tax_manager->getTaxCalculator();
        
        $price_with_tax = $tax_calculator->addTaxes($price);
        
        return $price_with_tax;
    }
    
    // LOGGING FUNCTIONS
    private function logSync($api, $id_product, $id_product_attribute, $action, $result)
    {
        Db::getInstance()->insert('mpstocksync_log', [
            'api_name' => pSQL($api),
            'id_product' => (int)$id_product,
            'id_product_attribute' => (int)$id_product_attribute,
            'action' => pSQL($action),
            'status' => isset($result['success']) && $result['success'] ? 1 : 0,
            'response_data' => pSQL(json_encode($result)),
            'date_add' => date('Y-m-d H:i:s')
        ]);
        
        // Update mapping sync info
        Db::getInstance()->update('mpstocksync_mapping', [
            'last_sync' => date('Y-m-d H:i:s'),
            'sync_count' => 'sync_count + 1',
            'date_upd' => date('Y-m-d H:i:s')
        ], 'id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute.' AND api_name = "'.pSQL($api).'"');
    }
    
    private function logError($api, $id_product, $id_product_attribute, $error)
    {
        Db::getInstance()->insert('mpstocksync_log', [
            'api_name' => pSQL($api),
            'id_product' => (int)$id_product,
            'id_product_attribute' => (int)$id_product_attribute,
            'action' => 'error',
            'status' => 0,
            'error_message' => pSQL($error),
            'date_add' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function logChange($action, $id_product, $id_product_attribute, $data)
    {
        if (!Configuration::get('MP_LOG_ENABLED')) {
            return;
        }
        
        Db::getInstance()->insert('mpstocksync_log', [
            'api_name' => 'internal',
            'id_product' => (int)$id_product,
            'id_product_attribute' => (int)$id_product_attribute,
            'action' => pSQL($action),
            'old_value' => pSQL(json_encode($data['old'])),
            'new_value' => pSQL(json_encode($data['new'])),
            'status' => 1,
            'date_add' => date('Y-m-d H:i:s')
        ]);
    }
    
    // UTILITY FUNCTIONS
    public function getSyncStatistics()
    {
        $stats = [];
        
        // Total syncs
        $sql = 'SELECT api_name, COUNT(*) as total, 
                SUM(IF(status=1,1,0)) as success,
                SUM(IF(status=0,1,0)) as failed
                FROM `'._DB_PREFIX_.'mpstocksync_log`
                GROUP BY api_name';
        
        $result = Db::getInstance()->executeS($sql);
        
        foreach ($result as $row) {
            $stats[$row['api_name']] = [
                'total' => (int)$row['total'],
                'success' => (int)$row['success'],
                'failed' => (int)$row['failed'],
                'success_rate' => $row['total'] > 0 ? round(($row['success'] / $row['total']) * 100, 2) : 0
            ];
        }
        
        // Queue status
        $sql = 'SELECT api_name, status, COUNT(*) as count
                FROM `'._DB_PREFIX_.'mpstocksync_queue`
                GROUP BY api_name, status';
        
        $queue = Db::getInstance()->executeS($sql);
        
        foreach ($queue as $item) {
            if (!isset($stats[$item['api_name']]['queue'])) {
                $stats[$item['api_name']]['queue'] = [];
            }
            $stats[$item['api_name']]['queue'][$item['status']] = (int)$item['count'];
        }
        
        return $stats;
    }
    
    public function manualSyncAll($api_name = null)
    {
        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'id_product', 'ASC', false, true);
        
        $count = 0;
        $errors = 0;
        
        foreach ($products as $product) {
            $id_product = (int)$product['id_product'];
            $quantity = StockAvailable::getQuantityAvailableByProduct($id_product);
            $price = Product::getPriceStatic($id_product);
            
            if ($api_name === 'emag' || $api_name === null) {
                if (Configuration::get('MP_EMAG_AUTO_SYNC')) {
                    if (!$this->syncToEmag($id_product, 0, $quantity, 'stock')) {
                        $errors++;
                    }
                    $count++;
                }
            }
            
            if ($api_name === 'trendyol' || $api_name === null) {
                if (Configuration::get('MP_TRENDYOL_AUTO_SYNC')) {
                    if (!$this->syncToTrendyol($id_product, 0, $quantity, 'stock')) {
                        $errors++;
                    }
                    $count++;
                }
            }
        }
        
        return [
            'total' => $count,
            'success' => $count - $errors,
            'errors' => $errors
        ];
    }
    
    public function clearLogs($days = 30)
    {
        $date_limit = date('Y-m-d H:i:s', strtotime('-' . (int)$days . ' days'));
        
        return Db::getInstance()->delete('mpstocksync_log', 'date_add < "' . pSQL($date_limit) . '"');
    }
    
    // AJAX HANDLING
    public function ajaxProcessManualSync()
    {
        $api = Tools::getValue('api');
        $result = $this->manualSyncAll($api);
        
        die(json_encode($result));
    }
    
    public function ajaxProcessClearLogs()
    {
        $days = Tools::getValue('days', 30);
        $result = $this->clearLogs($days);
        
        die(json_encode(['success' => $result]));
    }
    
    public function ajaxProcessTestConnection()
    {
        $api = Tools::getValue('api');
        $result = [];
        
        if ($api == 'emag') {
            $result = $this->emagService->testConnection();
        } elseif ($api == 'trendyol') {
            $result = $this->trendyolService->testConnection();
        }
        
        die(json_encode($result));
    }
}
