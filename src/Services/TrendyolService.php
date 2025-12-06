<?php

namespace MpStockSync\Services;

use MpStockSync\ApiClient\TrendyolApiClient;

class TrendyolService
{
    private $client;

    public function __construct()
    {
        $this->client = new TrendyolApiClient(
            \Configuration::get('MPSTOCKSYNC_TRENDYOL_SELLER_ID'),
            \Configuration::get('MPSTOCKSYNC_TRENDYOL_API_KEY'),
            \Configuration::get('MPSTOCKSYNC_TRENDYOL_API_SECRET')
        );
    }

    /**
     * Send stock update for a single product
     */
    public function updateStock($productId, $reference, $quantity)
    {
        return $this->client->updateStock($reference, $quantity);
    }

    /**
     * Bulk push (used for full sync)
     */
    public function bulkUpdateStock(array $items)
    {
        return $this->client->bulkUpdateStock($items);
    }
}
