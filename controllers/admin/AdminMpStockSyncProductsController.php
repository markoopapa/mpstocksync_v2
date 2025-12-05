<?php
class AdminMpStockSyncProductsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrap = true;
        $this->table = 'mpstocksync_mapping';
        $this->className = 'MpStockSyncMapping';
        $this->identifier = 'id_mapping';
        $this->lang = false;
        
        // Lista oszlopok
        $this->fields_list = [
            'id_mapping' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => true,
                'search' => false
            ],
            'id_product' => [
                'title' => $this->l('Product ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => true,
                'search' => false
            ],
            'api_name' => [
                'title' => $this->l('Marketplace'),
                'align' => 'center',
                'type' => 'select',
                'list' => [
                    'emag' => 'eMAG',
                    'trendyol' => 'Trendyol'
                ],
                'filter_key' => 'api_name',
                'orderby' => true
            ],
            'external_id' => [
                'title' => $this->l('Marketplace Product ID'),
                'align' => 'center',
                'orderby' => true,
                'search' => true
            ],
            'sync_stock' => [
                'title' => $this->l('Sync Stock'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'sync_stock',
                'orderby' => true
            ],
            'last_sync' => [
                'title' => $this->l('Last Sync'),
                'type' => 'datetime',
                'align' => 'center',
                'orderby' => true
            ],
            'active' => [
                'title' => $this->l('Active'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'active',
                'orderby' => true
            ]
        ];
        
        $this->actions = ['edit', 'delete'];
        
        // Default sort order - JAVÍTVA: 'a.' prefix nélkül
        $this->_defaultOrderBy = 'id_mapping';
        $this->_defaultOrderWay = 'DESC';
        
        // Alias beállítása
        $this->_select = 'a.*';
    }
    
    public function initContent()
    {
        parent::initContent();
        
        // API beállítások ellenőrzése
        $emag_configured = Configuration::get('MP_EMAG_CLIENT_ID') && Configuration::get('MP_EMAG_CLIENT_SECRET');
        $trendyol_configured = Configuration::get('MP_TRENDYOL_API_KEY') && Configuration::get('MP_TRENDYOL_API_SECRET');
        
        if (!$emag_configured && !$trendyol_configured) {
            $this->warnings[] = $this->l('Please configure API settings first to use product mapping.');
        }
        
        $this->content = $this->renderList();
        $this->context->smarty->assign('content', $this->content);
    }
    
    /**
     * JAVÍTOTT: RenderList metódus - megakadályozzuk az automatikus alias-t
     */
    public function renderList()
    {
        // Ha nincs adat, mutassunk üzenetet
        $total = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'mpstocksync_mapping`');
        
        if ($total == 0) {
            $this->informations[] = $this->l('No product mappings found. Click "Add new" to create your first mapping.');
        }
        
        // Manuálisan beállítjuk a WHERE feltételt, hogy ne használjon alias-t
        $this->_where = '1';
        
        // Lekérdezés előkészítése - JAVÍTVA
        $this->prepareTable();
        
        return parent::renderList();
    }
    
    /**
     * Tábla előkészítése
     */
    private function prepareTable()
    {
        // Biztosítjuk, hogy a tábla neve helyesen legyen megadva
        $this->table = 'mpstocksync_mapping';
        
        // SQL alias beállítása
        $this->_select = '*';
        
        // WHERE feltétel
        $this->_where = '1';
        
        // GROUP BY törlése
        $this->_group = '';
        
        // JOIN-ok törlése
        $this->_join = '';
    }
    
    public function renderForm()
    {
        // Form mezők
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Product Mapping'),
                'icon' => 'icon-link'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Product ID'),
                    'name' => 'id_product',
                    'required' => true,
                    'col' => 4
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Product Attribute ID'),
                    'name' => 'id_product_attribute',
                    'col' => 4,
                    'hint' => $this->l('0 for product without attributes')
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Marketplace'),
                    'name' => 'api_name',
                    'required' => true,
                    'options' => [
                        'query' => [
                            ['id' => 'emag', 'name' => 'eMAG'],
                            ['id' => 'trendyol', 'name' => 'Trendyol']
                        ],
                        'id' => 'id',
                        'name' => 'name'
                    ],
                    'col' => 4
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Marketplace Product ID'),
                    'name' => 'external_id',
                    'required' => true,
                    'col' => 4
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Sync Stock'),
                    'name' => 'sync_stock',
                    'required' => false,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'sync_stock_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ],
                        [
                            'id' => 'sync_stock_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ]
                    ],
                    'col' => 4
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        ]
                    ],
                    'col' => 4
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];
        
        return parent::renderForm();
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $id_product = (int)Tools::getValue('id_product');
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            $api_name = Tools::getValue('api_name');
            $external_id = Tools::getValue('external_id');
            
            // Ellenőrzés, hogy létezik-e a termék
            if (!Product::existsInDatabase($id_product, 'product')) {
                $this->errors[] = $this->l('Product does not exist');
            }
            
            // Ellenőrzés, hogy létezik-e már a leképezés
            $exists = Db::getInstance()->getValue('
                SELECT COUNT(*) FROM `'._DB_PREFIX_.'mpstocksync_mapping`
                WHERE id_product = '.(int)$id_product.'
                AND id_product_attribute = '.(int)$id_product_attribute.'
                AND api_name = "'.pSQL($api_name).'"
            ');
            
            if ($exists) {
                $this->errors[] = $this->l('Mapping already exists for this product and marketplace');
            }
        }
        
        parent::postProcess();
    }
}
