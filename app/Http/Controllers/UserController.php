<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as RulesPassword;

class UserController extends Controller
{

    // função para listar usuários
    public function index()
    {
        try {
            $users = User::all()
                ->makeHidden(['password', 'remember_token'])
                ->where('role', '!=', 'owner'); // ocultar senha e token de lembrança

            return response()->json([
                'success' => true,
                'message' => 'Usuários consultados com sucesso',
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar usuários: ' . $e->getMessage()
            ]);
        }
    }

    // função para alterar a senha do usuário
    public function alterarSenha(Request $request, $id)
    {

        // configurar regras de validação
        $rules = [
            'old_pass' => ['required'],
            'new_pass' => ['required', 'confirmed:confirm_new_pass', RulesPassword::default()],
            'confirm_new_pass' => ['required'],
        ];

        // validação dos dados recebidos
        $validator = Validator::make($request->all(), $rules, [
            'old_pass.required' => 'A senha antiga é obrigatória.',
            'old_pass.password.mixed' => 'A senha deve conter letras maiúsculas, minúsculas, números e símbolos.',

            'new_pass.min' => 'A senha deve ter no mínimo :min caracteres.',
            'new_pass.password.mixed' => 'A senha deve conter letras maiúsculas, minúsculas, números e símbolos.',
            'new_pass.confirmed' => 'As senhas não coincidem.',

            'confirm_new_pass.required' => 'A confirmação da senha é obrigatória.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        // lógica para alterar a senha do usuário com o ID fornecido
        try {
            // encontrar usuário pelo ID
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ]);
            }

            // validar se a senha antiga está correta
            if (!Hash::check($request->old_pass, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A senha antiga está incorreta.'
                ]);
            }

            // alterar primeiro_acesso para false se for true
            if ($user->primeiro_acesso) {
                $user->primeiro_acesso = false;
            }

            // atualizar senha do usuário
            $user->password = Hash::make($request->new_pass);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar a senha: ' . $e->getMessage()
            ]);
        }
    }

    // função para alterar imagem do usuário
    public function alterarImagem(Request $request, $id)
    {
        // validação básica
        $request->validate([
            'imagem' => 'required|image|max:4096', // max 4MB
        ]);

        // buscar usuário
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        if (!$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma imagem foi enviada.'
            ], 400);
        }

        $file = $request->file('image');

        try {
            // salva no disco 'public' dentro da pasta images e gera nome único automaticamente
            // $path example: "images/abcd1234efgh.jpg"
            $path = $file->store('images', 'public');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao salvar a imagem.'
                ], 500);
            }

            // remover imagem antiga se existir e não for URL externa (como ui-avatars)
            if ($user->profile_photo && !$this->isExternalUrl($user->profile_photo)) {
                $oldPath = $this->extractStoragePath($user->profile_photo);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // gera a URL pública completa (ex: https://api.exemplo.com/storage/images/abc.jpg)
            $urlRelativa = Storage::url($path);
            $url = config('app.public_url') . $urlRelativa;

            // salva no banco (salva a URL pública completa)
            $user->profile_photo = $url;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Imagem alterada com sucesso.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload da imagem: ' . $e->getMessage()
            ], 500);
        }
    }

    // função para remover imagem de perfil do usuário
    public function removerImagem($id)
    {
        // buscar usuário
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        // remover imagem antiga se existir e não for URL externa (como ui-avatars)
        try {
            if ($user->profile_photo && !$this->isExternalUrl($user->profile_photo)) {
                $oldPath = $this->extractStoragePath($user->profile_photo);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // remove a foto de perfil (o mutator irá definir a imagem padrão automaticamente)
            $user->profile_photo = '';
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'Imagem removida com sucesso.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover a imagem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica se a URL é externa (não pertence ao storage local)
     */
    private function isExternalUrl($url)
    {
        // Verifica se é uma URL de serviço externo (ui-avatars, gravatar, etc)
        $externalDomains = ['ui-avatars.com'];

        foreach ($externalDomains as $domain) {
            if (strpos($url, $domain) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extrai o caminho relativo do storage a partir de uma URL completa ou path
     */
    private function extractStoragePath($url)
    {
        // Se for URL completa, extrai apenas o path
        $path = parse_url($url, PHP_URL_PATH);

        if (!$path) {
            $path = $url;
        }

        // Remove o /storage/ do início se existir
        if (strpos($path, '/storage/') !== false) {
            $path = substr($path, strpos($path, '/storage/') + strlen('/storage/'));
        }

        // Remove barras do início
        $path = ltrim($path, '/');

        return $path;
    }

    /**
     * Listar favoritos
     *
     * Retorna a lista de menus favoritos do usuário autenticado.
     */
    public function getFavoriteMenus(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        /** @var User $user Usuário autenticado */
        $user = $request->user();

        $favoritos = $user
            ->favorite_menus()
            ->with('parent_menu_id')
            ->get()
            ->setHidden(['pivot'])
        ;

        return response()->json([
            'success' => true,
            'data' => $favoritos,
        ]);
    }

    /**
     * Recebe o ID do menu e alterna seu status de favorito para o usuário autenticado.
     * Se o menu já estiver favoritado, ele será desfavoritado, e vice-versa.
     */
    public function favoriteMenu(Request $request, Menu $menu)
    {
        try {
            /** @var User $user Usuário autenticado */
            $user = $request->user();

            $result = $user
                ->favorite_menus()
                ->toggle($menu->id)
            ;

            $menu->load('menu_pai');

            $message = !empty($result['attached'])
                ? 'Menu favoritado com sucesso.'
                : 'Menu desfavoritado com sucesso.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'favorito' => !empty($result['attached']),
                    'menu' => $menu,
                ],
            ]);
        }
        catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao favoritar/desfavoritar o menu.',
                'debug_error' => $th->getMessage(),
            ], 500);
        }
    }
}
