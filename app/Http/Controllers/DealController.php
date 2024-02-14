<?php

namespace App\Http\Controllers;

use App\Services\AmoCRMIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DealController extends Controller
{
    private $amoCRMIntegrationService;

    public function __construct(AmoCRMIntegrationService $amoCRMIntegrationService)
    {
        $this->amoCRMIntegrationService = $amoCRMIntegrationService;
    }

    public function calculateProfit(Request $request): JsonResponse
    {
        $dealId = $request->input('deal_id');

        // Проверяем наличие deal_id в запросе
        if (!$dealId) {
            return response()->json(['error' => 'Missing deal_id'], 400);
        }

        // Вызываем метод calculateProfit из сервиса AmoCRMIntegrationService
        $this->amoCRMIntegrationService->calculateProfit($dealId);

        return response()->json(['message' => 'Profit calculation complete']);
    }
}


