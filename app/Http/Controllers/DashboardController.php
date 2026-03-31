<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function todayAppointments(Request $request)
    {
        $query = Appointment::whereDate('date', now()->toDateString())
            ->with(['service', 'customer']);

        // filtrar por status se informado
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $todayAppointments = $query
            ->orderByRaw("CASE WHEN status = '2' THEN 1 ELSE 0 END")
            ->orderBy('time', 'asc')
            ->get();

        // contadores (sempre sem filtro para os cards)
        $allAppointments = Appointment::whereDate('date', now()->toDateString())->get();
        $stats = [
            'today' => $allAppointments->whereIn('status', ['0', '1'])->count(),
            'pending' => $allAppointments->where('status', '0')->count(),
            'confirmed' => $allAppointments->where('status', '1')->count(),
            'cancelled' => $allAppointments->where('status', '2')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Agendamentos de hoje consultados com sucesso.',
            'data' => $todayAppointments,
            'stats' => $stats,
        ]);
    }
}
