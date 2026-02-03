<?php

namespace App\Http\Controllers;

use App\Models\BusinessHour;
use Illuminate\Http\Request;

class BusinessHourController extends Controller
{
    public function index()
    {
        try {
            // Retorna apenas as agendas da barbearia logada (graças à Trait)
            $businessHours = BusinessHour::get();
            return response()->json([
                'success' => true,
                'message' => 'Horários comerciais consultados com sucesso!',
                'data' => $businessHours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar horários comerciais: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Lógica para armazenar horários comerciais
            $request->validate([
                'day_of_week' => 'required|integer|min:0|max:6',
                'open_time' => 'required|date_format:H:i',
                'close_time' => 'required|date_format:H:i|after:open_time',
            ]);

            $businessHours = BusinessHour::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Horários comerciais armazenados com sucesso!',
                'data' => $businessHours
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao armazenar horários comerciais: ' . $e->getMessage()
            ], 500);
        }
    }
}
