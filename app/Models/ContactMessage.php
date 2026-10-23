<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $subject
 * @property string $message
 * @property string $locale
 * @property string|null $ip_hash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ContactMessage extends VisitorRequest
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'subject', 'message', 'locale', 'status_id', 'assigned_to', 'source', 'ip_hash',
    ];

    public function displayNumber(): string
    {
        return 'CM-'.str_pad((string) $this->getKey(), 4, '0', STR_PAD_LEFT);
    }
}
