<?php
namespace MpStockSync\Service;

use MpStockSync\ApiClient\EmagApiClient;
use MpStockSync\ApiClient\TrendyolApiClient;

/**
 * MarketplaceSyncService
 * - push stock changes from local products to marketplaces (only stock)
 */
class MarketplaceSyncService
{
    private $emagClient;
    private $trendyolClient;

    public function __construct(array $emagConfig = [], array $trendyolConfig = [])
    {
        $this->emagClient = new EmagApiClient(
            $emagConfig['api_url'] ?? '',
            $emagConfig['client_id'] ?? '',
            $emagConfig['client_secret'] ?? '',
            $emagConfig['username'] ?? '',
            $emagConfig['password'] ?? ''
        );

        $this->trendyolClient = new TrendyolApiClient(
            $trendyolConfig['api_url'] ?? '',
            $trendyolConfig['api_key'] ?? '',
            $trendyolConfig['api_secret'] ?? '',
            $trendyolConfig['seller_id'] ?? ''
        );
    }

    /**
     * Push single product stock to enabled marketplaces
     * $mapping array should contain 'api_name' and 'external_id' keys
     */
    public function pushStockToMarketplace(array $mapping, int $quantity): array
    {
        $result = [];

        if (!isset($mapping['api_name']) || !isset($mapping['external_id'])) {
            return ['success' => false, 'error' => 'Invalid mapping'];
        }

        $api = $mapping['api_name'];
        $externalId = $mapping['external_id'];

        if ($api === 'emag') {
            $result['emag'] = $this->emagClient->updateStock($externalId, $quantity);
        } elseif ($api === 'trendyol') {
            $result['trendyol'] = $this->trendyolClient->updateStock($externalId, $quantity);
        } else {
            $result['error'] = 'Unknown API: ' . $api;
        }

        return $result;
    }
}
