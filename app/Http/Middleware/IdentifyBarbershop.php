<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Barbershop;
use Illuminate\Http\Request;

class IdentifyBarbershop
{
    public function handle(Request $request, Closure $next)
    {
        // Tenta pegar pelo Header (útil para App Mobile/Postman)
        $slug = $request->header('X-Barbershop-Slug');

        // Se não tiver header, tenta pegar pelo subdomínio (útil para Web)
        // Lógica simples de exemplo: extrair 'loja1' de 'loja1.meusite.com'
        if (!$slug) {
            $host = $request->getHost();
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $slug = $parts[0];
            }
        }

        $barbershop = Barbershop::where('slug', $slug)->first();

        if (!$barbershop) {
            return response()->json(['message' => 'Barbearia não encontrada.'], 404);
        }

        // Salva o ID na sessão ou num Singleton para usar na Trait
        session()->put('barbershop_id', $barbershop->id);

        // Opcional: injetar o objeto barbershop na requisição para fácil acesso
        $request->merge(['barbershop' => $barbershop]);

        return $next($request);
    }
}
