<?php

namespace App\Notifications;

use App\Broadcasting\DatabaseChannel;
use App\Contracts\DatabaseNotifiable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

// O padrão do Laravel não exige interface extra para database.
class UserNotification extends Notification implements ShouldQueue, DatabaseNotifiable
{
    use Queueable;

    public array $data; // Tipagem forte para evitar erros

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        // Força o ID da notificação a ser o UUID que você gerou no Controller
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Retorne a CLASSE do seu canal, não a string 'database'
        return [DatabaseChannel::class, 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        // O Laravel salva isso automaticamente na coluna 'data' em JSON
        return $this->data;
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            // Mantemos o ID original da notificação para rastreio no front
            'notification_id' => $this->id,

            // Dados do negócio
            'resource_id' => $this->data['id'] ?? null, // Ex: id do agendamento
            'title'       => $this->data['title'] ?? 'Nova Notificação',
            'message'     => $this->data['message'],
            'type'        => $this->data['type'] ?? 'info',
            'link'        => $this->data['link'] ?? null,

            // Metadados úteis para o front
            'tenant_id'   => $this->data['tenant_id'] ?? null, // Útil para whitelabel
            'sent_at'     => now()->toIso8601String(), // Formato padrão ISO para JS
            'read'        => false,
        ]);
    }

    /**
     * Define o canal de broadcast.
     */
    public function broadcastOn(): array
    {
        // Alteração importante: Padronize o nome do canal.
        // Se a notificação é para um usuário específico, use o ID dele.
        // Certifique-se que o user_id está vindo no $data ou use $notifiable->id se possível.

        $userId = $this->data['user_id'];

        return [
            new PrivateChannel('barber.' . $userId . '.notifications'),
            // OU se preferir manter seu padrão:
            // new PrivateChannel('barber.' . $userId . '.notifications')
        ];
    }

    /**
     * Opcional: Define o nome do evento para o frontend ouvir.
     * Padrão: Illuminate\Notifications\Events\BroadcastNotificationCreated
     */
    public function broadcastType(): string
    {
        return 'notification.created';
    }
}
