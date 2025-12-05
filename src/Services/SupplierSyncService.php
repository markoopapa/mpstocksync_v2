<?php
namespace MpStockSync\Services;  // ← UGYANAZ A NAMESPACE!

class SupplierSyncService
{
    private $supplierConfig;
    
    public function __construct($supplierId)
    {
        $this->loadSupplierConfig($supplierId);
    }
    
    private function loadSupplierConfig($supplierId)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'mpstocksync_suppliers`
                WHERE id_supplier = '.(int)$supplierId;
        
        $this->supplierConfig = Db::getInstance()->getRow($sql);
        
        if (!$this->supplierConfig) {
            throw new \Exception('Supplier configuration not found');
        }
    }
    
    public function syncSupplierToShops($supplierId)
    {
        // Mock implementation
        return [
            'success' => true,
            'total' => 15,
            'updated' => 12,
            'errors' => 3,
            'message' => 'Supplier sync completed (MOCK)'
        ];
    }
    
    public function testConnection()
    {
        if ($this->supplierConfig['connection_type'] == 'database') {
            return $this->testDatabaseConnection();
        } else {
            return $this->testApiConnection();
        }
    }
    
    private function testDatabaseConnection()
    {
        return [
            'success' => true,
            'message' => 'Database connection successful (MOCK)'
        ];
    }
    
    private function testApiConnection()
    {
        return [
            'success' => true,
            'message' => 'API connection successful (MOCK)'
        ];
    }
}
