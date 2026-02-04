<?php

namespace App\Http\Controllers;

use App\Models\Appointment;

class DashboardController extends Controller
{
    public function todayAppointments()
    {

        // consultando agendas de hoje
        $todayAppointments = Appointment::whereDate('date', now()->toDateString())
            ->with(['service', 'customer'])
            ->orderByRaw("CASE WHEN status = '2' THEN 1 ELSE 0 END")
            ->orderBy('time', 'asc')
            ->get();

        // retornando resposta
        return response()->json([
            'success' => true,
            'message' => 'Agendamentos de hoje consultados com sucesso.',
            'data' => $todayAppointments
        ]);
    }
}
