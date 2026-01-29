<?php

namespace App\Http\Controllers;

use App\Models\Tenant;

class TenantController extends Controller
{
    // listar todos os tenants
    public function index()
    {
        try {
            $tenants = Tenant::all();
            return response()->json([
                'success' => true,
                'message' => 'Consulta realizada com sucesso.',
                'data' => $tenants
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar: ' . $e->getMessage()
            ], 500);
        }
    }
}
