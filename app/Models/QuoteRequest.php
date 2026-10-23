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
 * Visitor quote request. Drawings/photos live on the private disk (collection `attachments`).
 *
 * @property int $id
 * @property string|null $number
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property int|null $service_id
 * @property string|null $facility_type
 * @property string|null $project_state
 * @property string|null $emirate
 * @property string|null $area_text
 * @property string|null $area_m2
 * @property string|null $start_when
 * @property string|null $description
 * @property string|null $preferred_contact
 * @property string $locale
 * @property Carbon|null $follow_up_at
 * @property string|null $ip_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Service|null $service
 */
class QuoteRequest extends Model implements HasMedia
{
    use InteractsWithMedia, IsVisitorRequest, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'service_id', 'facility_type', 'project_state', 'emirate', 'area_text',
        'area_m2', 'start_when', 'description', 'preferred_contact', 'locale', 'status_id', 'assigned_to',
        'follow_up_at', 'source', 'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'area_m2' => 'decimal:2',
            'follow_up_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (QuoteRequest $request): void {
            $request->forceFill(['number' => 'QR-'.str_pad((string) $request->getKey(), 4, '0', STR_PAD_LEFT)])->saveQuietly();
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')->useDisk('private');
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
