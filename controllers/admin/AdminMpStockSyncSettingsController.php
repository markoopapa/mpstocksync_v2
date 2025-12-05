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
        
        // JAVÍTVA: Hiányzó változók hozzáadása
        $this->context->smarty->assign([
            'settings_form' => $this->renderSettingsForm(),
            'module_dir' => Module::getInstanceByName('mpstocksync')->getLocalPath(),
            'ps_version' => _PS_VERSION_,  // PrestaShop verzió
            'php_version' => phpversion(),  // PHP verzió
            'currentIndex' => $this->context->link->getAdminLink('AdminMpStockSyncSettings'),
            'token' => Tools::getAdminTokenLite('AdminMpStockSyncSettings')
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
                        ]
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
        
        // Jelenlegi értékek
        $helper->fields_value = [
            'MP_DEBUG_MODE' => Configuration::get('MP_DEBUG_MODE', 0)
        ];
        
        return $helper->generateForm([$fields_form]);
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('submit_mpstocksync_settings')) {
            Configuration::updateValue('MP_DEBUG_MODE', Tools::getValue('MP_DEBUG_MODE'));
            $this->confirmations[] = $this->l('Settings saved successfully');
        }
        
        parent::postProcess();
    }
}
