<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Tenta pegar pelo Header (útil para App Mobile/Postman)
        $slug = $request->header('X-Tenant-Slug');

        // Se não tiver header, tenta pegar pelo subdomínio (útil para Web)
        // Lógica simples de exemplo: extrair 'loja1' de 'loja1.meusite.com'
        if (!$slug) {
            $host = $request->getHost();
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $slug = $parts[0];
            }
        }

        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            return response()->json(['message' => 'Barbearia não encontrada.'], 404);
        }

        // Salva o ID na sessão ou num Singleton para usar na Trait
        session()->put('tenant_id', $tenant->id);

        // Opcional: injetar o objeto tenant na requisição para fácil acesso
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
