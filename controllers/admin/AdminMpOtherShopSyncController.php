<?php
/**
 * Controller: Supplier -> Másik Shop (PrestaShop) szinkron kezelése
 */
class AdminMpOtherShopSyncController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'product'; 
        $this->className = 'Product';
        $this->identifier = 'id_product';
        $this->lang = true;
        
        parent::__construct();

        // JOIN a 'mp_othershop_map' táblához!
        $this->_select = '
            mp.remote_product_id, 
            mp.sync_enabled, 
            mp.last_synced,
            sa.quantity as stock_qty
        ';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'mp_othershop_map` mp ON (a.`id_product` = mp.`id_product`)
            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (a.`id_product` = sa.`id_product` AND sa.`id_product_attribute` = 0)
        ';

        $this->fields_list = [
            'id_product' => ['title' => 'ID', 'width' => 30, 'align' => 'center'],
            'image' => ['title' => 'Img', 'align' => 'center', 'image' => 'p', 'width' => 40, 'search' => false],
            'name' => ['title' => 'Name', 'width' => 'auto', 'filter_key' => 'b!name'],
            'stock_qty' => ['title' => 'My Stock', 'width' => 50, 'align' => 'center', 'search' => false],
            
            'remote_product_id' => [
                'title' => 'Remote ID/SKU',
                'align' => 'left',
                'width' => 80,
                'search' => true,
            ],
            'sync_enabled' => [
                'title' => 'Send to Other Shop?',
                'align' => 'center',
                'active' => 'status', // Ez a kulcs a toggle gombhoz
                'type' => 'bool',
                'width' => 40,
                'search' => true,
            ],
            'last_synced' => ['title' => 'Last Sent', 'align' => 'right', 'type' => 'datetime', 'width' => 100, 'search' => false]
        ];
    }

    // A státusz váltás logikája (SYNC / NO SYNC)
    public function processStatus()
    {
        if (Validate::isLoadedObject($object = $this->loadObject())) {
            $id_product = (int)$object->id;
            
            // Ellenőrizzük a 'mp_othershop_map' táblát
            $exists = Db::getInstance()->getValue('SELECT id_mp_other_map FROM '._DB_PREFIX_.'mp_othershop_map WHERE id_product = ' . $id_product);
            
            if ($exists) {
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'mp_othershop_map SET sync_enabled = NOT sync_enabled WHERE id_product = ' . $id_product);
            } else {
                Db::getInstance()->insert('mp_othershop_map', [
                    'id_product' => $id_product,
                    'remote_product_id' => $object->reference, // Defaultnak a reference-t használjuk
                    'sync_enabled' => 1
                ]);
            }
        }
        Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
    }
}
