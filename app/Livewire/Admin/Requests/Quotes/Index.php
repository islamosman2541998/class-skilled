<?php

namespace App\Livewire\Admin\Requests\Quotes;

use App\Enums\RequestType;
use App\Livewire\Admin\Requests\RequestIndex;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Settings\FormSettings;
use App\Support\Translate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Index extends RequestIndex
{
    protected function modelClass(): string
    {
        return QuoteRequest::class;
    }

    public function type(): RequestType
    {
        return RequestType::Quote;
    }

    public function routeKey(): string
    {
        return 'quotes';
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with('service')->withCount('media');
    }

    protected function applySearch(Builder $query, string $term): void
    {
        parent::applySearch($query, $term);
        $query->orWhere('number', 'like', '%'.trim($term).'%');
    }

    public function extraFilters(): array
    {
        $emirates = collect(app(FormSettings::class)->emirates)->map(fn ($e) => Translate::pick($e, 'ar'));

        return [
            'service' => [(string) __('admin.requests.service'), Service::query()->ordered()->get()->mapWithKeys(fn (Service $s) => [(string) $s->id => (string) $s->getTranslation('title', 'ar')])->all()],
            'emirate' => [(string) __('admin.requests.emirate'), $emirates->mapWithKeys(fn ($name) => [$name => $name])->all()],
            'files' => [(string) __('admin.requests.attachments'), ['1' => (string) __('admin.requests.with_files'), '0' => (string) __('admin.requests.without_files')]],
        ];
    }

    protected function applyTypeFilters(Builder $query): void
    {
        if (filled($this->filters['service'] ?? null)) {
            $query->where('service_id', (int) $this->filters['service']);
        }

        if (filled($this->filters['emirate'] ?? null)) {
            $query->where('emirate', (string) $this->filters['emirate']);
        }

        if (($this->filters['files'] ?? '') !== '') {
            $this->filters['files'] === '1' ? $query->has('media') : $query->doesntHave('media');
        }
    }

    public function extraColumns(): array
    {
        return [
            'service' => [__('admin.requests.service'), fn (Model $row) => $row->getRelationValue('service')?->getTranslation('title', app()->getLocale())],
            'emirate' => [__('admin.requests.emirate'), fn (Model $row) => $row->getAttribute('emirate')],
        ];
    }
}
