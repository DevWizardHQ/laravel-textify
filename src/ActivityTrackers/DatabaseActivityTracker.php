<?php

declare(strict_types=1);

namespace DevWizard\Textify\ActivityTrackers;

use DevWizard\Textify\Contracts\ActivityTrackerInterface;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;
use DevWizard\Textify\Models\TextifyActivity;
use Exception;
use Illuminate\Support\Facades\DB;

class DatabaseActivityTracker implements ActivityTrackerInterface
{
    public function trackSending(TextifyMessage $message, string $provider): void
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
            logger()->error('Failed to track SMS sending to database: '.$e->getMessage(), [
                'message_id' => $message->getId(),
                'provider' => $provider,
                'to' => $message->getTo(),
            ]);
        }
    }

    public function trackSent(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        try {
            $this->updateOrCreateActivity($message, $response, $provider, 'sent', true);
        } catch (Exception $e) {
            logger()->error('Failed to track successful SMS to database: '.$e->getMessage());
        }
    }

    public function trackFailed(TextifyMessage $message, TextifyResponse $response, string $provider): void
    {
        try {
            $this->updateOrCreateActivity($message, $response, $provider, 'failed', false);
        } catch (Exception $e) {
            logger()->error('Failed to track failed SMS to database: '.$e->getMessage());
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

    public function getActivities(array $filters = [], int $limit = 100): array
    {
        try {
            $query = TextifyActivity::query();

            if (isset($filters['provider'])) {
                $query->where('provider', $filters['provider']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['success'])) {
                $query->where('success', $filters['success']);
            }

            if (isset($filters['to'])) {
                $query->where('to', $filters['to']);
            }

            return $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (Exception $e) {
            logger()->error('Failed to retrieve SMS activities from database: '.$e->getMessage());

            return [];
        }
    }

    public function getStats(array $filters = []): array
    {
        try {
            $query = TextifyActivity::query();

            if (isset($filters['provider'])) {
                $query->where('provider', $filters['provider']);
            }

            $total = $query->count();
            $successful = (clone $query)->where('success', true)->count();
            $failed = (clone $query)->where('success', false)->count();

            return [
                'total' => $total,
                'successful' => $successful,
                'failed' => $failed,
                'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            ];
        } catch (Exception $e) {
            logger()->error('Failed to get SMS stats from database: '.$e->getMessage());

            return [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'success_rate' => 0,
            ];
        }
    }

    private function updateOrCreateActivity(
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

        $updated = TextifyActivity::where('message_id', $message->getId())->update($data);

        if (! $updated) {
            $data['message_id'] = $message->getId();
            TextifyActivity::create($data);
        }
    }
}
