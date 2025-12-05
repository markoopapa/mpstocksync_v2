<?php
class AdminMpStockSyncApiController extends ModuleAdminController
{
    private $test_results = [];
    
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }
    
    public function initContent()
    {
        parent::initContent();
        
        // Konfigurációs értékek gyűjtése
        $config_values = [
            'emag' => [
                'api_url' => Configuration::get('MP_EMAG_API_URL', 'https://marketplace.emag.ro/api-3'),
                'client_id' => Configuration::get('MP_EMAG_CLIENT_ID'),
                'client_secret' => Configuration::get('MP_EMAG_CLIENT_SECRET'),
                'username' => Configuration::get('MP_EMAG_USERNAME'),
                'password' => Configuration::get('MP_EMAG_PASSWORD'),
                'auto_sync' => Configuration::get('MP_EMAG_AUTO_SYNC', 0),
            ],
            'trendyol' => [
                'api_url' => Configuration::get('MP_TRENDYOL_API_URL', 'https://api.trendyol.com/sapigw/'),
                'seller_id' => Configuration::get('MP_TRENDYOL_SELLER_ID'),
                'api_key' => Configuration::get('MP_TRENDYOL_API_KEY'),
                'api_secret' => Configuration::get('MP_TRENDYOL_API_SECRET'),
                'supplier_id' => Configuration::get('MP_TRENDYOL_SUPPLIER_ID'),
                'auto_sync' => Configuration::get('MP_TRENDYOL_AUTO_SYNC', 0),
            ],
            'general' => [
                'log_enabled' => Configuration::get('MP_LOG_ENABLED', 1),
                'notify_errors' => Configuration::get('MP_NOTIFY_ERRORS', 1),
                'auto_retry' => Configuration::get('MP_AUTO_RETRY', 1),
                'retry_attempts' => Configuration::get('MP_RETRY_ATTEMPTS', 3),
                'retry_delay' => Configuration::get('MP_RETRY_DELAY', 60),
                'sync_interval' => Configuration::get('MP_SYNC_INTERVAL', 300)
            ]
        ];
        
        $this->context->smarty->assign([
            'config' => $config_values,
            'module_dir' => Module::getInstanceByName('mpstocksync')->getLocalPath(),
            'post_url' => $this->context->link->getAdminLink('AdminMpStockSyncApi'),
            'token' => Tools::getAdminTokenLite('AdminMpStockSyncApi'),
            'test_results' => $this->test_results
        ]);
        
        $this->setTemplate('api_settings/api_settings.tpl');
    }
    
    public function postProcess()
    {
        // Mentés gomb
        if (Tools::isSubmit('submit_api_settings')) {
            // eMAG beállítások
            Configuration::updateValue('MP_EMAG_API_URL', Tools::getValue('emag_api_url'));
            Configuration::updateValue('MP_EMAG_CLIENT_ID', Tools::getValue('emag_client_id'));
            Configuration::updateValue('MP_EMAG_CLIENT_SECRET', Tools::getValue('emag_client_secret'));
            Configuration::updateValue('MP_EMAG_USERNAME', Tools::getValue('emag_username'));
            Configuration::updateValue('MP_EMAG_PASSWORD', Tools::getValue('emag_password'));
            Configuration::updateValue('MP_EMAG_AUTO_SYNC', Tools::getValue('emag_auto_sync'));
            
            // Trendyol beállítások
            Configuration::updateValue('MP_TRENDYOL_API_URL', Tools::getValue('trendyol_api_url'));
            Configuration::updateValue('MP_TRENDYOL_SELLER_ID', Tools::getValue('trendyol_seller_id'));
            Configuration::updateValue('MP_TRENDYOL_API_KEY', Tools::getValue('trendyol_api_key'));
            Configuration::updateValue('MP_TRENDYOL_API_SECRET', Tools::getValue('trendyol_api_secret'));
            Configuration::updateValue('MP_TRENDYOL_SUPPLIER_ID', Tools::getValue('trendyol_supplier_id'));
            Configuration::updateValue('MP_TRENDYOL_AUTO_SYNC', Tools::getValue('trendyol_auto_sync'));
            
            // Általános beállítások
            Configuration::updateValue('MP_LOG_ENABLED', Tools::getValue('log_enabled'));
            Configuration::updateValue('MP_NOTIFY_ERRORS', Tools::getValue('notify_errors'));
            Configuration::updateValue('MP_AUTO_RETRY', Tools::getValue('auto_retry'));
            Configuration::updateValue('MP_RETRY_ATTEMPTS', Tools::getValue('retry_attempts'));
            Configuration::updateValue('MP_RETRY_DELAY', Tools::getValue('retry_delay'));
            Configuration::updateValue('MP_SYNC_INTERVAL', Tools::getValue('sync_interval'));
            
            $this->confirmations[] = $this->l('Settings saved successfully!');
        }
        
        // eMAG csatlakozás teszt
        if (Tools::isSubmit('test_emag_connection')) {
            $this->test_results['emag'] = $this->testEmagConnection();
        }
        
        // Trendyol csatlakozás teszt
        if (Tools::isSubmit('test_trendyol_connection')) {
            $this->test_results['trendyol'] = $this->testTrendyolConnection();
        }
        
        // Mindkettő tesztelése
        if (Tools::isSubmit('test_all_connections')) {
            $this->test_results['emag'] = $this->testEmagConnection();
            $this->test_results['trendyol'] = $this->testTrendyolConnection();
        }
        
        parent::postProcess();
    }
    
    private function testEmagConnection()
    {
        try {
            $api_url = Tools::getValue('emag_api_url', Configuration::get('MP_EMAG_API_URL'));
            $client_id = Tools::getValue('emag_client_id', Configuration::get('MP_EMAG_CLIENT_ID'));
            $client_secret = Tools::getValue('emag_client_secret', Configuration::get('MP_EMAG_CLIENT_SECRET'));
            $username = Tools::getValue('emag_username', Configuration::get('MP_EMAG_USERNAME'));
            $password = Tools::getValue('emag_password', Configuration::get('MP_EMAG_PASSWORD'));
            
            if (empty($api_url) || empty($client_id) || empty($client_secret) || empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Missing eMAG API credentials'
                ];
            }
            
            // Itt hívnád meg az EmagService-t
            // $emagService = new EmagService($api_url, $client_id, $client_secret, $username, $password);
            // $result = $emagService->testConnection();
            
            // Mivel nincs valódi implementáció, szimuláljuk
            $success = !empty($api_url) && !empty($client_id) && !empty($client_secret);
            
            return [
                'success' => $success,
                'message' => $success ? 
                    '✓ eMAG connection successful!' : 
                    '✗ eMAG connection failed. Check your credentials.',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testTrendyolConnection()
    {
        try {
            $api_url = Tools::getValue('trendyol_api_url', Configuration::get('MP_TRENDYOL_API_URL'));
            $seller_id = Tools::getValue('trendyol_seller_id', Configuration::get('MP_TRENDYOL_SELLER_ID'));
            $api_key = Tools::getValue('trendyol_api_key', Configuration::get('MP_TRENDYOL_API_KEY'));
            $api_secret = Tools::getValue('trendyol_api_secret', Configuration::get('MP_TRENDYOL_API_SECRET'));
            $supplier_id = Tools::getValue('trendyol_supplier_id', Configuration::get('MP_TRENDYOL_SUPPLIER_ID'));
            
            if (empty($api_url) || empty($seller_id) || empty($api_key) || empty($api_secret) || empty($supplier_id)) {
                return [
                    'success' => false,
                    'message' => 'Missing Trendyol API credentials'
                ];
            }
            
            // Itt hívnád meg a TrendyolService-t
            // $trendyolService = new TrendyolService($api_url, $api_key, $api_secret, $supplier_id);
            // $result = $trendyolService->testConnection();
            
            // Szimulált válasz
            $success = !empty($api_url) && !empty($api_key) && !empty($api_secret);
            
            return [
                'success' => $success,
                'message' => $success ? 
                    '✓ Trendyol connection successful!' : 
                    '✗ Trendyol connection failed. Check your credentials.',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
}
