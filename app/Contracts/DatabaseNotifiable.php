<?php

namespace App\Contracts;

interface DatabaseNotifiable
{
    /**
     * Get the Database representation of the notification.
     */
    public function toDatabase(object $notifiable): array;
}
