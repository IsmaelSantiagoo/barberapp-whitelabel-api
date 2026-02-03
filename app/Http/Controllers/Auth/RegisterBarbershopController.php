<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarbershopRequest;
use App\Models\Barbershop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterBarbershopController extends Controller
{
    public function store(StoreBarbershopRequest $request)
    {
        return DB::transaction(function () use ($request) {

            // 1. Gerar Slug se não vier (ex: "Barbearia do Zé" -> "barbearia-do-ze")
            $slug = $request->slug ?? Str::slug($request->company_name);

            // Verifica duplicidade básica de slug gerado
            if (Barbershop::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . rand(100, 999);
            }

            // 2. Criar a Barbearia
            $barbershop = Barbershop::create([
                'company_name' => $request->company_name,
                'slug' => $slug,
                'primary_color' => $request->primary_color ?? '#000000',
            ]);

            // 3. Criar o Usuário Dono vinculado à Barbearia
            $user = $barbershop->users()->create([
                'name' => $request->owner_name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'owner',
            ]);

            // 4. Criar dados iniciais para o cliente não começar do zero
            $this->createDefaultCategories($barbershop);

            // 5. Criar horários de funcionamento padrão
            $barbershop->createDefaultBusinessHours();

            // 6. Gerar Token (Login automático após registro)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Barbearia cadastrada com sucesso!',
                'data' => [
                    'barbershop' => $barbershop->load('businessHours'),
                    'user' => $user,
                ],
                'access_token' => $token
            ], 201);
        });
    }

    /**
     * Cria categorias padrão para a nova barbearia
     */
    private function createDefaultCategories(Barbershop $barbershop)
    {
        // Criando a categoria
        $category = $barbershop->categories()->create([
            'name' => 'Cortes Clássicos',
        ]);

        // Criando o serviço dentro da categoria
        $category->services()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Corte Social',
            'price' => 35.00,
            'duration_minutes' => 30
        ]);
    }
}
