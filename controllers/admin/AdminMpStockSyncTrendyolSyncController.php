<?php

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AdminMpStockSyncTrendyolSyncController extends FrameworkBundleAdminController
{
    public function syncAction(Request $request)
    {
        try {
            /** @var \MpStockSync\Services\TrendyolService $trendyolService */
            $trendyolService = $this->get('mpstocksync.service.trendyol');

            $result = $trendyolService->syncStocks();

            return new Response(
                json_encode([
                    'success' => true,
                    'message' => 'Trendyol stock sync completed.',
                    'result' => $result
                ]),
                200,
                ['Content-Type' => 'application/json']
            );

        } catch (\Exception $e) {

            return new Response(
                json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }
}
