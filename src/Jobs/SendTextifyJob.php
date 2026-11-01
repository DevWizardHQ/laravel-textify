<?php

declare(strict_types=1);

namespace DevWizard\Textify\Jobs;

use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Events\TextifyJobFailed;
use DevWizard\Textify\Exceptions\TextifyException;
use DevWizard\Textify\Facades\Textify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTextifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public int $timeout;

    /** @var int|array<int, int> */
    public int|array $backoff;

    public function __construct(
        public readonly TextifyMessage $message,
        public readonly string $provider = 'default'
    ) {
        $config = config('textify.queue', []);

        $this->tries = max(1, (int) ($config['tries'] ?? 3));
        $this->timeout = (int) ($config['timeout'] ?? 60);
        $this->backoff = $this->parseBackoff($config['backoff'] ?? '10,30,60');

        $this->onConnection($config['connection'] ?? null);
        $this->onQueue($config['name'] ?? null);

        if (($config['delay'] ?? 0) > 0) {
            $this->delay((int) $config['delay']);
        }
    }

    public function handle(): void
    {
        $response = $this->provider !== 'default'
            ? Textify::via($this->provider)->send($this->message->to, $this->message->message, $this->message->from)
            : Textify::send($this->message->to, $this->message->message, $this->message->from);

        if ($response instanceof TextifyResponse && $response->isFailed() && config('textify.queue.retry_failed_sends', true)) {
            throw new TextifyException(
                "SMS to {$this->message->to} failed: {$response->getErrorMessage()}",
                0,
                null,
                $this->provider
            );
        }
    }

    /**
     * @return int|array<int, int>
     */
    protected function parseBackoff(int|string|array $backoff): int|array
    {
        $delays = is_array($backoff) ? $backoff : explode(',', (string) $backoff);
        $delays = array_values(array_map('intval', array_filter(array_map(fn (mixed $delay) => trim((string) $delay), $delays), fn (string $delay) => $delay !== '')));

        return count($delays) === 1 ? $delays[0] : $delays;
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
        event(new TextifyJobFailed($this->message, $this->provider, $exception));
    }
}
