<?php

namespace App\Http\Controllers;

use App\Models\BusinessHour;
use Illuminate\Http\Request;

class BusinessHourController extends Controller
{
    public function index()
    {
        try {
            // Retorna apenas os horários da barbearia logada (graças à Trait)
            $businessHours = BusinessHour::orderBy('day_of_week')->get();
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
            $request->validate([
                'day_of_week' => 'required|integer|min:0|max:6',
                'open_time' => 'required|date_format:H:i',
                'close_time' => 'required|date_format:H:i|after:open_time',
                'is_open' => 'boolean'
            ]);

            $businessHours = BusinessHour::create([
                'day_of_week' => $request->day_of_week,
                'open_time' => $request->open_time . ':00',
                'close_time' => $request->close_time . ':00',
                'is_open' => $request->is_open ?? true
            ]);

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

    /**
     * Atualiza ou cria os horários de funcionamento em lote
     */
    public function upsert(Request $request)
    {
        try {
            $request->validate([
                'hours' => 'required|array|size:7',
                'hours.*.day_of_week' => 'required|integer|min:0|max:6',
                'hours.*.open_time' => 'required|string',
                'hours.*.close_time' => 'required|string',
                'hours.*.is_open' => 'required|boolean'
            ]);

            $barbershopId = session()->get('barbershop_id');
            $updatedHours = [];

            foreach ($request->hours as $hour) {
                $openTime = strlen($hour['open_time']) === 5
                    ? $hour['open_time'] . ':00'
                    : $hour['open_time'];
                $closeTime = strlen($hour['close_time']) === 5
                    ? $hour['close_time'] . ':00'
                    : $hour['close_time'];

                $businessHour = BusinessHour::updateOrCreate(
                    [
                        'barbershop_id' => $barbershopId,
                        'day_of_week' => $hour['day_of_week']
                    ],
                    [
                        'open_time' => $openTime,
                        'close_time' => $closeTime,
                        'is_open' => $hour['is_open']
                    ]
                );

                $updatedHours[] = $businessHour;
            }

            return response()->json([
                'success' => true,
                'message' => 'Horários atualizados com sucesso!',
                'data' => $updatedHours
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar horários: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, BusinessHour $businessHour)
    {
        try {
            $request->validate([
                'open_time' => 'date_format:H:i',
                'close_time' => 'date_format:H:i|after:open_time',
                'is_open' => 'boolean'
            ]);

            $data = [];
            if ($request->has('open_time')) {
                $data['open_time'] = $request->open_time . ':00';
            }
            if ($request->has('close_time')) {
                $data['close_time'] = $request->close_time . ':00';
            }
            if ($request->has('is_open')) {
                $data['is_open'] = $request->is_open;
            }

            $businessHour->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Horário atualizado com sucesso!',
                'data' => $businessHour
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar horário: ' . $e->getMessage()
            ], 500);
        }
    }
}
