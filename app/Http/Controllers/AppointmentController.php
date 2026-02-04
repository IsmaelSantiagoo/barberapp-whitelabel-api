<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BusinessHour;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        try {
            // Retorna apenas as agendas da barbearia logada (graças à Trait)
            $appointments = Appointment::with('service')->get();
            return response()->json([
                'success' => true,
                'message' => 'Agendas consultadas com sucesso!',
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar agendas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
                'service_id' => 'required|exists:services,id',
            ]);

            // Verificar se o horário está disponível
            $this->validateBusinessHours($request);

            // Verificar se já existe agendamento para esse horário
            $existingAppointment = Appointment::where('date', $request->date)
                ->where('time', $request->time)
                ->whereIn('status', ['0', '1']) // 0 = pendente, 1 = confirmado
                ->first();

            if ($existingAppointment) {
                throw new \Exception('Este horário já está agendado. Por favor, escolha outro horário.');
            }

            // O barbershop_id é preenchido automaticamente pela Trait no evento 'creating'
            $appointment = Appointment::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Horário agendado com sucesso!',
                'data' => $appointment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao agendar horário: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Appointment $appointment)
    {
        // Se a agenda não pertencer à barbearia logada,
        // a Trait fará o Laravel retornar 404 automaticamente
        return response()->json($appointment->load('services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        try {
            $request->validate([
                'date' => 'date',
                'time' => 'date_format:H:i',
                'notes' => 'string|nullable'
            ]);

            // Verificar se o horário está disponível ao atualizar
            if ($request->has('date') || $request->has('time')) {
                $this->validateBusinessHours($request);
            }

            $appointment->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Agenda atualizada!',
                'data' => $appointment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar agenda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirm(Appointment $appointment)
    {
        try {
            $appointment->update(['status' => '1']);
            return response()->json([
                'success' => true,
                'message' => 'Agenda confirmada.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar agenda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Appointment $appointment)
    {
        try {
            $appointment->update(['status' => '2']);
            return response()->json([
                'success' => true,
                'message' => 'Agenda cancelada.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar agenda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca os agendamentos do cliente logado na barbearia atual
     */
    public function getByClient()
    {
        try {
            $user = Auth::user();

            $appointments = Appointment::with('service')
                ->where('customer_id', $user->id)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Agendamentos do cliente consultados com sucesso!',
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar agendamentos do cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valida se o horário está disponível segundo os business_hours
     */
    private function validateBusinessHours(Request $request)
    {
        $date = Carbon::parse($request->input('date'));
        $time = $request->input('time');
        $dayOfWeek = $date->dayOfWeek;

        // Obter o barbershop_id da autenticação (da Trait)
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('Usuário não autenticado');
        }

        $barbershopId = $user->barbershop_id ?? null;

        if (!$barbershopId) {
            throw new \Exception('Barbearia não identificada');
        }

        // Verificar se existe business_hours para este dia
        $businessHour = BusinessHour::where('barbershop_id', $barbershopId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        $dayName = $this->getDayNameInPortuguese($dayOfWeek);

        if (!$businessHour) {
            throw new \Exception('A barbearia não funciona ' . $dayName);
        }

        if (!$businessHour->is_open) {
            throw new \Exception('A barbearia está fechada ' . $dayName);
        }

        // Comparar o horário solicitado com os horários de abertura/fechamento
        $requestTime = Carbon::createFromFormat('H:i', $time);
        $openTime = Carbon::createFromFormat('H:i:s', $businessHour->open_time);
        $closeTime = Carbon::createFromFormat('H:i:s', $businessHour->close_time);

        if ($requestTime->lt($openTime) || $requestTime->gt($closeTime)) {
            throw new \Exception(
                'Horário indisponível. A barbearia funciona de ' .
                    $businessHour->open_time . ' às ' . $businessHour->close_time
            );
        }
    }

    /**
     * Traduz o número do dia da semana para o nome em português
     */
    private function getDayNameInPortuguese(int $dayOfWeek): string
    {
        $days = [
            0 => 'domingo',
            1 => 'segunda-feira',
            2 => 'terça-feira',
            3 => 'quarta-feira',
            4 => 'quinta-feira',
            5 => 'sexta-feira',
            6 => 'sábado',
        ];

        return $days[$dayOfWeek] ?? 'dia desconhecido';
    }
}
