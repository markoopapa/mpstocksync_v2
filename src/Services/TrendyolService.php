<?php
namespace MpStockSync\Services;

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
    
    public function updateStockAndPrice($barcode, $quantity, $salePrice, $listPrice = null)
    {
        $url = $this->apiUrl . '/suppliers/' . $this->supplierId . '/products/price-and-inventory';
        
        if ($listPrice === null) {
            $listPrice = $salePrice * 1.2; // Default 20% higher
        }
        
        $payload = [
            'items' => [
                [
                    'barcode' => $barcode,
                    'quantity' => (int)$quantity,
                    'salePrice' => (float)$salePrice,
                    'listPrice' => (float)$listPrice
                ]
            ]
        ];
        
        return $this->makeRequest($url, 'POST', $payload);
    }
    
    public function batchUpdateStockAndPrice($items)
    {
        $url = $this->apiUrl . '/suppliers/' . $this->supplierId . '/products/batch-requests/price-and-inventory';
        
        $payload = ['items' => []];
        
        foreach ($items as $item) {
            $payload['items'][] = [
                'barcode' => $item['barcode'],
                'quantity' => (int)$item['quantity'],
                'salePrice' => (float)$item['salePrice'],
                'listPrice' => isset($item['listPrice']) ? 
                    (float)$item['listPrice'] : (float)$item['salePrice'] * 1.2
            ];
        }
        
        return $this->makeRequest($url, 'POST', $payload);
    }
    
    public function getProducts($page = 0, $size = 100)
    {
        $url = $this->apiUrl . '/suppliers/' . $this->supplierId . '/products';
        
        $params = [
            'page' => $page,
            'size' => $size
        ];
        
        return $this->makeRequest($url . '?' . http_build_query($params), 'GET');
    }
    
    public function testConnection()
    {
        try {
            $result = $this->getProducts(0, 1);
            return [
                'success' => $result['success'],
                'message' => $result['success'] ? 'Connection successful' : 'Connection failed'
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function makeRequest($url, $method = 'GET', $data = null)
    {
        $ch = curl_init();
        
        $headers = [
            'Authorization: Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
            'Content-Type: application/json',
            'User-Agent: PrestaShop-MPStockSync/2.0'
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'data' => $result,
            'raw' => $response
        ];
    }
}
