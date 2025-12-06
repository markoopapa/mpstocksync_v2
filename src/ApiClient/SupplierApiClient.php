<?php

namespace MpStockSync\Supplier;

class SupplierApiClient
{
    private $apiUrl;
    private $apiKey;

    public function __construct($apiUrl, $apiKey)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
    }

    /**
     * Fetch products from supplier PrestaShop API
     * Required fields: name, reference, quantity
     */
    public function getProducts()
    {
        $url = $this->apiUrl . '/products?display=[reference,name,quantity]';

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_USERPWD => $this->apiKey . ':',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return [
                'success' => false,
                'error' => 'CURL Error: ' . $error
            ];
        }

        $data = json_decode($response, true);

        if (!$data || !isset($data['products'])) {
            return [
                'success' => false,
                'error' => 'Invalid supplier API response'
            ];
        }

        $clean = [];

        foreach ($data['products'] as $p) {
            $clean[] = [
                'name'      => $p['name'] ?? '',
                'reference' => $p['reference'] ?? '',
                'quantity'  => isset($p['quantity']) ? (int)$p['quantity'] : 0
            ];
        }

        return [
            'success' => true,
            'products' => $clean
        ];
    }
}
