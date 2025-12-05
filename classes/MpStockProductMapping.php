<?php

class MpStockProductMapping extends ObjectModel
{
    public $id_mapping;
    public $id_product;
    public $marketplace_product_id;
    public $marketplace_name;
    public $last_sync;
    public $date_add;
    public $date_upd;
    
    public static $definition = array(
        'table' => 'mp_stock_product_mapping',
        'primary' => 'id_mapping',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'marketplace_product_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'marketplace_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'last_sync' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
    
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
