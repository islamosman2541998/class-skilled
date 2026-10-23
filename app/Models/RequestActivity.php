<?php

namespace App\Models;

use App\Enums\ActivityType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Timeline entry of a visitor request (note, status change, assignment, system, email).
 *
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $user_id
 * @property ActivityType $type
 * @property string|null $body
 * @property array<string, mixed>|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Model $subject
 */
class RequestActivity extends Model
{
    protected $fillable = ['user_id', 'type', 'body', 'meta'];

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'meta' => 'array',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
