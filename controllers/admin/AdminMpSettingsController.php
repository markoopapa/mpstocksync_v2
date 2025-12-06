<?php
/**
 * Központi beállítások: API kulcsok és URL-ek megadása
 */
class AdminMpSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'Configuration';
        $this->table = 'configuration';
        parent::__construct();

        // 1. Szekció: Supplier Connection (Honnan jön az áru?)
        $this->fields_options = [
            'supplier' => [
                'title' =>    $this->l('1. Supplier Source Settings (PrestaShop Webservice)'),
                'icon' =>     'icon-truck',
                'fields' =>   [
                    'MP_SUPPLIER_URL' => [
                        'title' => $this->l('Supplier Shop URL'),
                        'desc'  => $this->l('E.g., https://suppliershop.com/'),
                        'type'  => 'text',
                        'size'  => 50
                    ],
                    'MP_SUPPLIER_KEY' => [
                        'title' => $this->l('Webservice API Key'),
                        'desc'  => $this->l('API key generated in Supplier PrestaShop BO'),
                        'type'  => 'text',
                        'size'  => 50
                    ],
                ],
                'submit' => ['title' => $this->l('Save Supplier Settings')]
            ],
            // 2. Szekció: Other Shop Connection (Hova küldjük még?)
            'othershop' => [
                'title' =>    $this->l('2. Other Shop Settings (Target PrestaShop)'),
                'icon' =>     'icon-building',
                'fields' =>   [
                    'MP_OTHER_URL' => [
                        'title' => $this->l('Target Shop URL'),
                        'type'  => 'text',
                    ],
                    'MP_OTHER_KEY' => [
                        'title' => $this->l('Target Shop API Key'),
                        'type'  => 'text',
                    ],
                ],
                'submit' => ['title' => $this->l('Save Other Shop Settings')]
            ],
            // 3. Szekció: Marketplaces (eMAG & Trendyol)
            'marketplaces' => [
                'title' =>    $this->l('3. Marketplace API Settings'),
                'icon' =>     'icon-cloud-upload',
                'fields' =>   [
                    // eMAG
                    'MP_EMAG_USER' => [
                        'title' => $this->l('eMAG Username'),
                        'type'  => 'text',
                    ],
                    'MP_EMAG_PASS' => [
                        'title' => $this->l('eMAG Password'),
                        'type'  => 'text',
                        'inputType' => 'password'
                    ],
                    'MP_EMAG_COMPANY_ID' => [
                        'title' => $this->l('eMAG Company Code'),
                        'type'  => 'text',
                    ],
                    // Trendyol
                    'MP_TRENDYOL_ID' => [
                        'title' => $this->l('Trendyol Supplier ID'),
                        'type'  => 'text',
                    ],
                    'MP_TRENDYOL_KEY' => [
                        'title' => $this->l('Trendyol API Key'),
                        'type'  => 'text',
                    ],
                    'MP_TRENDYOL_SECRET' => [
                        'title' => $this->l('Trendyol API Secret'),
                        'type'  => 'text',
                        'inputType' => 'password'
                    ],
                ],
                'submit' => ['title' => $this->l('Save Marketplace Settings')]
            ]
        ];
    }
}
