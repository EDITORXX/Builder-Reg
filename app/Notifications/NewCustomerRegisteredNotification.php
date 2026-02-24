<?php

namespace App\Notifications;

use App\Models\BuilderFirm;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCustomerRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public BuilderFirm $builder,
        public Lead $lead
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $projectName = $this->lead->project?->name ?? 'Project';
        $customerName = $this->lead->customer?->name ?? 'Customer';
        $mobile = $this->lead->customer?->mobile ?? null;

        $message = (new MailMessage)
            ->subject("New customer registration – {$projectName}")
            ->line("A new customer has been registered under you for {$projectName}.")
            ->line("Customer: {$customerName}");
        if ($mobile) {
            $message->line("Mobile: {$mobile}");
        }

        $fromAddress = $this->builder->getMailFromAddress();
        if ($fromAddress) {
            $message->from($fromAddress, $this->builder->getMailFromName());
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_customer_registered',
            'builder_firm_id' => $this->builder->id,
            'lead_id' => $this->lead->id,
            'project_id' => $this->lead->project_id,
            'customer_name' => $this->lead->customer?->name ?? null,
        ];
    }
}
