<?php
namespace MpStockSync\ApiClient;

use Exception;

/**
 * Supplier PrestaShop Webservice API client
 * - Basic auth with API key
 * - Returns products with: name, reference, quantity
 */
class SupplierApiClient
{
    private $apiUrl;
    private $apiKey;

    public function __construct(string $apiUrl, string $apiKey)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
    }

    /**
     * Fetch products from supplier PrestaShop API
     * Returns: ['success' => bool, 'products' => [ ['name'=>..., 'reference'=>..., 'quantity'=>...], ... ], 'error'=>string? ]
     */
    public function getProducts(): array
    {
        // PrestaShop webservice: use display parameter to limit fields
        $url = $this->apiUrl . '/products?display=[reference,name]&output_format=JSON';
        // We'll fetch quantities separately via stock_availables for efficiency
        try {
            $productsRaw = $this->call($url);
            if (!isset($productsRaw['products']) || !is_array($productsRaw['products'])) {
                return ['success' => false, 'error' => 'Invalid products payload'];
            }

            // Build map of references -> product basic info
            $items = [];
            foreach ($productsRaw['products'] as $p) {
                $ref = isset($p['reference']) ? $p['reference'] : '';
                $name = isset($p['name']) ? $p['name'] : '';
                // quantity will be fetched per product via stock_availables endpoint
                $items[$ref] = [
                    'reference' => $ref,
                    'name' => $name,
                    'quantity' => 0
                ];
            }

            // Now fetch stock_availables for those products in batch (if many, do it per-chunk)
            // PrestaShop API: /stock_availables?display=[id_product,quantity]&filter[reference]=[...]
            // Not all PS setups support filtering by reference on stock_availables; we'll fallback to fetching by product ids if available.
            // Simpler: iterate products and query stock for each product by its product url given in _links if present.
            foreach ($productsRaw['products'] as $p) {
                // try to obtain product id and then query stock_availables by id_product
                if (isset($p['id'])) {
                    $id = $p['id'];
                    $stockUrl = $this->apiUrl . '/stock_availables?display=[id_product,quantity]&filter[id_product]=' . (int)$id . '&output_format=JSON';
                    try {
                        $stockResp = $this->call($stockUrl);
                        if (isset($stockResp['stock_availables'][0]['quantity'])) {
                            $quantity = (int)$stockResp['stock_availables'][0]['quantity'];
                        } else {
                            $quantity = 0;
                        }
                    } catch (Exception $e) {
                        $quantity = 0;
                    }

                    $ref = isset($p['reference']) ? $p['reference'] : '';
                    if ($ref !== '' && isset($items[$ref])) {
                        $items[$ref]['quantity'] = $quantity;
                    }
                } else {
                    // fallback: try to fetch product resource and parse stock in product -> depends on API config; we skip for now
                    continue;
                }
            }

            // convert items associative to numeric array
            $clean = array_values($items);

            return ['success' => true, 'products' => $clean];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Low-level API caller
     * Returns decoded JSON as array or throws Exception
     */
    private function call(string $url): array
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->apiKey . ':',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ],
        ]);

        $result = curl_exec($ch);
        if ($result === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception('Supplier API CURL error: ' . $err);
        }

        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            throw new Exception('Supplier API returned HTTP ' . $code . ' for ' . $url);
        }

        $json = json_decode($result, true);
        if (!is_array($json)) {
            throw new Exception('Supplier API returned invalid JSON');
        }

        return $json;
    }
}
