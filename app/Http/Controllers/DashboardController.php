<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


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

    public function getinvoicingByYear(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'year' => 'required|integer'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validate->errors()
            ], 400);
        }

        $year = $request->year;
        $appointments = Appointment::with('service')->whereYear('date', $year)->get();

        $invoicingByMonth = $appointments->where('status', '1')
            ->groupBy(function ($appointment) {
                return Carbon::parse($appointment->date)->format('n');
            })
            ->map(function ($monthGroup) {
                return $monthGroup->sum(function ($appointment) {
                    return optional($appointment->service)->price ?? 0;
                });
            })
            ->toArray();
        
        $formattedData = [];
        for ($m = 1; $m <= 12; $m++) {
            $formattedData[] = [
                // 'M' traz a abreviação de 3 letras (ex: "Jan"). Usamos strtolower para "jan".
                'month' => strtolower(Carbon::create()->month($m)->translatedFormat('M')),
                'total' => $invoicingByMonth[$m] ?? 0
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Faturamento do ano consultado com sucesso.',
            'data' => $formattedData
        ]);
    }
}
