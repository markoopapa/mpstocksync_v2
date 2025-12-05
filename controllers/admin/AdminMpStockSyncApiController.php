<?php
class AdminMpStockSyncApiController extends ModuleAdminController
{
    private $api_name;
    
    public function __construct()
    {
        $this->bootstrap = true;
        $this->api_name = Tools::getValue('configure', 'emag');
        
        parent::__construct();
        
        $this->meta_title = ucfirst($this->api_name) . ' API Settings';
    }
    
    public function initContent()
    {
        parent::initContent();
        
        if (Tools::isSubmit('save_' . $this->api_name)) {
            $this->processSave();
        }
        
        if (Tools::isSubmit('test_' . $this->api_name)) {
            $this->processTest();
        }
        
        $this->context->smarty->assign([
            'api_name' => $this->api_name,
            'api_config' => $this->getApiConfig(),
            'form' => $this->renderForm(),
            'test_result' => isset($_SESSION['mpstocksync_test_result']) ? 
                $_SESSION['mpstocksync_test_result'] : null
        ]);
        
        unset($_SESSION['mpstocksync_test_result']);
        
        $this->setTemplate('api_settings.tpl');
    }
    
    private function getApiConfig()
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'mpstocksync_api_config`
                WHERE api_name = "'.pSQL($this->api_name).'"';
        
        $config = Db::getInstance()->getRow($sql);
        
        if (!$config) {
            $config = [
                'api_name' => $this->api_name,
                'status' => 0,
                'test_mode' => 1,
                'auto_sync' => 0,
                'sync_interval' => 300
            ];
        }
        
        // Merge with configuration values
        if ($this->api_name == 'emag') {
            $config['api_url'] = Configuration::get('MP_EMAG_API_URL');
            $config['client_id'] = Configuration::get('MP_EMAG_CLIENT_ID');
            $config['client_secret'] = Configuration::get('MP_EMAG_CLIENT_SECRET');
            $config['username'] = Configuration::get('MP_EMAG_USERNAME');
            $config['password'] = Configuration::get('MP_EMAG_PASSWORD');
        } elseif ($this->api_name == 'trendyol') {
            $config['api_url'] = Configuration::get('MP_TRENDYOL_API_URL');
            $config['api_key'] = Configuration::get('MP_TRENDYOL_API_KEY');
            $config['api_secret'] = Configuration::get('MP_TRENDYOL_API_SECRET');
            $config['supplier_id'] = Configuration::get('MP_TRENDYOL_SUPPLIER_ID');
        }
        
        return $config;
    }
    
    private function renderForm()
    {
        if ($this->api_name == 'emag') {
            return $this->renderEmagForm();
        } elseif ($this->api_name == 'trendyol') {
            return $this->renderTrendyolForm();
        }
        
        return '';
    }
    
    private function renderEmagForm()
    {
        $fields = [
            'form' => [
                'legend' => [
                    'title' => 'eMAG API Configuration',
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => 'API URL',
                        'name' => 'MP_EMAG_API_URL',
                        'required' => true,
                        'value' => Configuration::get('MP_EMAG_API_URL'),
                        'desc' => 'e.g., https://marketplace-api.emag.ro'
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Client ID',
                        'name' => 'MP_EMAG_CLIENT_ID',
                        'required' => true,
                        'value' => Configuration::get('MP_EMAG_CLIENT_ID')
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Client Secret',
                        'name' => 'MP_EMAG_CLIENT_SECRET',
                        'required' => true,
                        'value' => Configuration::get('MP_EMAG_CLIENT_SECRET')
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Username',
                        'name' => 'MP_EMAG_USERNAME',
                        'required' => true,
                        'value' => Configuration::get('MP_EMAG_USERNAME'),
                        'desc' => 'Your eMAG marketplace email'
                    ],
                    [
                        'type' => 'password',
                        'label' => 'Password',
                        'name' => 'MP_EMAG_PASSWORD',
                        'required' => true,
                        'value' => Configuration::get('MP_EMAG_PASSWORD')
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Enabled',
                        'name' => 'emag_status',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1],
                            ['id' => 'active_off', 'value' => 0]
                        ],
                        'value' => $this->getApiConfig()['status']
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Test Mode',
                        'name' => 'emag_test_mode',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1],
                            ['id' => 'active_off', 'value' => 0]
                        ],
                        'desc' => 'Use sandbox environment',
                        'value' => $this->getApiConfig()['test_mode']
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Auto Sync',
                        'name' => 'emag_auto_sync',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1],
                            ['id' => 'active_off', 'value' => 0]
                        ],
                        'value' => $this->getApiConfig()['auto_sync']
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Sync Interval (seconds)',
                        'name' => 'emag_sync_interval',
                        'suffix' => 'seconds',
                        'value' => $this->getApiConfig()['sync_interval'],
                        'desc' => 'How often to check for changes'
                    ]
                ],
                'submit' => [
                    'title' => 'Save',
                    'name' => 'save_emag',
                    'class' => 'btn btn-default pull-right'
                ],
                'buttons' => [
                    'testButton' => [
                        'title' => 'Test Connection',
                        'name' => 'test_emag',
                        'type' => 'submit',
                        'class' => 'btn btn-info',
                        'icon' => 'process-icon-refresh'
                    ]
                ]
            ]
        ];
        
        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->name_controller = $this->controller_name;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->api_name;
        $helper->title = $this->meta_title;
        
        return $helper->generateForm([$fields]);
    }
    
    private function renderTrendyolForm()
    {
        $fields = [
            'form' => [
                'legend' => [
                    'title' => 'Trendyol API Configuration',
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => 'API URL',
                        'name' => 'MP_TRENDYOL_API_URL',
                        'required' => true,
                        'value' => Configuration::get('MP_TRENDYOL_API_URL'),
                        'desc' => 'e.g., https://api.trendyol.com/sapigw'
                    ],
                    [
                        'type' => 'text',
                        'label' => 'API Key',
                        'name' => 'MP_TRENDYOL_API_KEY',
                        'required' => true,
                        'value' => Configuration::get('MP_TRENDYOL_API_KEY')
                    ],
                    [
                        'type' => 'text',
                        'label' => 'API Secret',
                        'name' => 'MP_TRENDYOL_API_SECRET',
                        'required' => true,
                        'value' => Configuration::get('MP_TRENDYOL_API_SECRET')
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Supplier ID',
                        'name' => 'MP_TRENDYOL_SUPPLIER_ID',
                        'required' => true,
                        'value' => Configuration::get('MP_TRENDYOL_SUPPLIER_ID')
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Enabled',
                        'name' => 'trendyol_status',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1],
                            ['id' => 'active_off', 'value' => 0]
                        ],
                        'value' => $this->getApiConfig()['status']
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Test Mode',
                        'name' => 'trendyol_test_mode',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1],
                            ['id' => 'active_off', 'value' => 0]
                        ],
                        'desc' => 'Use test environment',
                        'value' => $this->getApiConfig()['test_mode']
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Auto Sync',
                        'name' => 'trendyol_auto_sync',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1],
                            ['id' => 'active_off', 'value' => 0]
                        ],
                        'value' => $this->getApiConfig()['auto_sync']
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Sync Interval (seconds)',
                        'name' => 'trendyol_sync_interval',
                        'suffix' => 'seconds',
                        'value' => $this->getApiConfig()['sync_interval'],
                        'desc' => 'How often to check for changes'
                    ]
                ],
                'submit' => [
                    'title' => 'Save',
                    'name' => 'save_trendyol',
                    'class' => 'btn btn-default pull-right'
                ],
                'buttons' => [
                    'testButton' => [
                        'title' => 'Test Connection',
                        'name' => 'test_trendyol',
                        'type' => 'submit',
                        'class' => 'btn btn-info',
                        'icon' => 'process-icon-refresh'
                    ]
                ]
            ]
        ];
        
        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->name_controller = $this->controller_name;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->api_name;
        $helper->title = $this->meta_title;
        
        return $helper->generateForm([$fields]);
    }
    
    private function processSave()
    {
        if ($this->api_name == 'emag') {
            Configuration::updateValue('MP_EMAG_API_URL', Tools::getValue('MP_EMAG_API_URL'));
            Configuration::updateValue('MP_EMAG_CLIENT_ID', Tools::getValue('MP_EMAG_CLIENT_ID'));
            Configuration::updateValue('MP_EMAG_CLIENT_SECRET', Tools::getValue('MP_EMAG_CLIENT_SECRET'));
            Configuration::updateValue('MP_EMAG_USERNAME', Tools::getValue('MP_EMAG_USERNAME'));
            Configuration::updateValue('MP_EMAG_PASSWORD', Tools::getValue('MP_EMAG_PASSWORD'));
            
            $status = Db::getInstance()->update('mpstocksync_api_config', [
                'status' => (int)Tools::getValue('emag_status'),
                'test_mode' => (int)Tools::getValue('emag_test_mode'),
                'auto_sync' => (int)Tools::getValue('emag_auto_sync'),
                'sync_interval' => (int)Tools::getValue('emag_sync_interval'),
                'date_upd' => date('Y-m-d H:i:s')
            ], 'api_name = "emag"');
            
        } elseif ($this->api_name == 'trendyol') {
            Configuration::updateValue('MP_TRENDYOL_API_URL', Tools::getValue('MP_TRENDYOL_API_URL'));
            Configuration::updateValue('MP_TRENDYOL_API_KEY', Tools::getValue('MP_TRENDYOL_API_KEY'));
            Configuration::updateValue('MP_TRENDYOL_API_SECRET', Tools::getValue('MP_TRENDYOL_API_SECRET'));
            Configuration::updateValue('MP_TRENDYOL_SUPPLIER_ID', Tools::getValue('MP_TRENDYOL_SUPPLIER_ID'));
            
            $status = Db::getInstance()->update('mpstocksync_api_config', [
                'status' => (int)Tools::getValue('trendyol_status'),
                'test_mode' => (int)Tools::getValue('trendyol_test_mode'),
                'auto_sync' => (int)Tools::getValue('trendyol_auto_sync'),
                'sync_interval' => (int)Tools::getValue('trendyol_sync_interval'),
                'date_upd' => date('Y-m-d H:i:s')
            ], 'api_name = "trendyol"');
        }
        
        $this->confirmations[] = 'Settings saved successfully';
    }
    
    private function processTest()
    {
        $module = Module::getInstanceByName('mpstocksync');
        
        if ($this->api_name == 'emag') {
            $service = new EmagService(
                Tools::getValue('MP_EMAG_API_URL'),
                Tools::getValue('MP_EMAG_CLIENT_ID'),
                Tools::getValue('MP_EMAG_CLIENT_SECRET'),
                Tools::getValue('MP_EMAG_USERNAME'),
                Tools::getValue('MP_EMAG_PASSWORD')
            );
            
            $result = $service->testConnection();
            
        } elseif ($this->api_name == 'trendyol') {
            $service = new TrendyolService(
                Tools::getValue('MP_TRENDYOL_API_URL'),
                Tools::getValue('MP_TRENDYOL_API_KEY'),
                Tools::getValue('MP_TRENDYOL_API_SECRET'),
                Tools::getValue('MP_TRENDYOL_SUPPLIER_ID')
            );
            
            $result = $service->testConnection();
        }
        
        $_SESSION['mpstocksync_test_result'] = $result;
        
        if ($result['success']) {
            $this->confirmations[] = 'Connection test successful!';
        } else {
            $this->errors[] = 'Connection test failed: ' . $result['message'];
        }
    }
}
