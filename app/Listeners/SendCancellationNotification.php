<?php

namespace App\Listeners;

use App\Events\AppointmentCancelled;
use App\Events\NotificationBroadcast;
use App\Models\User;
use App\Notifications\AppointmentCancelledNotification;
use Illuminate\Support\Facades\Notification;

class SendCancellationNotification
{
  public function __construct()
  {
    //
  }

  private function getFormattedCustomerName($name): string
  {
    return ucwords(mb_strtolower($name));
  }

  public function handle(AppointmentCancelled $event): void
  {
    $appointment = $event->appointment;

    $appointment->load(['customer', 'service', 'barbershop']);

    $admins = User::where('barbershop_id', $appointment->barbershop_id)
      ->whereIn('role', ['admin', 'owner'])
      ->get();

    $broadcastData = [
      'id' => \Illuminate\Support\Str::uuid()->toString(),
      'type' => 'error',
      'title' => 'Agendamento Cancelado',
      'message' => "{$this->getFormattedCustomerName($appointment->customer->name)} cancelou o agendamento de {$appointment->service->name}",
      'appointment_id' => $appointment->id,
      'customer_name' => $appointment->customer->name,
      'service_name' => $appointment->service->name,
      'date' => $appointment->date,
      'time' => $appointment->time,
      'status' => '2',
    ];

    Notification::send($admins, new AppointmentCancelledNotification($appointment));

    foreach ($admins as $admin) {
      event(new NotificationBroadcast($admin->id, $broadcastData));
    }
  }
}
