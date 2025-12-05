<?php
class MpStockSyncMapping extends ObjectModel
{
    public $id_mapping;
    public $id_product;
    public $id_product_attribute;
    public $api_name;
    public $external_id;
    public $external_reference;
    public $sync_stock;
    public $sync_price;
    public $last_sync;
    public $sync_count;
    public $active;
    public $date_add;
    public $date_upd;
    
    public static $definition = [
        'table' => 'mpstocksync_mapping',
        'primary' => 'id_mapping',
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false],
            'api_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 20],
            'external_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 100],
            'external_reference' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'size' => 100],
            'sync_stock' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'sync_price' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'last_sync' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'sync_count' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
    
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }
    
    public function add($auto_date = true, $null_values = false)
    {
        $this->date_add = date('Y-m-d H:i:s');
        $this->date_upd = date('Y-m-d H:i:s');
        return parent::add($auto_date, $null_values);
    }
    
    public function update($null_values = false)
    {
        $this->date_upd = date('Y-m-d H:i:s');
        return parent::update($null_values);
    }
}
