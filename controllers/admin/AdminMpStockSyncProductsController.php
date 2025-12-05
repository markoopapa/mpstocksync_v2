<?php
class AdminMpStockSyncProductsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstocksync_mapping';
        $this->identifier = 'id_mapping';
        $this->className = 'MpStockSyncMapping';
        $this->lang = false;
        
        parent::__construct();
        
        $this->fields_list = [
            'id_mapping' => [
                'title' => 'ID',
                'width' => 50,
                'align' => 'center'
            ],
            'api_name' => [
                'title' => 'Platform',
                'width' => 100,
                'callback' => 'renderPlatform'
            ],
            'external_id' => [
                'title' => 'External ID',
                'width' => 150
            ],
            'reference' => [
                'title' => 'SKU',
                'width' => 100,
                'filter_key' => 'p!reference'
            ],
            'name' => [
                'title' => 'Product Name',
                'width' => 200,
                'filter_key' => 'pl!name'
            ],
            'last_sync' => [
                'title' => 'Last Sync',
                'width' => 120,
                'type' => 'datetime'
            ],
            'sync_count' => [
                'title' => 'Sync Count',
                'width' => 80,
                'align' => 'center'
            ],
            'active' => [
                'title' => 'Active',
                'width' => 80,
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool'
            ]
        ];
        
        $this->bulk_actions = [
            'enable' => [
                'text' => 'Enable',
                'icon' => 'icon-check',
                'confirm' => 'Enable selected items?'
            ],
            'disable' => [
                'text' => 'Disable',
                'icon' => 'icon-remove',
                'confirm' => 'Disable selected items?'
            ],
            'delete' => [
                'text' => 'Delete',
                'icon' => 'icon-trash',
                'confirm' => 'Delete selected items?'
            ]
        ];
        
        $this->_select = 'p.reference, pl.name';
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product` p ON p.id_product = a.id_product
                       LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON pl.id_product = p.id_product
                           AND pl.id_lang = '.(int)$this->context->language->id;
        $this->_where = 'AND a.id_product_attribute = 0';
        $this->_defaultOrderBy = 'a.date_upd';
        $this->_defaultOrderWay = 'DESC';
    }
    
    public function initToolbar()
    {
        parent::initToolbar();
        
        $this->page_header_toolbar_btn['new_mapping'] = [
            'href' => self::$currentIndex . '&addmapping&token=' . $this->token,
            'desc' => 'Add New Mapping',
            'icon' => 'process-icon-new'
        ];
        
        $this->page_header_toolbar_btn['import_csv'] = [
            'href' => self::$currentIndex . '&importcsv&token=' . $this->token,
            'desc' => 'Import CSV',
            'icon' => 'process-icon-upload'
        ];
        
        $this->page_header_toolbar_btn['export_csv'] = [
            'href' => self::$currentIndex . '&exportcsv&token=' . $this->token,
            'desc' => 'Export CSV',
            'icon' => 'process-icon-download'
        ];
    }
    
    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        
        // Add custom filter for platform
        $this->_filter .= ' AND a.api_name LIKE "%'.pSQL(Tools::getValue('mpstocksync_mappingFilter_api_name')).'%"';
        
        return parent::renderList();
    }
    
    public function renderPlatform($value, $row)
    {
        if ($value == 'emag') {
            return '<span class="label" style="background:#0056b3">eMAG</span>';
        } elseif ($value == 'trendyol') {
            return '<span class="label" style="background:#ff6b00">Trendyol</span>';
        }
        return $value;
    }
    
    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => 'Product Mapping',
                'icon' => 'icon-link'
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => 'Platform',
                    'name' => 'api_name',
                    'required' => true,
                    'options' => [
                        'query' => [
                            ['id' => 'emag', 'name' => 'eMAG'],
                            ['id' => 'trendyol', 'name' => 'Trendyol']
                        ],
                        'id' => 'id',
                        'name' => 'name'
                    ]
                ],
                [
                    'type' => 'select',
                    'label' => 'Product',
                    'name' => 'id_product',
                    'required' => true,
                    'options' => [
                        'query' => $this->getProductsList(),
                        'id' => 'id_product',
                        'name' => 'name'
                    ]
                ],
                [
                    'type' => 'text',
                    'label' => 'External ID',
                    'name' => 'external_id',
                    'required' => true,
                    'desc' => 'For eMAG: SKU | For Trendyol: Barcode (EAN13)'
                ],
                [
                    'type' => 'text',
                    'label' => 'External Reference',
                    'name' => 'external_reference',
                    'desc' => 'Optional: External SKU or ID'
                ],
                [
                    'type' => 'switch',
                    'label' => 'Sync Stock',
                    'name' => 'sync_stock',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1],
                        ['id' => 'active_off', 'value' => 0]
                    ]
                ],
                [
                    'type' => 'switch',
                    'label' => 'Sync Price',
                    'name' => 'sync_price',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1],
                        ['id' => 'active_off', 'value' => 0]
                    ]
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
                'title' => 'Save',
                'class' => 'btn btn-default pull-right'
            ]
        ];
        
        return parent::renderForm();
    }
    
    private function getProductsList()
    {
        $sql = 'SELECT p.id_product, pl.name, p.reference, p.ean13
                FROM `'._DB_PREFIX_.'product` p
                INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON pl.id_product = p.id_product
                    AND pl.id_lang = '.(int)$this->context->language->id.'
                WHERE p.active = 1
                ORDER BY pl.name ASC';
        
        $products = Db::getInstance()->executeS($sql);
        
        $list = [];
        foreach ($products as $product) {
            $name = $product['name'] . ' (SKU: ' . $product['reference'] . 
                   ($product['ean13'] ? ', EAN: ' . $product['ean13'] : '') . ')';
            $list[] = [
                'id_product' => $product['id_product'],
                'name' => $name
            ];
        }
        
        return $list;
    }
    
    public function processAdd()
    {
        $id_product = (int)Tools::getValue('id_product');
        $api_name = Tools::getValue('api_name');
        $external_id = Tools::getValue('external_id');
        
        // Check if mapping already exists
        $exists = Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `'._DB_PREFIX_.'mpstocksync_mapping`
            WHERE id_product = '.(int)$id_product.'
            AND api_name = "'.pSQL($api_name).'"
        ');
        
        if ($exists) {
            $this->errors[] = 'Mapping already exists for this product and platform';
            return false;
        }
        
        return parent::processAdd();
    }
    
    public function ajaxProcessSearchProducts()
    {
        $query = Tools::getValue('q');
        
        $sql = 'SELECT p.id_product, pl.name, p.reference, p.ean13
                FROM `'._DB_PREFIX_.'product` p
                INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON pl.id_product = p.id_product
                    AND pl.id_lang = '.(int)$this->context->language->id.'
                WHERE p.active = 1
                AND (pl.name LIKE "%'.pSQL($query).'%" 
                     OR p.reference LIKE "%'.pSQL($query).'%"
                     OR p.ean13 LIKE "%'.pSQL($query).'%")
                ORDER BY pl.name ASC
                LIMIT 20';
        
        $products = Db::getInstance()->executeS($sql);
        
        die(json_encode($products));
    }
}
