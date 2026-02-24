<?php

namespace App\Notifications;

use App\Models\BuilderFirm;
use App\Models\CpApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CpApplicationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public BuilderFirm $builder,
        public CpApplication $cpApplication,
        public string $notes
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $builderName = $this->builder->name ?? 'Builder';
        $message = (new MailMessage)
            ->subject("Application update – {$builderName}")
            ->line("Your channel partner application for {$builderName} has been reviewed and was not approved.")
            ->line('Reason: ' . $this->notes);

        $fromAddress = $this->builder->getMailFromAddress();
        if ($fromAddress) {
            $message->from($fromAddress, $this->builder->getMailFromName());
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cp_application_rejected',
            'builder_firm_id' => $this->builder->id,
            'builder_name' => $this->builder->name,
            'cp_application_id' => $this->cpApplication->id,
            'notes' => $this->notes,
        ];
    }
}
