<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinancialAlert extends Notification
{
    use Queueable;

    protected $alertData;

    public function __construct(array $alertData)
    {
        $this->alertData = $alertData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->alertData['type'] ?? 'info',
            'icon' => $this->alertData['icon'] ?? 'info-circle',
            'title' => $this->alertData['title'] ?? 'Notifikasi',
            'message' => $this->alertData['message'] ?? '',
            'severity' => $this->alertData['severity'] ?? 'info',
            'action_url' => $this->alertData['action_url'] ?? null,
            'data' => $this->alertData['data'] ?? [],
        ];
    }
}
