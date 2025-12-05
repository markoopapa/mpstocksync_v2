<?php

require_once _PS_MODULE_DIR_ . 'mpstocksync/services/BaseService.php';

class TrendyolService extends BaseService
{
    private $sellerId;
    private $integrationCode;
    private $apiKey;
    private $apiSecret;
    private $token;
    private $baseUrl = 'https://api.trendyol.com';

    public function __construct()
    {
        parent::__construct();
        $this->sellerId = Configuration::get('MPSTOCKSYNC_TRENDYOL_SELLER_ID');
        $this->integrationCode = Configuration::get('MPSTOCKSYNC_TRENDYOL_INTEGRATION_CODE');
        $this->apiKey = Configuration::get('MPSTOCKSYNC_TRENDYOL_API_KEY');
        $this->apiSecret = Configuration::get('MPSTOCKSYNC_TRENDYOL_API_SECRET');
        $this->token = Configuration::get('MPSTOCKSYNC_TRENDYOL_TOKEN');
    }

    public function testConnection()
    {
        $headers = $this->getHeaders();
        $url = $this->baseUrl . '/api/v1/sellers/' . $this->sellerId . '/products';
        
        $response = $this->makeRequest($url, 'GET', $headers);
        
        if ($response && isset($response['success']) && $response['success']) {
            return array('success' => true, 'message' => 'Connection successful');
        } else {
            return array('success' => false, 'message' => 'Connection failed: ' . ($response['message'] ?? 'Unknown error'));
        }
    }

    public function getProducts($page = 0, $size = 100)
    {
        $headers = $this->getHeaders();
        $url = $this->baseUrl . '/api/v1/sellers/' . $this->sellerId . '/products?page=' . $page . '&size=' . $size;
        
        return $this->makeRequest($url, 'GET', $headers);
    }

    public function updateStock($items)
    {
        $headers = $this->getHeaders();
        $url = $this->baseUrl . '/api/v1/sellers/' . $this->sellerId . '/products/stock';
        
        $data = array('items' => $items);
        
        return $this->makeRequest($url, 'PUT', $headers, $data);
    }

    public function updatePrice($items)
    {
        $headers = $this->getHeaders();
        $url = $this->baseUrl . '/api/v1/sellers/' . $this->sellerId . '/products/price';
        
        $data = array('items' => $items);
        
        return $this->makeRequest($url, 'PUT', $headers, $data);
    }

    private function getHeaders()
    {
        return array(
            'Content-Type: application/json',
            'User-Agent: ' . $this->integrationCode,
            'Authorization: Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
            'X-Auth-Token: ' . $this->token
        );
    }
}
