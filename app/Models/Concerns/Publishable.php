<?php

namespace App\Models\Concerns;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Draft / published workflow. "Scheduled" is never stored: it is a published
 * row whose `published_at` lies in the future (evaluated on read, no cron).
 *
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property-read bool $is_scheduled
 */
trait Publishable
{
    public function initializePublishable(): void
    {
        $this->mergeCasts([
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
        ]);
    }

    public static function bootPublishable(): void
    {
        static::saving(function (self $model): void {
            if ($model->status === PublishStatus::Scheduled) {
                $model->status = PublishStatus::Published;
            }

            if ($model->status === PublishStatus::Published && $model->published_at === null) {
                $model->published_at = now();
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), PublishStatus::Published->value)
            ->whereNotNull($this->qualifyColumn('published_at'))
            ->where($this->qualifyColumn('published_at'), '<=', now());
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), PublishStatus::Draft->value);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeScheduled(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), PublishStatus::Published->value)
            ->where($this->qualifyColumn('published_at'), '>', now());
    }

    public function getIsScheduledAttribute(): bool
    {
        return $this->status === PublishStatus::Published
            && $this->published_at !== null
            && $this->published_at->isFuture();
    }

    public function isPublished(): bool
    {
        return $this->status === PublishStatus::Published
            && $this->published_at !== null
            && ! $this->published_at->isFuture();
    }

    /**
     * Status as shown in the dashboard (draft / published / scheduled).
     */
    public function displayStatus(): PublishStatus
    {
        return $this->is_scheduled ? PublishStatus::Scheduled : $this->status;
    }
}
