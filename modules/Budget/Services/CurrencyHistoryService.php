<?php

namespace Modules\Budget\Services;

use Carbon\CarbonPeriod;
use Modules\Budget\Models\Currency;
use Modules\Budget\Models\ExchangeRate;

class CurrencyHistoryService
{
    /**
     * Recupera el historial de tipos de cambio para un par de divisas determinado dentro de un rango de fechas específico.
     *
     * @param string $startDate La fecha de inicio del período histórico en formato 'Y-m-d'.
     * @param string $endDate La fecha de finalización del período histórico en formato 'Y-m-d'.
     * @param Currency $fromCurrency La moneda base para el tipo de cambio.
     * @param Currency $toCurrency La moneda de destino del tipo de cambio.
     *
     * @return array Una lista de tipos de cambio para el rango de fechas dado. Si las monedas son las mismas,
     *               Devuelve una lista con una tasa de cambio de 1 para cada fecha. Si no se proporciona la moneda de destino,
     *               devuelve una lista vacía.
     */
    public function getExchangeRateHistory(
        string $startDate,
        string $endDate,
        Currency $fromCurrency,
        Currency $toCurrency
    ): array {
        // Si no hay moneda de destino, retornar un array vacío
        if (!$toCurrency) {
            return [];
        }

        // Si las monedas son iguales, retornar 1 para todas las fechas
        if ($fromCurrency->id === $toCurrency->id) {
            return $this->generateDefaultRates($startDate, $endDate);
        }

        // Obtener las tasas de cambio en el rango de fechas
        $rates = ExchangeRate::where('from_currency_id', $fromCurrency->id)
            ->where('to_currency_id', $toCurrency->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_at', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($endDate) {
                        $q->where('start_at', '<=', $endDate)
                        ->whereNull('end_at');
                    });
            })
            ->orderBy('start_at', 'desc')
            ->get();

        // Procesar las tasas para generar el historial
        return $this->processRates($rates, $startDate, $endDate);
    }

    private function generateDefaultRates(string $start, string $end): array
    {
        $period = CarbonPeriod::create($start, $end);
        $result = [];

        // Generar un array con valor 1 para todas las fechas
        foreach ($period as $date) {
            $result[$date->format('Y-m-d')] = 1;
        }

        return $result;
    }

    /**
     * Procesa las tasas de cambio para generar el historial.
     *
     * @param array $rates Las tasas de cambio para el rango de fechas.
     * @param string $start La fecha de inicio del período histórico.
     * @param string $end La fecha de finalización del período histórico.
     *
     * @return array Un array con las tasas de cambio para el rango de fechas dado.
     */
    private function processRates($rates, string $start, string $end): array
    {
        $period = CarbonPeriod::create($start, $end);
        $history = [];
        $ratePointer = 0;
        $currentRate = null;

        foreach ($period as $date) {
            $currentDate = $date->format('Y-m-d');

            // Buscar la tasa válida para esta fecha
            while ($ratePointer < count($rates)) {
                $rate = $rates[$ratePointer];

                // Asegurarse de que start_at y end_at sean instancias de Carbon
                $rateStart = \Carbon\Carbon::parse($rate->start_at)->format('Y-m-d');
                $rateEnd = $rate->end_at ? \Carbon\Carbon::parse($rate->end_at)->format('Y-m-d') : now()->format('Y-m-d');

                // Si la fecha está dentro del rango de la tasa actual
                if ($rateStart <= $currentDate && $rateEnd >= $currentDate) {
                    $currentRate = $rate->amount;
                    break;
                }

                $ratePointer++;
            }

            // Asignar la tasa encontrada (o null si no hay)
            $history[$currentDate] = $currentRate ?: null;
        }

        return $history;
    }
}
