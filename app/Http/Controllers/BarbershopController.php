<?php

namespace App\Http\Controllers;

use App\Models\Barbershop;

class BarbershopController extends Controller
{
    // listar todas as barbearias
    public function index()
    {
        try {
            $barbershops = Barbershop::with('businessHours')->get();
            return response()->json([
                'success' => true,
                'message' => 'Consulta realizada com sucesso.',
                'data' => $barbershops
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar: ' . $e->getMessage()
            ], 500);
        }
    }
}
