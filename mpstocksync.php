<?php
/**
 * Marketplace Stock Sync
 * 
 * @author markoopapa
 * @version 2.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class MpStockSync extends Module
{
    public function __construct()
    {
        $this->name = 'mpstocksync';
        $this->tab = 'administration';
        $this->version = '2.0.0';
        $this->author = 'markoopapa';
        $this->need_instance = 0;
        $this->bootstrap = true;
        
        parent::__construct();
        
        $this->displayName = $this->l('Marketplace Stock Sync');
        $this->description = $this->l('Sync stock between PrestaShop and marketplaces');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
    }
    
    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        
        // Adatbázis táblák létrehozása
        if (!$this->createTables()) {
            return false;
        }
        
        // Tab-ok létrehozása
        if (!$this->createTabs()) {
            return false;
        }
        
        // Alap konfiguráció
        Configuration::updateValue('MP_STOCK_API_KEY', '');
        Configuration::updateValue('MP_STOCK_API_SECRET', '');
        
        return true;
    }
    
    private function createTables()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mp_stock_product_mapping` (
            `id_mapping` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` int(11) UNSIGNED NOT NULL,
            `marketplace_product_id` varchar(255) NOT NULL,
            `marketplace_name` varchar(50) NOT NULL,
            `last_sync` datetime DEFAULT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_mapping`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
        
        return Db::getInstance()->execute($sql);
    }
    
    private function createTabs()
    {
        // Fő admin tab (Stock Sync)
        $parentTab = new Tab();
        $parentTab->class_name = 'AdminMpStockSync';
        $parentTab->module = $this->name;
        $parentTab->id_parent = (int)Tab::getIdFromClassName('AdminParentPreferences'); // Beállítások menü alá
        
        $languages = Language::getLanguages();
        foreach ($languages as $lang) {
            $parentTab->name[$lang['id_lang']] = $this->l('Stock Sync');
        }
        
        if (!$parentTab->add()) {
            return false;
        }
        
        // Almenü tab-ok
        $subTabs = [
            ['AdminMpStockSyncApi', 'API Settings'],
            ['AdminMpStockSyncProductMapping', 'Product Mapping'],
            ['AdminMpStockSyncManualSync', 'Manual Sync'],
            ['AdminMpStockSyncLogs', 'Logs']
        ];
        
        foreach ($subTabs as $tab) {
            $newTab = new Tab();
            $newTab->class_name = $tab[0];
            $newTab->module = $this->name;
            $newTab->id_parent = $parentTab->id;
            
            foreach ($languages as $lang) {
                $newTab->name[$lang['id_lang']] = $this->l($tab[1]);
            }
            
            if (!$newTab->add()) {
                // Ha nem sikerül, töröljük az eddigieket
                $this->deleteTabs();
                return false;
            }
        }
        
        return true;
    }
    
    private function deleteTabs()
    {
        $tabs = [
            'AdminMpStockSync',
            'AdminMpStockSyncApi',
            'AdminMpStockSyncProductMapping',
            'AdminMpStockSyncManualSync',
            'AdminMpStockSyncLogs'
        ];
        
        foreach ($tabs as $className) {
            $id_tab = Tab::getIdFromClassName($className);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
    }
    
    public function uninstall()
    {
        // Tab-ok törlése
        $this->deleteTabs();
        
        // Konfigurációk törlése
        Configuration::deleteByName('MP_STOCK_API_KEY');
        Configuration::deleteByName('MP_STOCK_API_SECRET');
        
        return parent::uninstall();
    }
    
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMpStockSyncApi'));
    }
}
