<?php
namespace MpStockSync\Services;

class EmagService
{
    private $apiUrl;
    private $clientId;
    private $clientSecret;
    private $username;
    private $password;
    private $accessToken;
    
    public function __construct($apiUrl, $clientId, $clientSecret, $username, $password)
    {
        $this->apiUrl = $apiUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
        
        $this->loadToken();
    }
    
    private function loadToken()
    {
        $this->accessToken = Configuration::get('MP_EMAG_ACCESS_TOKEN');
        $expiry = Configuration::get('MP_EMAG_TOKEN_EXPIRY');
        
        if (!$this->accessToken || time() >= $expiry) {
            $this->authenticate();
        }
    }
    
    private function authenticate()
    {
        $url = $this->apiUrl . '/api-2/auth/token';
        
        $data = [
            'grant_type' => 'password',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username' => $this->username,
            'password' => $this->password
        ];
        
        $response = $this->makeRequest($url, 'POST', $data, false);
        
        if (isset($response['access_token'])) {
            $this->accessToken = $response['access_token'];
            
            Configuration::updateValue('MP_EMAG_ACCESS_TOKEN', $this->accessToken);
            Configuration::updateValue('MP_EMAG_TOKEN_EXPIRY', time() + 3600);
            
            return true;
        }
        
        throw new \Exception('eMAG authentication failed: ' . json_encode($response));
    }
    
    public function updateStock($sku, $quantity)
    {
        $url = $this->apiUrl . '/api-2/offer/save_stock';
        
        $payload = [
            'products' => [
                [
                    'id' => $sku,
                    'stock' => [
                        'status' => $quantity > 0 ? 'visible' : 'hidden',
                        'qty' => (int)$quantity
                    ]
                ]
            ]
        ];
        
        return $this->makeRequest($url, 'POST', $payload);
    }
    
    public function updatePrice($sku, $price)
    {
        $url = $this->apiUrl . '/api-2/offer/save_price';
        
        $payload = [
            'products' => [
                [
                    'id' => $sku,
                    'sale_price' => (float)$price
                ]
            ]
        ];
        
        return $this->makeRequest($url, 'POST', $payload);
    }
    
    public function updateStockAndPrice($sku, $quantity, $price)
    {
        $url = $this->apiUrl . '/api-2/product_offer/save_stock';
        
        $payload = [
            'products' => [
                [
                    'id' => $sku,
                    'stock' => [
                        'status' => $quantity > 0 ? 'visible' : 'hidden',
                        'qty' => (int)$quantity
                    ],
                    'sale_price' => (float)$price
                ]
            ]
        ];
        
        return $this->makeRequest($url, 'POST', $payload);
    }
    
    public function getProducts($page = 1, $limit = 100)
    {
        $url = $this->apiUrl . '/api-2/product/read';
        
        $params = [
            'currentPage' => $page,
            'itemsPerPage' => $limit
        ];
        
        return $this->makeRequest($url . '?' . http_build_query($params), 'GET');
    }
    
    public function testConnection()
    {
        try {
            $this->authenticate();
            return ['success' => true, 'message' => 'Connection successful'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function makeRequest($url, $method = 'GET', $data = null, $useToken = true)
    {
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($useToken && $this->accessToken) {
            $headers[] = 'Authorization: Bearer ' . $this->accessToken;
        }
        
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
