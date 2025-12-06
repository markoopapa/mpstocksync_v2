<?php

namespace MpStockSync\Services;

class TrendyolService
{
    private $apiUrl;
    private $sellerId;
    private $apiKey;
    private $apiSecret;

    public function __construct(
        string $apiUrl,
        string $sellerId,
        string $apiKey,
        string $apiSecret
    ) {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->sellerId = $sellerId;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Küldj stock update-et Trendyol felé
     */
    public function updateStock(string $productId, int $quantity): array
    {
        $url = $this->apiUrl . '/suppliers/' . $this->sellerId . '/products/stock';

        $payload = [
            'items' => [
                [
                    'barcode'  => $productId,
                    'quantity' => $quantity,
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: Trendyol-PrestaShop',
                'Authorization: Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'status' => $status,
            'response' => $result
        ];
    }
}
