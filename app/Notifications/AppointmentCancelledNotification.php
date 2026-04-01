<?php

namespace App\Notifications;

use App\Broadcasting\DatabaseChannel;
use App\Contracts\DatabaseNotifiable;
use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements DatabaseNotifiable
{
  public array $data;

  public function __construct(Appointment $appointment)
  {
    $this->data = $appointment->load(['customer', 'service'])->toArray();
    $this->id = \Illuminate\Support\Str::uuid()->toString();
  }

  private function getFormattedCustomerName(): string
  {
    return ucwords(mb_strtolower($this->data['customer']['name']));
  }

  public function via(object $notifiable): array
  {
    return [DatabaseChannel::class];
  }

  public function toDatabase(object $notifiable): array
  {
    return [
      'id' => $this->id,
      'title' => 'Agendamento Cancelado',
      'type' => 'error',
      'message' => "{$this->getFormattedCustomerName()} cancelou o agendamento de {$this->data['service']['name']}",
      'user_id' => $notifiable->id,
      'appointment_id' => $this->data['id'],
      'customer_name' => $this->getFormattedCustomerName(),
      'service_name' => $this->data['service']['name'],
      'date' => $this->data['date'],
      'time' => $this->data['time'],
      'status' => '2',
      'barbershop_id' => $this->data['barbershop_id'],
    ];
  }
}
