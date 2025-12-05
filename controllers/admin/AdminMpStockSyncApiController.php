<?php
class AdminMpStockSyncApiController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }
    
    public function initContent()
    {
        parent::initContent();
        
        // Konfigurációs értékek gyűjtése - JAVÍTVA
        $config_values = [
            'emag' => [
                'api_url' => Configuration::get('MP_EMAG_API_URL', 'https://marketplace.emag.ro/api-3'),
                'client_id' => Configuration::get('MP_EMAG_CLIENT_ID'),
                'client_secret' => Configuration::get('MP_EMAG_CLIENT_SECRET'),
                'username' => Configuration::get('MP_EMAG_USERNAME'),
                'password' => Configuration::get('MP_EMAG_PASSWORD'),
                'vat_rate' => Configuration::get('MP_EMAG_VAT_RATE', 19),
                'language' => Configuration::get('MP_EMAG_LANGUAGE', 'ro'),
                'auto_sync' => Configuration::get('MP_EMAG_AUTO_SYNC', 0),
                'test_mode' => Configuration::get('MP_EMAG_TEST_MODE', 1)
            ],
            'trendyol' => [
                'api_url' => Configuration::get('MP_TRENDYOL_API_URL', 'https://api.trendyol.com/sapigw/'),
                'seller_id' => Configuration::get('MP_TRENDYOL_SELLER_ID'),
                'api_key' => Configuration::get('MP_TRENDYOL_API_KEY'),
                'api_secret' => Configuration::get('MP_TRENDYOL_API_SECRET'),
                'supplier_id' => Configuration::get('MP_TRENDYOL_SUPPLIER_ID'),
                'integration_id' => Configuration::get('MP_TRENDYOL_INTEGRATION_ID'),
                'token' => Configuration::get('MP_TRENDYOL_TOKEN'),
                'auto_sync' => Configuration::get('MP_TRENDYOL_AUTO_SYNC', 0),
                'test_mode' => Configuration::get('MP_TRENDYOL_TEST_MODE', 1)
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
            'languages' => [
                ['id' => 'ro', 'name' => 'Română'],
                ['id' => 'hu', 'name' => 'Magyar'],
                ['id' => 'bg', 'name' => 'Български']
            ],
            'vat_rates' => [
                ['value' => 0, 'name' => '0%'],
                ['value' => 5, 'name' => '5%'],
                ['value' => 9, 'name' => '9%'],
                ['value' => 19, 'name' => '19%'],
                ['value' => 24, 'name' => '24%']
            ]
        ]);
        
        $this->setTemplate('api_settings/api_settings.tpl');
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('submit_api_settings')) {
            // eMAG beállítások - JAVÍTVA
            Configuration::updateValue('MP_EMAG_API_URL', Tools::getValue('emag_api_url'));
            Configuration::updateValue('MP_EMAG_CLIENT_ID', Tools::getValue('emag_client_id'));
            Configuration::updateValue('MP_EMAG_CLIENT_SECRET', Tools::getValue('emag_client_secret'));
            Configuration::updateValue('MP_EMAG_USERNAME', Tools::getValue('emag_username'));
            Configuration::updateValue('MP_EMAG_PASSWORD', Tools::getValue('emag_password'));
            Configuration::updateValue('MP_EMAG_VAT_RATE', Tools::getValue('emag_vat_rate'));
            Configuration::updateValue('MP_EMAG_LANGUAGE', Tools::getValue('emag_language'));
            Configuration::updateValue('MP_EMAG_AUTO_SYNC', Tools::getValue('emag_auto_sync'));
            Configuration::updateValue('MP_EMAG_TEST_MODE', Tools::getValue('emag_test_mode'));
            
            // Trendyol beállítások - JAVÍTVA
            Configuration::updateValue('MP_TRENDYOL_API_URL', Tools::getValue('trendyol_api_url'));
            Configuration::updateValue('MP_TRENDYOL_SELLER_ID', Tools::getValue('trendyol_seller_id'));
            Configuration::updateValue('MP_TRENDYOL_API_KEY', Tools::getValue('trendyol_api_key'));
            Configuration::updateValue('MP_TRENDYOL_API_SECRET', Tools::getValue('trendyol_api_secret'));
            Configuration::updateValue('MP_TRENDYOL_SUPPLIER_ID', Tools::getValue('trendyol_supplier_id'));
            Configuration::updateValue('MP_TRENDYOL_INTEGRATION_ID', Tools::getValue('trendyol_integration_id'));
            Configuration::updateValue('MP_TRENDYOL_TOKEN', Tools::getValue('trendyol_token'));
            Configuration::updateValue('MP_TRENDYOL_AUTO_SYNC', Tools::getValue('trendyol_auto_sync'));
            Configuration::updateValue('MP_TRENDYOL_TEST_MODE', Tools::getValue('trendyol_test_mode'));
            
            // Általános beállítások
            Configuration::updateValue('MP_LOG_ENABLED', Tools::getValue('log_enabled'));
            Configuration::updateValue('MP_NOTIFY_ERRORS', Tools::getValue('notify_errors'));
            Configuration::updateValue('MP_AUTO_RETRY', Tools::getValue('auto_retry'));
            Configuration::updateValue('MP_RETRY_ATTEMPTS', Tools::getValue('retry_attempts'));
            Configuration::updateValue('MP_RETRY_DELAY', Tools::getValue('retry_delay'));
            Configuration::updateValue('MP_SYNC_INTERVAL', Tools::getValue('sync_interval'));
            
            $this->confirmations[] = $this->l('Settings saved successfully');
        }
        
        // API tesztelés gomb
        if (Tools::isSubmit('test_emag_connection')) {
            $this->testEmagConnection();
        }
        
        if (Tools::isSubmit('test_trendyol_connection')) {
            $this->testTrendyolConnection();
        }
        
        parent::postProcess();
    }
    
    private function testEmagConnection()
    {
        try {
            $module = Module::getInstanceByName('mpstocksync');
            if ($module->emagService === null) {
                $module->emagService = new EmagService(
                    Configuration::get('MP_EMAG_API_URL'),
                    Configuration::get('MP_EMAG_CLIENT_ID'),
                    Configuration::get('MP_EMAG_CLIENT_SECRET'),
                    Configuration::get('MP_EMAG_USERNAME'),
                    Configuration::get('MP_EMAG_PASSWORD')
                );
            }
            
            $result = $module->emagService->testConnection();
            if ($result['success']) {
                $this->confirmations[] = $this->l('eMAG connection test successful!');
            } else {
                $this->errors[] = $this->l('eMAG connection failed: ') . $result['message'];
            }
        } catch (Exception $e) {
            $this->errors[] = $this->l('eMAG connection error: ') . $e->getMessage();
        }
    }
    
    private function testTrendyolConnection()
    {
        try {
            $module = Module::getInstanceByName('mpstocksync');
            if ($module->trendyolService === null) {
                $module->trendyolService = new TrendyolService(
                    Configuration::get('MP_TRENDYOL_API_URL'),
                    Configuration::get('MP_TRENDYOL_API_KEY'),
                    Configuration::get('MP_TRENDYOL_API_SECRET'),
                    Configuration::get('MP_TRENDYOL_SUPPLIER_ID')
                );
            }
            
            $result = $module->trendyolService->testConnection();
            if ($result['success']) {
                $this->confirmations[] = $this->l('Trendyol connection test successful!');
            } else {
                $this->errors[] = $this->l('Trendyol connection failed: ') . $result['message'];
            }
        } catch (Exception $e) {
            $this->errors[] = $this->l('Trendyol connection error: ') . $e->getMessage();
        }
    }
}
