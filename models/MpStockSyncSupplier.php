<?php
class MpStockSyncSupplier extends ObjectModel
{
    public $id_supplier;
    public $name;
    public $connection_type;
    public $db_host;
    public $db_name;
    public $db_user;
    public $db_password;
    public $db_prefix;
    public $api_url;
    public $api_key;
    public $target_shops;
    public $auto_sync;
    public $sync_interval;
    public $last_sync;
    public $active;
    public $date_add;
    public $date_upd;
    
    public static $definition = [
        'table' => 'mpstocksync_suppliers',
        'primary' => 'id_supplier',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 100],
            'connection_type' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 50],
            'db_host' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'db_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 100],
            'db_user' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 100],
            'db_password' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'db_prefix' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 50],
            'api_url' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'api_key' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'target_shops' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'auto_sync' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'sync_interval' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'last_sync' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
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
