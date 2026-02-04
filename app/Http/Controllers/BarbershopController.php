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

    // lista uma barbearia pelo slug
    public function find($barbershop_slug)
    {
        try {
            $barbershop = Barbershop::with('businessHours')->where('slug', $barbershop_slug)->first();
            return response()->json([
                'success' => true,
                'message' => 'Consulta realizada com sucesso.',
                'data' => $barbershop
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar: ' . $e->getMessage()
            ], 500);
        }
    }

    // atualizar dados da barbearia
    public function update(Barbershop $barbershop)
    {
        try {
            $barbershop->update(request()->only([
                'company_name',
                'address',
                'phone',
                'instagram',
                'email',
            ]));
            return response()->json([
                'success' => true,
                'message' => 'Barbearia atualizada com sucesso.',
                'data' => $barbershop
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar: ' . $e->getMessage()
            ], 500);
        }
    }
}
