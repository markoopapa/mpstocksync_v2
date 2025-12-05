<?php
class AdminMpStockSyncSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }
    
    public function initContent()
    {
        parent::initContent();
        
        // Beállítások form elkészítése
        $settings_form = $this->renderSettingsForm();
        
        $this->context->smarty->assign([
            'settings_form' => $settings_form,  // FONTOS: átadjuk a template-nek
            'module_dir' => Module::getInstanceByName('mpstocksync')->getLocalPath()
        ]);
        
        $this->setTemplate('settings/settings.tpl');
    }
    
    /**
     * Beállítások form generálása
     */
    private function renderSettingsForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('General Settings'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Debug Mode'),
                        'name' => 'MP_DEBUG_MODE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'debug_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'debug_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ],
                        'hint' => $this->l('Enable detailed logging for debugging')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Default Stock Buffer'),
                        'name' => 'MP_DEFAULT_STOCK_BUFFER',
                        'col' => 3,
                        'suffix' => $this->l('pieces'),
                        'hint' => $this->l('Default safety stock buffer for all products')
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Sync Priority'),
                        'name' => 'MP_SYNC_PRIORITY',
                        'options' => [
                            'query' => [
                                ['id' => 'low', 'name' => $this->l('Low (background)')],
                                ['id' => 'normal', 'name' => $this->l('Normal')],
                                ['id' => 'high', 'name' => $this->l('High (immediate)')]
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'col' => 4
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Send Email Notifications'),
                        'name' => 'MP_EMAIL_NOTIFICATIONS',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'email_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'email_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ]
                        ]
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Notification Email'),
                        'name' => 'MP_NOTIFICATION_EMAIL',
                        'col' => 4,
                        'placeholder' => 'admin@example.com'
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Log Retention Period'),
                        'name' => 'MP_LOG_RETENTION',
                        'options' => [
                            'query' => [
                                ['id' => '7', 'name' => $this->l('7 days')],
                                ['id' => '30', 'name' => $this->l('30 days')],
                                ['id' => '90', 'name' => $this->l('90 days')],
                                ['id' => '365', 'name' => $this->l('1 year')],
                                ['id' => '0', 'name' => $this->l('Keep forever')]
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ],
                        'col' => 4,
                        'hint' => $this->l('How long to keep sync logs')
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];
        
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this->module;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_mpstocksync_settings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminMpStockSyncSettings', false);
        $helper->token = Tools::getAdminTokenLite('AdminMpStockSyncSettings');
        
        // Jelenlegi értékek betöltése
        $helper->fields_value = [
            'MP_DEBUG_MODE' => Configuration::get('MP_DEBUG_MODE', 0),
            'MP_DEFAULT_STOCK_BUFFER' => Configuration::get('MP_DEFAULT_STOCK_BUFFER', 0),
            'MP_SYNC_PRIORITY' => Configuration::get('MP_SYNC_PRIORITY', 'normal'),
            'MP_EMAIL_NOTIFICATIONS' => Configuration::get('MP_EMAIL_NOTIFICATIONS', 0),
            'MP_NOTIFICATION_EMAIL' => Configuration::get('MP_NOTIFICATION_EMAIL', ''),
            'MP_LOG_RETENTION' => Configuration::get('MP_LOG_RETENTION', '30')
        ];
        
        return $helper->generateForm([$fields_form]);
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('submit_mpstocksync_settings')) {
            // Beállítások mentése
            Configuration::updateValue('MP_DEBUG_MODE', Tools::getValue('MP_DEBUG_MODE'));
            Configuration::updateValue('MP_DEFAULT_STOCK_BUFFER', Tools::getValue('MP_DEFAULT_STOCK_BUFFER'));
            Configuration::updateValue('MP_SYNC_PRIORITY', Tools::getValue('MP_SYNC_PRIORITY'));
            Configuration::updateValue('MP_EMAIL_NOTIFICATIONS', Tools::getValue('MP_EMAIL_NOTIFICATIONS'));
            Configuration::updateValue('MP_NOTIFICATION_EMAIL', Tools::getValue('MP_NOTIFICATION_EMAIL'));
            Configuration::updateValue('MP_LOG_RETENTION', Tools::getValue('MP_LOG_RETENTION'));
            
            $this->confirmations[] = $this->l('Settings saved successfully');
            
            // Régi logok törlése, ha szükséges
            $this->cleanupOldLogs();
        }
        
        parent::postProcess();
    }
    
    /**
     * Régi logok törlése
     */
    private function cleanupOldLogs()
    {
        $retention_days = (int)Configuration::get('MP_LOG_RETENTION', 30);
        
        if ($retention_days > 0) {
            $cutoff_date = date('Y-m-d H:i:s', strtotime('-' . $retention_days . ' days'));
            
            Db::getInstance()->execute('
                DELETE FROM `'._DB_PREFIX_.'mpstocksync_log`
                WHERE date_add < "' . pSQL($cutoff_date) . '"
            ');
            
            Db::getInstance()->execute('
                DELETE FROM `'._DB_PREFIX_.'mpstocksync_recent_log`
                WHERE date_add < "' . pSQL($cutoff_date) . '"
            ');
        }
    }
}
