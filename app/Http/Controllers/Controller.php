<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class Controller
{
    // Define as regras padrão para os filtros
    protected static $defaultFilterRules = [
        'page' => ['integer', 'min:1'],
        'per_page' => ['integer', 'min:1'],
        'search' => ['nullable', 'string'],
    ];

    /**
     * Formata a resposta de uma requisição paginada.
     */
    protected static function paginatedResponse(LengthAwarePaginator $paginatedResult, array $additionalData = []): array
    {
        return array_merge([
            'success' => true,
            'data' => $paginatedResult->items(),
            'pagination' => [
                'current_page' => $paginatedResult->currentPage(),
                'last_page' => $paginatedResult->lastPage(),
                'from' => $paginatedResult->firstItem(),
                'to' => $paginatedResult->lastItem(),
                'total' => $paginatedResult->total(),
                'per_page' => $paginatedResult->perPage(),
            ],
        ], $additionalData);
    }
}