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
        $tenantId = session()->get('tenant_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            // Valida se a categoria existe E pertence ao tenant logado
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId);
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
        $tenantId = session()->get('tenant_id');

        $validated = $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'duration_minutes' => 'integer|min:1',
            'category_id' => [
                'sometimes',
                Rule::exists('categories', 'id')->where(fn($q) => $q->where('tenant_id', $tenantId)),
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
