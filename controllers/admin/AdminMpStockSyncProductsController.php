<?php
class AdminMpStockSyncProductsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrap = true;
        $this->table = 'mpstocksync_mapping';  // JAVÍTVA: mpstocksync_mapping
        $this->className = 'MpStockSyncMapping'; // Model class
        $this->identifier = 'id_mapping';
        $this->lang = false;
        
        // Lista oszlopok
        $this->fields_list = [
            'id_mapping' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'id_product' => [
                'title' => $this->l('Product ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'api_name' => [  // JAVÍTVA: marketplace_name → api_name
                'title' => $this->l('Marketplace'),
                'align' => 'center',
                'type' => 'select',
                'list' => ['emag', 'trendyol'],
                'filter_key' => 'a!api_name'
            ],
            'external_id' => [  // JAVÍTVA: marketplace_product_id → external_id
                'title' => $this->l('Marketplace Product ID'),
                'align' => 'center'
            ],
            'sync_stock' => [
                'title' => $this->l('Sync Stock'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'sync_stock'
            ],
            'last_sync' => [
                'title' => $this->l('Last Sync'),
                'type' => 'datetime',
                'align' => 'center'
            ],
            'active' => [
                'title' => $this->l('Active'),
                'align' => 'center',
                'type' => 'bool',
                'active' => 'active'
            ]
        ];
        
        $this->actions = ['edit', 'delete'];
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            ],
            'enableSelection' => [
                'text' => $this->l('Enable selection'),
                'icon' => 'icon-power-off text-success'
            ],
            'disableSelection' => [
                'text' => $this->l('Disable selection'),
                'icon' => 'icon-power-off text-danger'
            ]
        ];
        
        // Default sort order
        $this->_defaultOrderBy = 'id_mapping';
        $this->_defaultOrderWay = 'DESC';
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
    
    public function renderList()
    {
        // Ha nincs adat, mutassunk üzenetet
        $total = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'mpstocksync_mapping`');
        
        if ($total == 0) {
            $this->informations[] = $this->l('No product mappings found. Click "Add new" to create your first mapping.');
        }
        
        return parent::renderList();
    }
    
    /**
     * Bulk action: Enable selected mappings
     */
    public function processBulkEnableSelection()
    {
        if ($this->access('edit')) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                $ids = array_map('intval', $this->boxes);
                Db::getInstance()->execute('
                    UPDATE `'._DB_PREFIX_.'mpstocksync_mapping`
                    SET active = 1
                    WHERE id_mapping IN ('.implode(',', $ids).')
                ');
                $this->confirmations[] = sprintf($this->l('%d mapping(s) enabled successfully'), count($ids));
            }
        }
    }
    
    /**
     * Bulk action: Disable selected mappings
     */
    public function processBulkDisableSelection()
    {
        if ($this->access('edit')) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                $ids = array_map('intval', $this->boxes);
                Db::getInstance()->execute('
                    UPDATE `'._DB_PREFIX_.'mpstocksync_mapping`
                    SET active = 0
                    WHERE id_mapping IN ('.implode(',', $ids).')
                ');
                $this->confirmations[] = sprintf($this->l('%d mapping(s) disabled successfully'), count($ids));
            }
        }
    }
}
