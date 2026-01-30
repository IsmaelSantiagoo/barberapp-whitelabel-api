<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{

    /**
     * Registro de novos usuários (Barbeiros/Staff) vinculados ao Tenant atual
     */
    public function register(Request $request): JsonResponse
    {
        // 1. Validamos usando as regras e mensagens definidas na sua Model User
        $validator = Validator::make(
            $request->all(),
            User::createRules(),
            User::messages()
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Criamos o usuário
        // Nota: tenant_id será preenchido automaticamente pela Trait BelongsToTenant
        // se a rota estiver protegida pelo middleware identify.tenant.
        // Os Mutators na Model cuidarão do Hash da senha e lowercases.
        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password), // Mutator faz o Hash automático
            'role'          => $request->role ?? 'user',
            'profile_photo' => $request->profile_photo, // Mutator gera avatar se for null
            'first_access'  => true,
        ]);

        // 3. Geramos o Token de acesso imediato
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuário registrado com sucesso!',
            'data' => [
                'user' => $user,
                'access_token' => $token,
            ]
        ], 201);
    }

    /**
     * Autentica o usuário e retorna o Token + Dados da Barbearia
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);

        // Buscamos o usuário pelo email (o mutador garante que o email no banco é lowercase)
        $user = User::with('tenant')
            ->where('email', mb_strtolower($request->email))
            ->first();

        // Verificação de segurança
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais informadas estão incorretas.'],
            ]);
        }

        // Remove tokens antigos para evitar múltiplas sessões (opcional)
        $user->tokens()->delete();

        // Gera o token via Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'data' => [
                'user' => $user->makeHidden('tenant'), // Esconde a relação para não duplicar no JSON
                'tenant' => $user->tenant, // Retorna os dados da barbearia (slug, cor, etc)
                'access_token' => $token,
            ]
        ]);
    }

    /**
     * Retorna os dados do usuário logado (Check Me)
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('tenant');

            return response()->json([
                'success' => true,
                'message' => 'Dados do usuário autenticado',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter os dados do usuário autenticado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoga o token e encerra a sessão
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sessão encerrada com sucesso'
        ]);
    }
}
