<?php
namespace MpStockSync\Services;  // ← UGYANAZ A NAMESPACE!

class TrendyolService
{
    private $apiUrl;
    private $apiKey;
    private $apiSecret;
    private $supplierId;
    
    public function __construct($apiUrl, $apiKey, $apiSecret, $supplierId)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->supplierId = $supplierId;
    }
    
    public function updateStockAndPrice($barcode, $quantity, $salePrice)
    {
        // Mock implementation
        return [
            'success' => true,
            'message' => 'Stock and price updated successfully (MOCK)',
            'data' => [
                'barcode' => $barcode,
                'quantity' => $quantity,
                'salePrice' => $salePrice
            ]
        ];
    }
    
    public function testConnection()
    {
        return [
            'success' => true,
            'message' => 'Trendyol connection test successful (MOCK)'
        ];
    }
}
