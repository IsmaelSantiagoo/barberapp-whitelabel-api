<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Barbershop;
use Illuminate\Http\Request;

class IdentifyBarbershop
{
    public function handle(Request $request, Closure $next)
    {
        // Obtém o barbershop_id do header
        $barbershopId = $request->header('X-Barbershop-ID');

        if (!$barbershopId) {
            return response()->json(['message' => 'Barbearia não identificada. Header X-Barbershop-ID é obrigatório.'], 400);
        }

        $barbershop = Barbershop::find($barbershopId);

        if (!$barbershop) {
            return response()->json(['message' => 'Barbearia não encontrada.'], 404);
        }

        // Salva o ID na sessão para usar na Trait
        session()->put('barbershop_id', $barbershop->id);

        // Injetar o objeto barbershop na requisição para fácil acesso
        $request->merge(['barbershop' => $barbershop]);

        return $next($request);
    }
}
