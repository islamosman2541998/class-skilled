<?php

namespace App\Models\Concerns;

use App\Models\RequestActivity;
use App\Models\RequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * Shared behaviour of quote requests, contact messages and job applications.
 *
 * @property int|null $status_id
 * @property int|null $assigned_to
 * @property Carbon|null $read_at
 * @property array<string, mixed>|null $source
 * @property-read RequestStatus|null $status
 * @property-read User|null $assignee
 * @property-read Collection<int, RequestActivity> $activities
 */
trait IsVisitorRequest
{
    public function initializeIsVisitorRequest(): void
    {
        $this->mergeCasts([
            'source' => 'array',
            'read_at' => 'datetime',
        ]);
    }

    /**
     * @return BelongsTo<RequestStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return MorphMany<RequestActivity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(RequestActivity::class, 'subject')->latest('id');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull($this->qualifyColumn('read_at'));
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->saveQuietly();
        }
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function logActivity(string $type, ?string $body = null, array $meta = [], ?int $userId = null): RequestActivity
    {
        return $this->activities()->create([
            'type' => $type,
            'body' => $body,
            'meta' => $meta ?: null,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}
