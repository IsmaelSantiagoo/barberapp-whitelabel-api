<?php

namespace App\Notifications;

use App\Broadcasting\DatabaseChannel;
use App\Contracts\DatabaseNotifiable;
use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class NewAppointmentNotification extends Notification implements DatabaseNotifiable
{
    public array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->data = $appointment->load(['customer', 'service'])->toArray();

        // Garantir que status existe
        if (!isset($this->data['status'])) {
            $this->data['status'] = '0'; // Pendente por padrão
        }

        // Gera UUID para a notificação
        $this->id = \Illuminate\Support\Str::uuid()->toString();
    }

    /**
     * Get customer name with first letters capitalized
     */
    private function getFormattedCustomerName(): string
    {
        return ucwords(mb_strtolower($this->data['customer']['name']));
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Apenas salvar no banco
        return [DatabaseChannel::class];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'id' => $this->id,
            'title' => 'Novo Agendamento',
            'type' => 'success',
            'message' => "Novo agendamento de {$this->getFormattedCustomerName()} para {$this->data['service']['name']}",
            'user_id' => $notifiable->id,
            'appointment_id' => $this->data['id'],
            'customer_name' => $this->getFormattedCustomerName(),
            'service_name' => $this->data['service']['name'],
            'date' => $this->data['date'],
            'time' => $this->data['time'],
            'status' => $this->data['status'] ?? '0',
            'barbershop_id' => $this->data['barbershop_id'],
        ];
    }
}
