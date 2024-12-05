<?php

return [
    'name' => 'Budget',
    'name_es' => 'Presupuesto',
    'budget_availability' => [
        'active' => env('BUDGET_AVAILABILITY_CUSTOM', false),
        'separator' => env('BUDGET_SEPARATOR', '-'), /** Separador entre segmentos */
    ]
];
