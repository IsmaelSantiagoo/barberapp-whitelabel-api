<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use App\Notifications\UserNotification;
// use App\Services\GoogleAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Monta array de dados do usuário para retorno na autenticação.
     *
     * @param  Usuarios  $user  Usuário autenticado
     * @return array Dados do usuário
     */
    private function userDataArray($user): array
    {
        return [
            'id' => $user->id,
            'nome' => $user->nome,
            'email' => $user->email,
            'avatar' => $user->foto_perfil,
            'tipo' => $user->tipo,
        ];
    }

    /**
     * Gera e retorna um token único para o usuário, removendo tokens antigos.
     *
     * @param  Usuarios  $user  Usuário para o qual gerar o token
     * @param  string  $tokenName  Nome do token (padrão: 'web_auth')
     * @return string Token gerado
     */
    private function generateToken($user, $tokenName = 'web_auth'): string
    {
        $user->tokens()->where('name', $tokenName)->delete();

        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Autenticação do usuário básica.
     *
     * @param  Request  $request  Dados da requisição
     * @return JsonResponse $JsonResponse      Token de autenticação e dados do usuário
     */
    public function login(Request $request)
    {
        // Valida os dados enviados pelo usuário
        $payload = $request->validate([
            'email' => ['required', 'string'],
            'senha' => ['required', 'string'],
        ]);

        // Busca o usuário pelo campo 'email'
        $user = Usuarios::query()
            ->where('email', $payload['email'])
            ->first()
        ;

        // Verifica se o usuário existe e está ativo
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado. Tente novamente ou contate o administrador.',
                'debug_error' => 'Usuário não encontrado ou senha inválida.',
            ], 401);
        }

        // Validação de senha segura
        if (!Hash::check($payload['senha'], $user->senha)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        // Define o nome do token conforme o ambiente e o gera
        $tokenName = (config('app.env') !== 'production' && str_starts_with($request->userAgent(), 'PostmanRuntime/')) ? 'postman_auth' : 'web_auth';
        $token = $this->generateToken($user, $tokenName);

        // gerar uuid para a notificação
        $notificationId = 'welcome-notify';

        // notificação
        $notification = [
            'id' => $notificationId,
            'titulo' => 'Bem-vindo ao nosso sistema de gestão, ' . ucwords(strtolower($user->nome)) . '!',
            'mensagem' => 'Estamos felizes em tê-lo(a) conosco. Explore nossos recursos e aproveite ao máximo sua experiência.',
            'tipo' => 'info',
            'usuario_id' => $user->id,
        ];

        // notificação de boas vindas, só aparece se a notificação ainda não foi enviada
        if (!$user->notificacoes()->where('id', $notificationId)->exists()) {
            // enviar notificação
            $user->notify(new UserNotification($notification));
        }

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso.',
            'data' => [
                'token' => $token,
                'usuario' => $this->userDataArray($user),
            ],
        ]);
    }

    /**
     * Registrar novo usuário
     *
     * Cria um usuário e retorna o token JWT
     */
    public function register(Request $request)
    {
        // Valida os dados relativos ao usuário na requisição
        $validator = Validator::make($request->all(), Usuarios::createRules(), Usuarios::messages());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao validar os dados do usuario.',
                'debug_errors' => $validator->errors(),
            ], 422);
        }

        Usuarios::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'senha' => $request->senha,
            'foto_perfil' => $request->avatar,
            'status' => $request->status ?? true,
            'tipo' => $request->tipo,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuário registrado com sucesso.',
        ]);
    }

    /**
     * Logout
     *
     * Invalida o token de acesso do usuário
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.',
        ]);
    }
}
