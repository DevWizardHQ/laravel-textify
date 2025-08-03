<?php

declare(strict_types=1);

namespace DevWizard\Textify\Loggers;

use DevWizard\Textify\Contracts\TextifyLoggerInterface;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Models\TextifyActivity;
use Exception;
use Illuminate\Support\Facades\DB;

class DatabaseLogger implements TextifyLoggerInterface
{
    public function logSending(TextifyMessage $message, string $provider): void
    {
        try {
            TextifyActivity::create([
                'message_id' => $message->getId(),
                'provider' => $provider,
                'to' => $message->getTo(),
                'from' => $message->getFrom(),
                'message' => $message->getMessage(),
                'status' => 'sending',
                'success' => false,
                'metadata' => $message->getMetadata(),
                'sent_at' => null,
            ]);
        } catch (Exception $e) {
            // Fallback to file logging if database fails
            logger()->error('Failed to log SMS to database: '.$e->getMessage(), [
                'message_id' => $message->getId(),
                'provider' => $provider,
                'to' => $message->getTo(),
            ]);
        }
    }

    public function logSent(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        try {
            $this->updateOrCreateLog($message, $response, $provider, 'sent', true);
        } catch (Exception $e) {
            logger()->error('Failed to log successful SMS to database: '.$e->getMessage());
        }
    }

    public function logFailed(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        try {
            $this->updateOrCreateLog($message, $response, $provider, 'failed', false);
        } catch (Exception $e) {
            logger()->error('Failed to log failed SMS to database: '.$e->getMessage());
        }
    }

    public function updateStatus(string $messageId, string $status, array $metadata = []): void
    {
        try {
            TextifyActivity::where('message_id', $messageId)
                ->update([
                    'status' => $status,
                    'metadata' => DB::raw("JSON_MERGE_PATCH(COALESCE(metadata, '{}'), '".json_encode($metadata)."')"),
                    'updated_at' => now(),
                ]);
        } catch (Exception $e) {
            logger()->error('Failed to update SMS status in database: '.$e->getMessage(), [
                'message_id' => $messageId,
                'status' => $status,
            ]);
        }
    }

    private function updateOrCreateLog(
        TextifyMessage $message,
        TextifyResponse $response,
        string $provider,
        string $status,
        bool $success
    ): void {
        $data = [
            'provider' => $provider,
            'to' => $message->getTo(),
            'from' => $message->getFrom(),
            'message' => $message->getMessage(),
            'status' => $status,
            'success' => $success,
            'error_code' => $response->getErrorCode(),
            'error_message' => $response->getErrorMessage(),
            'cost' => $response->getCost(),
            'metadata' => [...$message->getMetadata(), ...$response->getMetadata()],
            'sent_at' => $success ? now() : null,
        ];

        // Try to update existing log first
        $updated = TextifyActivity::where('message_id', $message->getId())
            ->update($data);

        // If no existing log found, create new one
        if (! $updated) {
            $data['message_id'] = $message->getId();
            TextifyActivity::create($data);
        }
    }
}
