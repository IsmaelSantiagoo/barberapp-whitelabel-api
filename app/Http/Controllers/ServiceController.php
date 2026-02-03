<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $barbershopId = session()->get('barbershop_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            // Valida se a categoria existe E pertence à barbearia logada
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) use ($barbershopId) {
                    $query->where('barbershop_id', $barbershopId);
                }),
            ],
        ]);

        $service = Service::create($validated);

        return response()->json([
            'message' => 'Serviço cadastrado!',
            'data' => $service->load('category')
        ], 201);
    }

    public function update(Request $request, Service $service)
    {
        $barbershopId = session()->get('barbershop_id');

        $validated = $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'duration_minutes' => 'integer|min:1',
            'category_id' => [
                'sometimes',
                Rule::exists('categories', 'id')->where(fn($q) => $q->where('barbershop_id', $barbershopId)),
            ],
        ]);

        $service->update($validated);

        return response()->json(['message' => 'Serviço atualizado!', 'data' => $service]);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Serviço removido.']);
    }
}
