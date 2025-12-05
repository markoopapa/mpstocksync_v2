<?php
// controllers/admin/AdminMpStockSyncSupplierController.php
class AdminMpStockSyncSupplierController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        
        $this->meta_title = 'Supplier Configuration';
    }
    
    public function initContent()
    {
        parent::initContent();
        
        if (Tools::isSubmit('save_supplier')) {
            $this->processSaveSupplier();
        }
        
        if (Tools::isSubmit('test_supplier')) {
            $this->processTestConnection();
        }
        
        if (Tools::isSubmit('sync_now')) {
            $this->processSyncNow();
        }
        
        $this->context->smarty->assign([
            'supplier_form' => $this->renderSupplierForm(),
            'suppliers' => $this->getSuppliers(),
            'sync_stats' => $this->getSyncStats()
        ]);
        
        $this->setTemplate('supplier_config.tpl');
    }
    
    private function renderSupplierForm()
    {
        $fields = [
            'form' => [
                'legend' => [
                    'title' => 'Supplier Configuration',
                    'icon' => 'icon-truck'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => 'Supplier Name',
                        'name' => 'supplier_name',
                        'required' => true
                    ],
                    [
                        'type' => 'select',
                        'label' => 'Connection Type',
                        'name' => 'connection_type',
                        'required' => true,
                        'options' => [
                            'query' => [
                                ['id' => 'database', 'name' => 'Direct Database'],
                                ['id' => 'api', 'name' => 'PrestaShop Web Service API']
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ],
                    // Database fields
                    [
                        'type' => 'text',
                        'label' => 'Database Host',
                        'name' => 'db_host',
                        'desc' => 'e.g., localhost or IP address'
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Database Name',
                        'name' => 'db_name'
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Database User',
                        'name' => 'db_user'
                    ],
                    [
                        'type' => 'password',
                        'label' => 'Database Password',
                        'name' => 'db_password'
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Table Prefix',
                        'name' => 'db_prefix',
                        'desc' => 'e.g., ps_'
                    ],
                    // API fields
                    [
                        'type' => 'text',
                        'label' => 'API URL',
                        'name' => 'api_url',
                        'desc' => 'e.g., https://supplier.com/api'
                    ],
                    [
                        'type' => 'text',
                        'label' => 'API Key',
                        'name' => 'api_key'
                    ],
                    // Sync settings
                    [
                        'type' => 'select',
                        'label' => 'Target Shops',
                        'name' => 'target_shops',
                        'multiple' => true,
                        'options' => [
                            'query' => Shop::getShops(true, null, true),
                            'id' => 'id_shop',
                            'name' => 'name'
                        ],
                        'desc' => 'Select which shops to sync to'
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Auto Sync',
                        'name' => 'auto_sync',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1],
                            ['id' => 'active_off', 'value' => 0]
                        ]
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Sync Interval (minutes)',
                        'name' => 'sync_interval',
                        'suffix' => 'minutes',
                        'value' => 15
                    ],
                    [
                        'type' => 'switch',
                        'label' => 'Active',
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => 1],
                            ['id' => 'active_off', 'value' => 0]
                        ]
                    ]
                ],
                'submit' => [
                    'title' => 'Save Supplier',
                    'name' => 'save_supplier'
                ],
                'buttons' => [
                    [
                        'title' => 'Test Connection',
                        'name' => 'test_supplier',
                        'type' => 'submit',
                        'class' => 'btn btn-info',
                        'icon' => 'process-icon-refresh'
                    ],
                    [
                        'title' => 'Sync Now',
                        'name' => 'sync_now',
                        'type' => 'submit',
                        'class' => 'btn btn-success',
                        'icon' => 'process-icon-cogs'
                    ]
                ]
            ]
        ];
        
        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = AdminController::$currentIndex;
        
        return $helper->generateForm([$fields]);
    }
    
    private function processSaveSupplier()
    {
        $data = [
            'name' => Tools::getValue('supplier_name'),
            'connection_type' => Tools::getValue('connection_type'),
            'db_host' => Tools::getValue('db_host'),
            'db_name' => Tools::getValue('db_name'),
            'db_user' => Tools::getValue('db_user'),
            'db_password' => Tools::getValue('db_password'),
            'db_prefix' => Tools::getValue('db_prefix'),
            'api_url' => Tools::getValue('api_url'),
            'api_key' => Tools::getValue('api_key'),
            'target_shops' => json_encode(Tools::getValue('target_shops', [])),
            'auto_sync' => Tools::getValue('auto_sync'),
            'sync_interval' => Tools::getValue('sync_interval'),
            'active' => Tools::getValue('active'),
            'date_upd' => date('Y-m-d H:i:s')
        ];
        
        if (Tools::getValue('id_supplier')) {
            // Update
            Db::getInstance()->update(
                'mpstocksync_suppliers',
                $data,
                'id_supplier = ' . (int)Tools::getValue('id_supplier')
            );
        } else {
            // Insert
            $data['date_add'] = date('Y-m-d H:i:s');
            Db::getInstance()->insert('mpstocksync_suppliers', $data);
        }
        
        $this->confirmations[] = 'Supplier saved successfully';
    }
    
    private function getSuppliers()
    {
        return Db::getInstance()->executeS('
            SELECT * FROM `'._DB_PREFIX_.'mpstocksync_suppliers`
            ORDER BY active DESC, name ASC
        ');
    }
}
