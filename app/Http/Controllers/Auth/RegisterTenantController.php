<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterTenantController extends Controller
{
    public function store(StoreTenantRequest $request)
    {
        return DB::transaction(function () use ($request) {

            // 1. Gerar Slug se não vier (ex: "Barbearia do Zé" -> "barbearia-do-ze")
            $slug = $request->slug ?? Str::slug($request->company_name);

            // Verifica duplicidade básica de slug gerado
            if (Tenant::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . rand(100, 999);
            }

            // 2. Criar o Tenant (Barbearia)
            $tenant = Tenant::create([
                'company_name' => $request->company_name,
                'slug' => $slug,
                'primary_color' => $request->primary_color ?? '#000000',
            ]);

            // 3. Criar o Usuário Dono vinculado ao Tenant
            // Nota: Como estamos criando via relação, o tenant_id é preenchido automático
            $user = $tenant->users()->create([
                'name' => $request->owner_name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'owner',
            ]);

            // 4. (Opcional) Criar dados iniciais para o cliente não começar do zero
            $this->createDefaultCategories($tenant);

            // 5. Gerar Token (Login automático após registro)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Barbearia cadastrada com sucesso!',
                'data' => [
                    'tenant' => $tenant,
                    'user' => $user,
                ],
                'access_token' => $token
            ], 201);
        });
    }

    /**
     * Cria categorias padrão para a nova barbearia
     */
    private function createDefaultCategories(Tenant $tenant)
    {
        // Criando a categoria
        $category = $tenant->categories()->create([
            'name' => 'Cortes Clássicos',
        ]);

        // Criando o serviço dentro da categoria
        $category->services()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Corte Social',
            'price' => 35.00,
            'duration_minutes' => 30
        ]);
    }
}
