<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public Lead $lead,
        public int $lockDays,
        public string $endAt
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $customerName = $this->lead->customer->name ?? 'Customer';
        return (new MailMessage)
            ->subject('Visit confirmed – Lead locked')
            ->line("Visit confirmed! {$customerName} locked for {$this->lockDays} days till {$this->endAt}.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'visit_confirmed',
            'lead_id' => $this->lead->id,
            'customer_name' => $this->lead->customer->name ?? null,
            'lock_days' => $this->lockDays,
            'lock_end_at' => $this->endAt,
        ];
    }
}
