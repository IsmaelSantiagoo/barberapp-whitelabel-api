<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // Listar todos os menus
    public function read()
    {
        $menus = Menu::whereNull('parent_menu_id')->get();

        // armazena submenus em cada menu
        foreach ($menus as $menu) {
            $subMenus = Menu::where('parent_menu_id', $menu->id)->get();
            $menu->submenus = $subMenus;
        }

        try {
            return response()->json([
                'success' => true,
                'message' => 'Consulta de menus realizada com sucesso.',
                'data' => $menus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar menus.'
            ], 500);
        }
    }

    // Criar novo menu
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'icon' => 'nullable|string',
            'route' => 'nullable|string',
            'order' => 'nullable|integer',
            'parent_menu_id' => 'nullable|integer|exists:menus,id',
            'responsible_user' => 'required|integer|exists:users,id',
        ]);

        // validação de cadastro completo
        try {
            // Verifica se já existe menu com o mesmo título
            $existe = Menu::where('title', $request->title)->first();
            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu já cadastrado.'
                ], 400);
            }
            Menu::create([
                'title' => $request->title,
                'icon' => $request->icon,
                'route' => $request->route,
                'order' => $request->order,
                'parent_menu_id' => $request->parent_menu_id,
                'responsible_user' => $request->responsible_user,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Menu cadastrado com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar menu.'
            ], 500);
        }
    }

    // Exibir um menu específico
    public function show($id)
    {
        $menu = Menu::find($id);

        try {
            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu não encontrado.',
                ], 404);
            }
            // dados do menu formatados
                $menuArray = $menu->toArray();
                $menuArray['responsible_user'] = $menu->responsible_user;
                return response()->json([
                    'success' => true,
                    'message' => 'Menu encontrado com sucesso.',
                    'data' => $menuArray
                ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar menu.',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    // Atualizar um menu
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'icon' => 'nullable|string',
            'route' => 'nullable|string',
            'order' => 'nullable|integer',
            'parent_menu_id' => 'nullable|integer|exists:menus,id',
            'responsible_user' => 'required|integer|exists:users,id',
        ]);

        $menu = Menu::find($id);
        try {
            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu não encontrado para atualização.',
                ], 404);
            }
                $menu->update([
                    'title' => $request->title,
                    'icon' => $request->icon,
                    'route' => $request->route,
                    'order' => $request->order,
                    'parent_menu_id' => $request->parent_menu_id,
                    'responsible_user' => $request->responsible_user,
                ]);
            return response()->json([
                'success' => true,
                'message' => 'Menu atualizado com sucesso.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar menu.',
                'data' => $e->getMessage()
            ], 400);
        }
    }

    // Deletar um menu
    public function delete($id)
    {
        $menu = Menu::find($id);
        try {
            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu não encontrado para exclusão.',
                ], 404);
            }
            $menu->delete();
            return response()->json([
                'success' => true,
                'message' => 'Menu deletado com sucesso.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar menu.',
                'data' => $e->getMessage()
            ], 400);
        }
    }
}
