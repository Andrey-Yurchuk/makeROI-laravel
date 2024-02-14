<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AmoCRMIntegrationService
{
    private $apiUrl = 'a1430gmailcom';
    private $accessToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImM1NmI5YzNhZjI1Njc3ZTVmYWFmOTk1MTE5MjVlYjQ5Z
    Dc4MTQ1MGUwNGU4MmZmOWI0YjY1ZWY4MDQ4MGYzYmM2N2UzMzhjN2NmYmVlNjVmIn0.eyJhdWQiOiJiZGYxMzFlNC0zYzI5LTQ3OTQtOGVmYi03O
    TkzMGM3ZWZlMjIiLCJqdGkiOiJjNTZiOWMzYWYyNTY3N2U1ZmFhZjk5NTExOTI1ZWI0OWQ3ODE0NTBlMDRlODJmZjliNGI2NWVmODA0ODBmM2Jj
    NjdlMzM4YzdjZmJlZTY1ZiIsImlhdCI6MTcwNzkxOTI1MCwibmJmIjoxNzA3OTE5MjUwLCJleHAiOjE3MTUyOTkyMDAsInN1YiI6Ij
    EwNjc1MzE4IiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxNTY5NDc4LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24
    iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlm
    aWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiMmIwNjNiMGItMzcwZS00OGJjLWI4OWEtMmYzZmQxY2I1NDdjIn0.IXBxjXiUoawMn3Qz7L-m9vimNxYavh
    yTChaKJysBO0eeQik67xJQhRlhYbNl6ycHabTLzSCV9d-K--klwGvhiNA7zwH_RT9isybwrJBfdeoJTLZRaI1RDx-gLng4eYP5Sr1
    Y9PxSHoIRr7LBZOB4Hl8Dp7oXgaLQjWh8CK-1-aiDfuAI5Q9VSAIYrVU41AOLflFBX_mviW3_m_w9ex_hTRP4CnsiL6QCDt_utMp
    EJ1AVMxdEP8pvnzU3jzR0nv3GMWgk8r06pzh_jIZDnwOBcEZDc9zsAXMi0f9yaqL93FdA2y3jZDxrF4XfSRrBBfq6yeHAeqbYw_Jx7CNq2VvbeA';

    public function calculateProfit($dealId): void
    {
        // Получаем данные сделки
        $deal = $this->getDeal($dealId);

        // Проверяем наличие необходимых полей
        if (!isset($deal['custom_fields_values'])) {
            return;
        }

        $budgetFieldId = null;
        $costFieldId = null;
        $profitFieldId = null;

        // Ищем идентификаторы полей "Бюджет сделки", "Себестоимость" и "Прибыль"
        foreach ($deal['custom_fields_values'] as $field) {
            if ($field['field_code'] === 'BUDGET') {
                $budgetFieldId = $field['field_id'];
            } elseif ($field['field_code'] === 'COST') {
                $costFieldId = $field['field_id'];
            } elseif ($field['field_code'] === 'PROFIT') {
                $profitFieldId = $field['field_id'];
            }
        }

        // Проверяем наличие необходимых полей
        if (!$budgetFieldId || !$costFieldId || !$profitFieldId) {
            return;
        }

        // Получаем значения полей "Бюджет сделки" и "Себестоимость"
        $budgetValue = $this->getFieldValue($deal['custom_fields_values'], $budgetFieldId);
        $costValue = $this->getFieldValue($deal['custom_fields_values'], $costFieldId);

        // Проверяем наличие значений полей
        if (!$budgetValue || !$costValue) {
            return;
        }

        // Рассчитываем прибыль
        $profitValue = $budgetValue - $costValue;

        // Обновляем поле "Прибыль" с помощью API
        $this->updateField($dealId, $profitFieldId, $profitValue);
    }

    private function getDeal($dealId): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->get($this->apiUrl . '/leads/' . $dealId);

        if ($response->successful()) {
            return $response->json()['data'];
        }

        return null;
    }

    private function getFieldValue($fields, $fieldId): ?string
    {
        foreach ($fields as $field) {
            if ($field['field_id'] === $fieldId) {
                return $field['values'][0]['value'];
            }
        }

        return null;
    }

    private function updateField($dealId, $fieldId, $value): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->patch($this->apiUrl . '/leads/' . $dealId, [
            'custom_fields_values' => [
                [
                    'field_id' => $fieldId,
                    'values' => [
                        [
                            'value' => $value,
                        ],
                    ],
                ],
            ],
        ]);

        if ($response->successful()) {
            return true;
        }

        return false;
    }
}
