<?php

namespace MpStockSync\Service;

use Db;
use PrestaShopDatabaseException;

class SupplierMappingService
{
    private $db;
    private $table;

    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->table = _DB_PREFIX_ . 'mpstocksync_supplier_map';
    }

    /**
     * Create mapping table if not exists
     */
    public function install()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id_map` INT AUTO_INCREMENT PRIMARY KEY,
            `id_supplier` INT NOT NULL,
            `supplier_reference` VARCHAR(255) NOT NULL,
            `local_id_product` INT DEFAULT NULL,
            `local_id_product_attribute` INT DEFAULT NULL,
            `sync_enabled` TINYINT(1) DEFAULT 1,
            INDEX (`id_supplier`),
            INDEX (`supplier_reference`),
            INDEX (`local_id_product`)
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;
        ";

        return $this->db->execute($sql);
    }

    /**
     * Save or update a mapping entry
     */
    public function saveMapping($supplierId, $supplierRef, $localIdProduct, $localIdAttr, $syncEnabled = 1)
    {
        $supplierRef = pSQL($supplierRef);

        // Check if exists
        $existing = $this->getMapping($supplierId, $supplierRef);

        if ($existing) {
            $sql = "
                UPDATE `{$this->table}`
                SET 
                    `local_id_product` = " . (int) $localIdProduct . ",
                    `local_id_product_attribute` = " . (int) $localIdAttr . ",
                    `sync_enabled` = " . (int) $syncEnabled . "
                WHERE `id_map` = " . (int) $existing['id_map'] . "
            ";
        } else {
            $sql = "
                INSERT INTO `{$this->table}`
                (`id_supplier`, `supplier_reference`, `local_id_product`, `local_id_product_attribute`, `sync_enabled`)
                VALUES (
                    " . (int) $supplierId . ",
                    '{$supplierRef}',
                    " . (int) $localIdProduct . ",
                    " . (int) $localIdAttr . ",
                    " . (int) $syncEnabled . "
                )
            ";
        }

        return $this->db->execute($sql);
    }

    /**
     * Get mapping for a single supplier reference
     */
    public function getMapping($supplierId, $supplierRef)
    {
        $sql = "
            SELECT *
            FROM `{$this->table}`
            WHERE id_supplier = " . (int) $supplierId . "
            AND supplier_reference = '" . pSQL($supplierRef) . "'
        ";

        return $this->db->getRow($sql);
    }

    /**
     * Get all mappings for supplier ID
     */
    public function getMappingsBySupplier($supplierId)
    {
        $sql = "
            SELECT *
            FROM `{$this->table}`
            WHERE id_supplier = " . (int) $supplierId . "
            ORDER BY supplier_reference ASC
        ";

        return $this->db->executeS($sql);
    }

    /**
     * Auto-create missing mappings from supplier API products
     */
    public function autoGenerateMappings($supplierId, $supplierProducts)
    {
        foreach ($supplierProducts as $p) {
            if (!isset($p['reference'])) {
                continue;
            }

            $ref = pSQL($p['reference']);
            $existing = $this->getMapping($supplierId, $ref);

            if (!$existing) {
                $this->saveMapping($supplierId, $ref, null, null, 0);
            }
        }
    }

    /**
     * Enable/disable sync for mapping
     */
    public function setSyncEnabled($idMap, $enabled)
    {
        $sql = "
            UPDATE `{$this->table}`
            SET sync_enabled = " . (int) $enabled . "
            WHERE id_map = " . (int) $idMap . "
        ";

        return $this->db->execute($sql);
    }

    /**
     * Delete mapping
     */
    public function deleteMapping($idMap)
    {
        return $this->db->delete($this->table, '`id_map` = ' . (int) $idMap);
    }
}
