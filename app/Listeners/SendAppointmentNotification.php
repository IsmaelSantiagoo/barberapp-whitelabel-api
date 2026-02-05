<?php

namespace App\Listeners;

use App\Events\AppointmentScheduled;
use App\Events\NotificationBroadcast;
use App\Models\User;
use App\Notifications\NewAppointmentNotification;
use Illuminate\Support\Facades\Notification;

class SendAppointmentNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get customer name with first letters capitalized
     */
    private function getFormattedCustomerName($name): string
    {
        return ucwords(mb_strtolower($name));
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentScheduled $event): void
    {
        $appointment = $event->appointment;

        // Carregar relacionamentos necessários
        $appointment->load(['customer', 'service', 'barbershop']);

        // Buscar todos os usuários admin/owner da barbearia
        $admins = User::where('barbershop_id', $appointment->barbershop_id)
            ->whereIn('role', ['admin', 'owner'])
            ->get();

        // Preparar dados para broadcast
        $broadcastData = [
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'success',
            'title' => 'Novo Agendamento',
            'message' => "Novo agendamento de {$this->getFormattedCustomerName($appointment->customer->name)} para {$appointment->service->name}",
            'appointment_id' => $appointment->id,
            'customer_name' => $appointment->customer->name,
            'service_name' => $appointment->service->name,
            'date' => $appointment->date,
            'time' => $appointment->time,
            'status' => $appointment->status,
        ];

        // Enviar notificação para todos os admins (salva no banco)
        Notification::send($admins, new NewAppointmentNotification($appointment));

        // Disparar evento de broadcast para cada admin (em tempo real via WebSocket)
        foreach ($admins as $admin) {
            event(new NotificationBroadcast($admin->id, $broadcastData));
        }
    }
}
