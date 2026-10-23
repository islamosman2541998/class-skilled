<?php

namespace App\Models;

use App\Models\Concerns\IsVisitorRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * CV on the private disk (collection `cv`).
 *
 * @property int $id
 * @property int|null $job_post_id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string|null $specialty
 * @property string|null $message
 * @property string $locale
 * @property string|null $ip_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read JobPost|null $jobPost
 */
class JobApplication extends Model implements HasMedia
{
    use InteractsWithMedia, IsVisitorRequest, SoftDeletes;

    protected $fillable = [
        'job_post_id', 'name', 'phone', 'email', 'specialty', 'message', 'locale', 'status_id', 'assigned_to',
        'source', 'ip_hash',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cv')->useDisk('private')->singleFile();
    }

    /**
     * @return BelongsTo<JobPost, $this>
     */
    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function displayNumber(): string
    {
        return 'JA-'.str_pad((string) $this->getKey(), 4, '0', STR_PAD_LEFT);
    }
}
