<?php
/**
 * MP Stock Sync Pro - Stock Only Version
 *
 * @author    Markoopapa
 * @copyright 2025 Markoopapa
 * @license   AFL-3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Composer autoload betöltése
require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class MpStockSync extends Module
{
    public function __construct()
    {
        $this->name = 'mpstocksync';
        $this->tab = 'administration';
        $this->version = '2.0.0';
        $this->author = 'Markoopapa';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('MP Stock Sync Pro');
        $this->description = $this->l('Synchronize STOCK ONLY: Supplier -> Shops -> Marketplaces (eMAG, Trendyol).');
    }

    public function install()
    {
        return parent::install()
            && $this->installDb()
            && $this->installTabs();
    }

    public function uninstall()
    {
        return $this->uninstallTabs()
            && parent::uninstall();
        // Opcionális: $this->uninstallDb() - ha akarod törölni az adatokat eltávolításkor
    }

    /**
     * Adatbázis táblák létrehozása a MAPPING és SYNC státuszokhoz
     * CSAK STOCK logikára építve
     */
    protected function installDb()
    {
        $sql = [];

        // 1. Tábla: Supplier Mapping (Presta termék ID <-> Supplier SKU/EAN) + SYNC kapcsoló
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mp_supplier_map` (
            `id_mp_map` int(11) NOT NULL AUTO_INCREMENT,
            `id_product` int(11) NOT NULL,
            `id_product_attribute` int(11) DEFAULT 0,
            `supplier_sku` varchar(64) NOT NULL,
            `source_type` varchar(32) NOT NULL DEFAULT "supplier_main", 
            `sync_enabled` tinyint(1) unsigned NOT NULL DEFAULT 0,
            `last_synced` datetime DEFAULT NULL,
            PRIMARY KEY (`id_mp_map`),
            KEY `idx_product` (`id_product`, `id_product_attribute`),
            KEY `idx_sku` (`supplier_sku`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        // 2. Tábla: Marketplace Mapping (Presta termék ID <-> Marketplace SKU) + SYNC kapcsoló
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mp_marketplace_map` (
            `id_mp_market_map` int(11) NOT NULL AUTO_INCREMENT,
            `id_product` int(11) NOT NULL,
            `id_product_attribute` int(11) DEFAULT 0,
            `marketplace_sku` varchar(64) NOT NULL,
            `marketplace_type` varchar(32) NOT NULL, -- "emag" vagy "trendyol"
            `sync_enabled` tinyint(1) unsigned NOT NULL DEFAULT 0,
            `last_synced` datetime DEFAULT NULL,
            PRIMARY KEY (`id_mp_market_map`),
            UNIQUE KEY `idx_unique_mp` (`id_product`, `id_product_attribute`, `marketplace_type`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        // 3. Tábla: Logs (Csak egyszerű stock log)
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mp_stock_logs` (
            `id_log` int(11) NOT NULL AUTO_INCREMENT,
            `severity` varchar(16) NOT NULL, -- "INFO", "ERROR", "SUCCESS"
            `message` text NOT NULL,
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_log`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Admin Menük (Tabs) létrehozása
     * Így lesz 3 külön konfigurációs oldalad
     */
    protected function installTabs()
    {
        // Főmenü létrehozása (láthatatlan szülő)
        $mainTabId = $this->addTab('MpStockSyncController', 'Stock Sync Pro', -1);

        // 1. Almenü: Supplier -> Te Shopod
        $this->addTab('AdminMpSupplierSync', 'Supplier Sync (Main)', $mainTabId);

        // 2. Almenü: Supplier -> Másik Shop
        $this->addTab('AdminMpOtherShopSync', 'Supplier Sync (Other Shop)', $mainTabId);

        // 3. Almenü: Marketplace Sync
        $this->addTab('AdminMpMarketplaceSync', 'Marketplace Sync', $mainTabId);
        
        // 4. Almenü: Beállítások & Logs
        $this->addTab('AdminMpSettings', 'Settings & Logs', $mainTabId);

        return true;
    }

    protected function addTab($controller, $name, $parentId)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $controller;
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        $tab->id_parent = $parentId;
        $tab->module = $this->name;
        return $tab->add();
    }

    protected function uninstallTabs()
    {
        $controllers = [
            'MpStockSyncController',
            'AdminMpSupplierSync',
            'AdminMpOtherShopSync',
            'AdminMpMarketplaceSync',
            'AdminMpSettings'
        ];

        foreach ($controllers as $controller) {
            $id_tab = (int) Tab::getIdFromClassName($controller);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
        return true;
    }
    
    // A régi getContent() helyett átirányítjuk az első Controllerre, ha valaki a modul listából kattint
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMpSettings'));
    }
}
