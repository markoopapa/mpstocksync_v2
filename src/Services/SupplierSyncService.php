<?php
// src/Services/SupplierSyncService.php
namespace MpStockSync\Services;

class SupplierSyncService
{
    private $supplierConfig;
    private $dbConnection;
    
    public function __construct($supplierId)
    {
        $this->loadSupplierConfig($supplierId);
        $this->connect();
    }
    
    private function loadSupplierConfig($supplierId)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'mpstocksync_suppliers`
                WHERE id_supplier = '.(int)$supplierId;
        
        $this->supplierConfig = Db::getInstance()->getRow($sql);
        
        if (!$this->supplierConfig) {
            throw new \Exception('Supplier not found');
        }
    }
    
    private function connect()
    {
        if ($this->supplierConfig['connection_type'] == 'database') {
            $this->connectDatabase();
        } else {
            $this->connectApi();
        }
    }
    
    private function connectDatabase()
    {
        $this->dbConnection = new \mysqli(
            $this->supplierConfig['db_host'],
            $this->supplierConfig['db_user'],
            $this->supplierConfig['db_password'],
            $this->supplierConfig['db_name']
        );
        
        if ($this->dbConnection->connect_error) {
            throw new \Exception('Database connection failed: ' . $this->dbConnection->connect_error);
        }
        
        $this->dbConnection->set_charset('utf8');
    }
    
    private function connectApi()
    {
        // PrestaShop Web Service API kapcsolat
        // Implementáció később
    }
    
    public function getProductsFromSupplier()
    {
        if ($this->supplierConfig['connection_type'] == 'database') {
            return $this->getProductsFromDatabase();
        } else {
            return $this->getProductsFromApi();
        }
    }
    
    private function getProductsFromDatabase()
    {
        $prefix = $this->supplierConfig['db_prefix'];
        
        $query = "
            SELECT 
                p.id_product,
                p.reference as supplier_reference,
                p.ean13 as supplier_ean13,
                p.upc as supplier_upc,
                pl.name as supplier_name,
                sa.quantity as supplier_quantity,
                sa.out_of_stock as supplier_oos,
                p.price as supplier_price,
                p.wholesale_price as supplier_wholesale_price
            FROM {$prefix}product p
            LEFT JOIN {$prefix}product_lang pl ON pl.id_product = p.id_product 
                AND pl.id_lang = 1
            LEFT JOIN {$prefix}stock_available sa ON sa.id_product = p.id_product 
                AND sa.id_product_attribute = 0
            WHERE p.active = 1
            ORDER BY p.id_product
        ";
        
        $result = $this->dbConnection->query($query);
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    public function syncSupplierToShops($supplierId)
    {
        $products = $this->getProductsFromSupplier();
        $targetShops = json_decode($this->supplierConfig['target_shops'], true);
        
        $results = [
            'total' => count($products),
            'updated' => 0,
            'errors' => 0,
            'details' => []
        ];
        
        foreach ($products as $product) {
            foreach ($targetShops as $shopId) {
                try {
                    $this->syncProductToShop($product, $shopId);
                    $results['updated']++;
                    
                    $results['details'][] = [
                        'product' => $product['supplier_reference'],
                        'shop' => $shopId,
                        'status' => 'success',
                        'quantity' => $product['supplier_quantity']
                    ];
                    
                } catch (\Exception $e) {
                    $results['errors']++;
                    
                    $results['details'][] = [
                        'product' => $product['supplier_reference'],
                        'shop' => $shopId,
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                }
            }
        }
        
        // Update last sync
        Db::getInstance()->update('mpstocksync_suppliers', [
            'last_sync' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ], 'id_supplier = ' . (int)$supplierId);
        
        return $results;
    }
    
    private function syncProductToShop($supplierProduct, $shopId)
    {
        // 1. Find matching product in target shop
        $localProductId = $this->findLocalProduct($supplierProduct, $shopId);
        
        if (!$localProductId) {
            throw new \Exception('Product not found in target shop');
        }
        
        // 2. Update stock
        $this->updateLocalStock($localProductId, $supplierProduct['supplier_quantity'], $shopId);
        
        // 3. Log the sync
        $this->logSync($supplierProduct, $localProductId, $shopId);
    }
    
    private function findLocalProduct($supplierProduct, $shopId)
    {
        // Try by reference
        $sql = 'SELECT id_product FROM `'._DB_PREFIX_.'product` 
                WHERE reference = "' . pSQL($supplierProduct['supplier_reference']) . '"';
        
        $result = Db::getInstance()->getValue($sql);
        
        if ($result) {
            return $result;
        }
        
        // Try by EAN13
        if (!empty($supplierProduct['supplier_ean13'])) {
            $sql = 'SELECT id_product FROM `'._DB_PREFIX_.'product` 
                    WHERE ean13 = "' . pSQL($supplierProduct['supplier_ean13']) . '"';
            
            $result = Db::getInstance()->getValue($sql);
            
            if ($result) {
                return $result;
            }
        }
        
        // Try by UPC
        if (!empty($supplierProduct['supplier_upc'])) {
            $sql = 'SELECT id_product FROM `'._DB_PREFIX_.'product` 
                    WHERE upc = "' . pSQL($supplierProduct['supplier_upc']) . '"';
            
            $result = Db::getInstance()->getValue($sql);
            
            if ($result) {
                return $result;
            }
        }
        
        return false;
    }
    
    private function updateLocalStock($productId, $quantity, $shopId)
    {
        // Set context to target shop
        $originalShopId = Context::getContext()->shop->id;
        Context::getContext()->shop = new Shop($shopId);
        
        try {
            // Update stock
            StockAvailable::setQuantity(
                $productId,
                0, // No attribute
                (int)$quantity,
                $shopId
            );
            
            // Update product out of stock status if needed
            $product = new Product($productId, false, null, $shopId);
            
            // Restore original context
            Context::getContext()->shop = new Shop($originalShopId);
            
        } catch (\Exception $e) {
            // Restore context on error
            Context::getContext()->shop = new Shop($originalShopId);
            throw $e;
        }
    }
    
    private function logSync($supplierProduct, $localProductId, $shopId)
    {
        Db::getInstance()->insert('mpstocksync_supplier_log', [
            'id_supplier' => $this->supplierConfig['id_supplier'],
            'id_product' => $localProductId,
            'id_shop' => $shopId,
            'supplier_reference' => pSQL($supplierProduct['supplier_reference']),
            'old_quantity' => 0, // We don't track old quantity for supplier sync
            'new_quantity' => (int)$supplierProduct['supplier_quantity'],
            'status' => 1,
            'date_add' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function testConnection()
    {
        try {
            if ($this->supplierConfig['connection_type'] == 'database') {
                return $this->testDatabaseConnection();
            } else {
                return $this->testApiConnection();
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function testDatabaseConnection()
    {
        // Try to get product count
        $prefix = $this->supplierConfig['db_prefix'];
        $query = "SELECT COUNT(*) as product_count FROM {$prefix}product WHERE active = 1";
        
        $result = $this->dbConnection->query($query);
        
        if ($result) {
            $row = $result->fetch_assoc();
            return [
                'success' => true,
                'message' => 'Connection successful. Found ' . $row['product_count'] . ' active products.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Could not query products table'
        ];
    }
}
