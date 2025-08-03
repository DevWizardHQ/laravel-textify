<?php

declare(strict_types=1);

namespace DevWizard\Textify\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TextifyActivity extends Model
{
    protected $table = 'textify_activities';

    /**
     * @property string $message_id
     * @property string $provider
     * @property string $to
     * @property string $from
     * @property string $message
     * @property string $status
     * @property bool $success
     * @property string|null $error_code
     * @property string|null $error_message
     * @property float|null $cost
     * @property array $metadata
     * @property \Carbon\Carbon|null $sent_at
     * @property \Carbon\Carbon $created_at
     * @property \Carbon\Carbon $updated_at
     */
    protected $fillable = [
        'message_id',
        'provider',
        'to',
        'from',
        'message',
        'status',
        'success',
        'error_code',
        'error_message',
        'cost',
        'metadata',
        'sent_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'cost' => 'decimal:4',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes for easy querying
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('success', true);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('success', false);
    }

    public function scopeByProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeLastDays(Builder $query, int $days): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeByRecipient(Builder $query, string $to): Builder
    {
        return $query->where('to', $to);
    }

    // Helper methods
    public function isSuccessful(): bool
    {
        return $this->getAttribute('success');
    }

    public function isFailed(): bool
    {
        return ! $this->getAttribute('success');
    }

    public function hasError(): bool
    {
        return ! empty($this->getAttribute('error_code')) || ! empty($this->getAttribute('error_message'));
    }

    public function getCostFormatted(): string
    {
        $cost = $this->getAttribute('cost');

        return $cost ? '$'.number_format($cost, 4) : 'N/A';
    }
}
