<?php

class AdminMpStockSyncProductMappingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mp_stock_product_mapping';
        $this->className = 'MpStockProductMapping';
        $this->identifier = 'id_mapping';
        $this->lang = false;
        $this->list_no_link = true;
        
        parent::__construct();
        
        // Alap Lista oszlopok definiálása
        $this->fields_list = [
            'id_mapping' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 30
            ],
            'id_product' => [
                'title' => $this->l('Product ID'),
                'align' => 'center',
                'width' => 50
            ],
            'marketplace_product_id' => [
                'title' => $this->l('Marketplace Product ID'),
                'align' => 'center',
                'width' => 100
            ],
            'marketplace_name' => [
                'title' => $this->l('Marketplace'),
                'align' => 'center',
                'width' => 100
            ],
            'last_sync' => [
                'title' => $this->l('Last Sync'),
                'align' => 'center',
                'type' => 'datetime',
                'width' => 150
            ]
        ];
        
        $this->actions = ['edit', 'delete'];
    }

    public function initContent()
    {
        parent::initContent();
        
        // Ellenőrizzük, hogy van-e API beállítva
        $api_key = Configuration::get('MP_STOCK_API_KEY');
        $api_secret = Configuration::get('MP_STOCK_API_SECRET');
        
        if (empty($api_key) || empty($api_secret)) {
            // API nincs beállítva - mutassunk információs üzenetet
            $this->warnings[] = $this->l('API settings are not configured. Please go to API Settings tab and configure your API credentials first.');
            
            // Mutassunk egy üres listát vagy csak az üzenetet
            $this->content = $this->renderList();
            $this->context->smarty->assign('content', $this->content);
            $this->setTemplate('product_mapping.tpl');
            return;
        }
        
        // Ha van API beállítás, betöltjük a termék leképezéseket
        $this->content = $this->renderList();
        $this->context->smarty->assign('content', $this->content);
        $this->setTemplate('product_mapping.tpl');
    }

    public function renderList()
    {
        // API beállítások ellenőrzése
        $api_key = Configuration::get('MP_STOCK_API_KEY');
        $api_secret = Configuration::get('MP_STOCK_API_SECRET');
        
        if (empty($api_key) || empty($api_secret)) {
            // API nincs beállítva - mutatunk egy üres listát információs üzenettel
            $this->displayWarning($this->l('API settings are not configured. Please configure API credentials to see product mappings.'));
        }
        
        // Próbáljuk meg lekérni a termék leképezéseket
        try {
            // Itt jönne a tényleges lekérdezés a marketplace API-ról
            // Példa:
            // $mappings = $this->module->getMarketplaceProductMappings();
            
            // Jelenleg egy üres listát mutatunk
            $this->_select = null;
            $this->_where = null;
            $this->_orderBy = 'id_mapping';
            $this->_orderWay = 'ASC';
            
            return parent::renderList();
            
        } catch (Exception $e) {
            // Ha hiba történt a lekérdezés során
            $this->errors[] = $this->l('Error loading product mappings: ') . $e->getMessage();
            return parent::renderList();
        }
    }

    public function renderForm()
    {
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
                    'label' => $this->l('Marketplace Product ID'),
                    'name' => 'marketplace_product_id',
                    'required' => true,
                    'col' => 4
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Marketplace'),
                    'name' => 'marketplace_name',
                    'required' => true,
                    'options' => [
                        'query' => [
                            ['id' => 'shopify', 'name' => 'Shopify'],
                            ['id' => 'amazon', 'name' => 'Amazon'],
                            ['id' => 'ebay', 'name' => 'eBay'],
                            ['id' => 'woocommerce', 'name' => 'WooCommerce']
                        ],
                        'id' => 'id',
                        'name' => 'name'
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
        parent::postProcess();
        
        // Egyéni mentési logika ide kerülhet
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            // Validáció és mentés
            $product_id = Tools::getValue('id_product');
            $marketplace_id = Tools::getValue('marketplace_product_id');
            $marketplace_name = Tools::getValue('marketplace_name');
            
            if (!empty($product_id) && !empty($marketplace_id) && !empty($marketplace_name)) {
                // Itt történhet az API mentés vagy adatbázis mentés
                $this->confirmations[] = $this->l('Product mapping saved successfully');
            } else {
                $this->errors[] = $this->l('Please fill all fields');
            }
        }
    }
    
    /**
     * Display warning message when API is not configured
     */
    protected function displayWarning($message)
    {
        $this->warnings[] = $message;
        $this->context->smarty->assign('warnings', $this->warnings);
    }
}
