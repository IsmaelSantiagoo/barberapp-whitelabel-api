<?php

namespace App\Broadcasting;

use App\Contracts\DatabaseNotifiable;
use App\Models\Notification;

class DatabaseChannel
{
    public function send($notifiable, $notification)
    {
        if (!$notification instanceof DatabaseNotifiable) {
            throw new \Exception('Notificação não implementa DatabaseNotifiable');
        }

        $data = $notification->toDatabase($notifiable);

        if (empty($data)) {
            return;
        }

        // O UUID deve vir do Controller, se não vier, lance erro
        if (!array_key_exists('id', $data) || empty($data['id'])) {
            throw new \Exception('Notificação sem ID (UUID). Certifique-se de passar o campo "id" ao criar a notificação.');
        }

        Notification::create([
            'id' => $data['id'],
            'title' => $data['title'] ?? null,
            'message' => $data['message'] ?? null,
            'type' => $data['type'] ?? null,
            'link' => $data['link'] ?? null,
            'user_id' => $data['user_id'] ?? $notifiable->getKey(),
            'menu_id' => $data['menu_id'] ?? null,
        ]);
    }
}
