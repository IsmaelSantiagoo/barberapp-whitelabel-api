<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        // lista e filtra serviços
        $services = Service::where(function ($query) use ($request) {

            // filtra por active true ou false
            if ($request->has('active')) {
                $query->where('active', filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN));
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Serviços carregados com sucesso',
            'data' => $services->get()
        ]);
    }

    public function show(Service $service)
    {
        return response()->json([
            'success' => true,
            'data' => $service
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

            $service = Service::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Serviço cadastrado!',
                'data' => $service
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
                'data' => $service->fresh()
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
