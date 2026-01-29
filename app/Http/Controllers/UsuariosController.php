<?php

namespace App\Http\Controllers;

use App\Models\Menus;
use App\Models\Usuarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as RulesPassword;

class UsuariosController extends Controller
{

    // função para alterar a senha do usuário
    public function alterarSenha(Request $request, $id)
    {

        // configurar regras de validação
        $rules = [
            'senha_antiga' => ['required'],
            'senha_nova' => ['required', 'confirmed:confirmar_senha_nova', RulesPassword::default()],
            'confirmar_senha_nova' => ['required'],
        ];

        // validação dos dados recebidos
        $validator = Validator::make($request->all(), $rules, [
            'senha_antiga.required' => 'A senha antiga é obrigatória.',
            'senha_antiga.password.mixed' => 'A senha deve conter letras maiúsculas, minúsculas, números e símbolos.',

            'senha_nova.min' => 'A senha deve ter no mínimo :min caracteres.',
            'senha_nova.password.mixed' => 'A senha deve conter letras maiúsculas, minúsculas, números e símbolos.',
            'senha_nova.confirmed' => 'As senhas não coincidem.',

            'confirmar_senha_nova.required' => 'A confirmação da senha é obrigatória.',
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
            $usuario = Usuarios::find($id);

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ]);
            }

            // validar se a senha antiga está correta
            if (!Hash::check($request->senha_antiga, $usuario->senha)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A senha antiga está incorreta.'
                ]);
            }

            // alterar primeiro_acesso para false se for true
            if ($usuario->primeiro_acesso) {
                $usuario->primeiro_acesso = false;
            }

            // atualizar senha do usuário
            $usuario->senha = $request->senha_nova;
            $usuario->save();

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
        $usuario = Usuarios::find($id);
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        if (!$request->hasFile('imagem')) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma imagem foi enviada.'
            ], 400);
        }

        $file = $request->file('imagem');

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
            if ($usuario->foto_perfil && !$this->isExternalUrl($usuario->foto_perfil)) {
                $oldPath = $this->extractStoragePath($usuario->foto_perfil);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // gera a URL pública completa (ex: https://api.exemplo.com/storage/images/abc.jpg)
            $urlRelativa = Storage::url($path);
            $url = config('app.public_url') . $urlRelativa;

            // salva no banco (salva a URL pública completa)
            $usuario->foto_perfil = $url;
            $usuario->save();

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
        $usuario = Usuarios::find($id);
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        // remover imagem antiga se existir e não for URL externa (como ui-avatars)
        try {
            if ($usuario->foto_perfil && !$this->isExternalUrl($usuario->foto_perfil)) {
                $oldPath = $this->extractStoragePath($usuario->foto_perfil);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // remove a foto de perfil (o mutator irá definir a imagem padrão automaticamente)
            $usuario->foto_perfil = '';
            $usuario->save();

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
    public function getMenusFavoritos(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        /** @var Usuarios $user Usuário autenticado */
        $user = $request->user();

        $favoritos = $user
            ->menus_favoritos()
            ->with('menu_pai')
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
    public function favoritarMenu(Request $request, Menus $menu)
    {
        try {
            /** @var Usuarios $user Usuário autenticado */
            $user = $request->user();

            $result = $user
                ->menus_favoritos()
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
