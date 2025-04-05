<?php

namespace Modules\Budget\Services;

use Modules\Budget\Models\Currency;
use Modules\Budget\Models\ExchangeRate;

class CurrencyConverterService
{
    /**
     * Convierte una cantidad determinada de dinero de una moneda a otra,
     * utilizando el tipo de cambio correcto para la fecha dada.
     *
     * @param float $amount El monto a convertir
     * @param string $date La fecha en la que debe realizarse la conversión
     * @param Currency $fromCurrency La moneda desde la que convertir
     * @param Currency $toCurrency La moneda a la que convertir
     * @return float|null El importe convertido, o nulo si no se pudo realizar la conversión
     */
    public function convert(
        float $amount,
        string $date,
        Currency $fromCurrency,
        Currency $toCurrency
    ): ?float {

        // Si no hay moneda de destino, retornar null
        if (!$toCurrency) {
            return null;
        }

        // Si las monedas son iguales, retornar el mismo importe
        if ($fromCurrency->id === $toCurrency->id) {
            return $amount;
        }

        // Obtener la tasa de cambio en la fecha especificada
        $exchangeRate = ExchangeRate::where('from_currency_id', $fromCurrency->id)
            ->where('to_currency_id', $toCurrency->id)
            ->where('start_at', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('end_at', '>=', $date)
                        ->orWhereNull('end_at');
            })
            ->orderBy('start_at', 'desc')
            ->first();

        // Calcular el importe convertido
        return $exchangeRate ? $amount * $exchangeRate->amount : null;
    }
}
