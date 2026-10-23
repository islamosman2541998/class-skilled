<?php

namespace App\Livewire\Admin\Requests\Messages;

use App\Enums\RequestType;
use App\Livewire\Admin\Requests\RequestIndex;
use App\Models\ContactMessage;
use App\Settings\FormSettings;
use App\Support\Translate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Index extends RequestIndex
{
    protected function modelClass(): string
    {
        return ContactMessage::class;
    }

    public function type(): RequestType
    {
        return RequestType::Message;
    }

    public function routeKey(): string
    {
        return 'messages';
    }

    public function extraFilters(): array
    {
        $subjects = collect(app(FormSettings::class)->contact_subjects)->map(fn ($s) => Translate::pick($s, 'ar'));

        return [
            'subject' => [(string) __('admin.requests.subject'), $subjects->mapWithKeys(fn ($name) => [$name => $name])->all()],
        ];
    }

    protected function applyTypeFilters(Builder $query): void
    {
        if (filled($this->filters['subject'] ?? null)) {
            $query->where('subject', (string) $this->filters['subject']);
        }
    }

    public function extraColumns(): array
    {
        return [
            'subject' => [__('admin.requests.subject'), fn (Model $row) => $row->getAttribute('subject')],
            'message' => [__('admin.requests.message'), fn (Model $row) => Str::limit((string) $row->getAttribute('message'), 70)],
        ];
    }
}
