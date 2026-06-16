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
        $allYesterdayAppointments = Appointment::with('service')->whereDate('date', now()->subDay()->toDateString())->get();
        $allTodayAppointments = Appointment::with('service')->whereDate('date', now()->toDateString())->get();
        $allMonthAppointments = Appointment::with('service')->whereMonth('date', now()->month)->get();

        $stats = [
            'today' => $allTodayAppointments->whereIn('status', ['0', '1'])->count(),
            'pending' => $allTodayAppointments->where('status', '0')->count(),
            'confirmed' => $allTodayAppointments->where('status', '1')->count(),
            'cancelled' => $allTodayAppointments->where('status', '2')->count(),
        ];

        // resumo financeiro
        $invoicingTrendPorcentage = $allYesterdayAppointments->count() > 0
            ? ($allTodayAppointments->where('status', '1')->sum('service.price') - $allYesterdayAppointments->where('status', '1')->sum('service.price')) / $allYesterdayAppointments->where('status', '1')->sum('service.price') * 100
            : 0;

        $financial_summary = [
            'today_invoicing' => [
                'total' => $allTodayAppointments->where('status', '1')->sum('service.price'),
                'invoincing_trend_porcentage' => $invoicingTrendPorcentage,
                'isFirst' => $allYesterdayAppointments->isEmpty()
            ],
            'month_invoicing' => $allMonthAppointments->where('status', '1')->sum('service.price'),
            'average_ticket' => $allMonthAppointments->where('status', '1')->count() > 0 ? $allMonthAppointments->where('status', '1')->sum('service.price') / $allMonthAppointments->where('status', '1')->count() : 0,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Agendamentos de hoje consultados com sucesso.',
            'data' => $todayAppointments,
            'stats' => $stats,
            'financial_summary' => $financial_summary
        ]);
    }
}
