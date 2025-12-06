<?php
/**
 * Controller a Supplier -> Saját Shop mappinghez
 */
class AdminMpSupplierSyncController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'product'; // Alapvetően a termékeket listázzuk
        $this->className = 'Product';
        $this->identifier = 'id_product';
        $this->lang = true;
        
        parent::__construct();

        // SQL JOIN, hogy lássuk a saját mapping táblánk adatait is (supplier_sku, sync_enabled)
        $this->_select = '
            mp.supplier_sku, 
            mp.sync_enabled, 
            mp.last_synced,
            sa.quantity as stock_qty
        ';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'mp_supplier_map` mp ON (a.`id_product` = mp.`id_product`)
            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON (a.`id_product` = sa.`id_product` AND sa.`id_product_attribute` = 0)
        ';

        // Mezők definíciója a listában
        $this->fields_list = [
            'id_product' => [
                'title' => 'ID',
                'align' => 'center',
                'width' => 30
            ],
            'image' => [
                'title' => 'Image',
                'align' => 'center',
                'image' => 'p',
                'width' => 50,
                'search' => false, // Kép keresést kikapcsoljuk
            ],
            'name' => [
                'title' => 'Product Name',
                'width' => 'auto',
                'filter_key' => 'b!name'
            ],
            'reference' => [
                'title' => 'Presta Reference',
                'align' => 'left',
                'width' => 100
            ],
            'stock_qty' => [
                'title' => 'Current Stock',
                'align' => 'center',
                'width' => 50,
                'search' => false,
                'havingFilter' => true
            ],
            'supplier_sku' => [
                'title' => 'Supplier SKU (Mapping)',
                'align' => 'left',
                'width' => 100,
                'search' => true,
                // Itt majd callback kell a szerkesztéshez, egyelőre csak kiírjuk
            ],
            'sync_enabled' => [
                'title' => 'Sync Active',
                'align' => 'center',
                'active' => 'status', // Ez csinálja a zöld pipát / piros X-et
                'type' => 'bool',
                'width' => 40,
                'orderby' => false,
                'search' => true,
            ],
            'last_synced' => [
                'title' => 'Last Synced',
                'align' => 'right',
                'type' => 'datetime',
                'width' => 100,
                'search' => false
            ]
        ];

        // Tömeges műveletek (Bulk actions) - ha egyszerre akarsz sokat bekapcsolni
        $this->bulk_actions = [
            'enableSelection' => [
                'text' => 'Enable Sync',
                'icon' => 'icon-power-off text-success'
            ],
            'disableSelection' => [
                'text' => 'Disable Sync',
                'icon' => 'icon-power-off text-danger'
            ]
        ];
    }

    /**
     * AJAX hívások kezelése (amikor rákattintasz a SYNC kapcsolóra)
     */
    public function initContent()
    {
        // Ha nem AJAX hívás, akkor renderelje a listát
        parent::initContent();
    }
    
    // Ez a függvény kezeli, amikor rákattintasz a pipára/X-re a listában
    public function processStatus()
    {
        if (Validate::isLoadedObject($object = $this->loadObject())) {
            // Itt kell majd frissíteni a custom táblánkat (mp_supplier_map)
            // Mivel a $object alapból a Product osztály, nekünk SQL-el kell belenyúlni a saját táblánkba
            
            $id_product = (int)$object->id;
            
            // Megnézzük, létezik-e már a bejegyzés
            $exists = Db::getInstance()->getValue('SELECT id_mp_map FROM '._DB_PREFIX_.'mp_supplier_map WHERE id_product = ' . $id_product);
            
            if ($exists) {
                // Ha létezik, negáljuk a státuszt (0->1, 1->0)
                Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'mp_supplier_map SET sync_enabled = NOT sync_enabled WHERE id_product = ' . $id_product);
            } else {
                // Ha nem létezik, létrehozzuk (és bekapcsoljuk, mert arra kattintottál)
                Db::getInstance()->insert('mp_supplier_map', [
                    'id_product' => $id_product,
                    'supplier_sku' => $object->reference, // Alapértelmezésnek beírjuk a referenciát
                    'sync_enabled' => 1,
                    'source_type' => 'supplier_main'
                ]);
            }
        }
        // Visszairányítás a listára
        Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
    }
}
