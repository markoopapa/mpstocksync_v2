<?php
class MpStockSyncLog extends ObjectModel
{
    public $id_log;
    public $api_name;
    public $id_product;
    public $id_product_attribute;
    public $action;
    public $old_value;
    public $new_value;
    public $status;
    public $error_message;
    public $response_data;
    public $date_add;
    
    public static $definition = [
        'table' => 'mpstocksync_log',
        'primary' => 'id_log',
        'fields' => [
            'api_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 20],
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_product_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'action' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 50],
            'old_value' => ['type' => self::TYPE_STRING],
            'new_value' => ['type' => self::TYPE_STRING],
            'status' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'error_message' => ['type' => self::TYPE_STRING],
            'response_data' => ['type' => self::TYPE_STRING],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
    
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }
}
