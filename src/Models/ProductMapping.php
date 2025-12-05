<?php
namespace MpStockSync\Models;

class ProductMapping
{
    public static function getByProduct($id_product, $id_product_attribute, $api_name)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'mpstocksync_mapping`
                WHERE id_product = '.(int)$id_product.'
                AND id_product_attribute = '.(int)$id_product_attribute.'
                AND api_name = "'.pSQL($api_name).'"
                AND active = 1';
        
        return Db::getInstance()->getRow($sql);
    }
    
    public static function getByExternalId($external_id, $api_name)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'mpstocksync_mapping`
                WHERE external_id = "'.pSQL($external_id).'"
                AND api_name = "'.pSQL($api_name).'"
                AND active = 1';
        
        return Db::getInstance()->getRow($sql);
    }
    
    public static function save($id_product, $id_product_attribute, $api_name, $external_id, $external_reference = null)
    {
        $existing = self::getByProduct($id_product, $id_product_attribute, $api_name);
        
        $data = [
            'id_product' => (int)$id_product,
            'id_product_attribute' => (int)$id_product_attribute,
            'api_name' => pSQL($api_name),
            'external_id' => pSQL($external_id),
            'external_reference' => pSQL($external_reference),
            'date_upd' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            return Db::getInstance()->update(
                'mpstocksync_mapping',
                $data,
                'id_mapping = '.(int)$existing['id_mapping']
            );
        } else {
            $data['date_add'] = date('Y-m-d H:i:s');
            return Db::getInstance()->insert('mpstocksync_mapping', $data);
        }
    }
    
    public static function getMappedProducts($api_name = null, $active_only = true)
    {
        $sql = 'SELECT m.*, p.reference, p.ean13, p.upc, pl.name
                FROM `'._DB_PREFIX_.'mpstocksync_mapping` m
                INNER JOIN `'._DB_PREFIX_.'product` p ON p.id_product = m.id_product
                INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON pl.id_product = p.id_product
                    AND pl.id_lang = '.(int)Context::getContext()->language->id;
        
        if ($api_name) {
            $sql .= ' WHERE m.api_name = "'.pSQL($api_name).'"';
        }
        
        if ($active_only) {
            $sql .= ($api_name ? ' AND' : ' WHERE') . ' m.active = 1';
        }
        
        $sql .= ' ORDER BY m.date_upd DESC';
        
        return Db::getInstance()->executeS($sql);
    }
    
    public static function deleteMapping($id_mapping)
    {
        return Db::getInstance()->delete(
            'mpstocksync_mapping',
            'id_mapping = '.(int)$id_mapping
        );
    }
}
