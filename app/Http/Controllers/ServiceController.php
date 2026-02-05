<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        // Lista todos os serviços da barbearia com os dados da categoria
        $services = Service::get();
        return response()->json([
            'success' => true,
            'message' => 'Serviços carregados com sucesso',
            'data' => $services
        ]);
    }

    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'duration_minutes' => 'required|integer|min:1',
                'description' => 'nullable|string',
                'active' => 'boolean',
            ]);

            Service::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Serviço cadastrado!',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar o serviço: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Service $service)
    {
        try {

            $validated = $request->validate([
                'name' => 'string|max:255',
                'price' => 'numeric|min:0',
                'duration_minutes' => 'integer|min:1',
                'active' => 'boolean'
            ]);

            $service->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Serviço atualizado!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o serviço: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Service $service)
    {
        try {
            $service->delete();
            return response()->json([
                'success' => true,
                'message' => 'Serviço removido.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover o serviço: ' . $e->getMessage(),
            ], 500);
        }
    }
}
