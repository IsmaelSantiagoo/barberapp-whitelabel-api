<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarbershopRequest;
use App\Models\Barbershop;
use Illuminate\Support\Facades\DB;

class RegisterBarbershopController extends Controller
{
    public function store(StoreBarbershopRequest $request)
    {
        return DB::transaction(function () use ($request) {

            // 2. Criar a Barbearia
            $barbershop = Barbershop::create([
                'company_name' => $request->company_name,
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
            $this->createDefaultServices($barbershop);

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
     * Cria serviços padrão para a nova barbearia
     */
    private function createDefaultServices(Barbershop $barbershop)
    {

        // Criando o serviço default
        $barbershop->services()->create([
            'barbershop_id' => $barbershop->id,
            'name' => 'Corte Social',
            'price' => 50.00,
            'duration_minutes' => 45
        ]);
    }
}
