<?php

declare(strict_types=1);

namespace DevWizard\Textify\Jobs;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\Facades\Textify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTextifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly TextifyMessage $message,
        public readonly string $provider = 'default'
    ) {}

    public function handle(): void
    {
        if ($this->provider !== 'default') {
            Textify::via($this->provider)->send(
                $this->message->to,
                $this->message->message,
                $this->message->from
            );
        } else {
            Textify::send(
                $this->message->to,
                $this->message->message,
                $this->message->from
            );
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Log the failure
        logger()->error('Textify Job Failed', [
            'message_id' => $this->message->getId(),
            'to' => $this->message->to,
            'provider' => $this->provider,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Optionally dispatch a failed event
        // event(new TextifyJobFailed($this->message, $this->provider, $exception));
    }
}
