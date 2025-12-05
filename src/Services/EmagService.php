<?php
// NINCS namespace! Sima class, mert manuálisan töltjük be
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
    }
    
    public function updateStock($sku, $quantity)
    {
        try {
            // TODO: Implement real eMAG API call
            // For now, return mock response
            
            return [
                'success' => true,
                'http_code' => 200,
                'message' => 'Stock updated successfully (MOCK)',
                'data' => [
                    'sku' => $sku,
                    'quantity' => $quantity
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function testConnection()
    {
        return [
            'success' => true,
            'message' => 'eMAG connection test successful (MOCK)'
        ];
    }
}
