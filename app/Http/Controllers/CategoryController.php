<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Retorna apenas as categorias da barbearia logada (graças à Trait)
        $categories = Category::with('services')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Categorias consultadas com sucesso!',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // O tenant_id é preenchido automaticamente pela Trait no evento 'creating'
        $category = Category::create($request->all());

        return response()->json([
            'message' => 'Categoria criada com sucesso!',
            'data' => $category
        ], 201);
    }

    public function show(Category $category)
    {
        // Se a categoria não pertencer ao tenant logado,
        // a Trait fará o Laravel retornar 404 automaticamente
        return response()->json($category->load('services'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'string|max:255',
            'active' => 'boolean'
        ]);

        $category->update($request->all());

        return response()->json([
            'message' => 'Categoria atualizada!',
            'data' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Categoria removida.']);
    }
}
