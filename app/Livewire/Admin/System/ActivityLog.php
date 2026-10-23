<?php

namespace App\Livewire\Admin\System;

use App\Livewire\Admin\Concerns\WithDataTable;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.admin')]
class ActivityLog extends Component
{
    use WithDataTable;

    public ?int $viewing = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view activity'), 403);
    }

    protected function baseQuery(): Builder
    {
        return Activity::query()->with(['causer', 'subject']);
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $query->where('description', 'like', '%'.$term.'%')->orWhere('log_name', 'like', '%'.$term.'%');
    }

    protected function applyFilters(Builder $query): void
    {
        if (filled($this->filters['user'] ?? null)) {
            $query->where('causer_id', (int) $this->filters['user'])->where('causer_type', 'user');
        }

        if (filled($this->filters['module'] ?? null)) {
            $query->where('log_name', (string) $this->filters['module']);
        }

        if (filled($this->filters['event'] ?? null)) {
            $query->where('event', (string) $this->filters['event']);
        }

        if (filled($this->filters['period'] ?? null)) {
            $query->where('created_at', '>=', now()->subDays((int) $this->filters['period']));
        }
    }

    protected function sortableColumns(): array
    {
        return ['created' => 'created_at'];
    }

    protected function exportName(): string
    {
        return 'activity-log';
    }

    protected function exportColumns(): array
    {
        return [
            __('admin.common.date') => fn (Model $a) => $a->getAttribute('created_at')?->format('Y-m-d H:i'),
            __('admin.activity.user') => fn (Model $a) => $a->getRelationValue('causer')?->getAttribute('name') ?? __('admin.activity.system'),
            __('admin.activity.action') => fn (Model $a) => $a->getAttribute('event'),
            __('admin.activity.module') => fn (Model $a) => $a->getAttribute('log_name'),
            __('admin.activity.subject') => fn (Model $a) => $a->getAttribute('subject_id'),
            __('admin.activity.changes') => fn (Model $a) => $a->getAttribute('properties')?->toJson(JSON_UNESCAPED_UNICODE),
        ];
    }

    public function show(int $id): void
    {
        $this->viewing = $id;
        $this->dispatch('open-modal', name: 'activity-diff');
    }

    /**
     * @return array{old: array<string, mixed>, new: array<string, mixed>}|null
     */
    #[Computed]
    public function diff(): ?array
    {
        if ($this->viewing === null) {
            return null;
        }

        $activity = Activity::query()->find($this->viewing);
        $properties = $activity?->properties?->toArray() ?? [];

        return [
            'old' => (array) ($properties['old'] ?? []),
            'new' => (array) ($properties['attributes'] ?? array_diff_key($properties, ['old' => true])),
        ];
    }

    public static function subjectTitle(?Model $subject): string
    {
        if ($subject === null) {
            return '';
        }

        foreach (['title', 'name', 'number', 'key', 'from_path'] as $field) {
            if (! array_key_exists($field, $subject->getAttributes())) {
                continue;
            }

            $value = $subject->getAttribute($field);

            if (is_array($value)) {
                $value = $value[app()->getLocale()] ?? $value['ar'] ?? reset($value);
            }

            if (filled($value) && is_scalar($value)) {
                return (string) $value;
            }
        }

        return '#'.$subject->getKey();
    }

    public static function display(mixed $value): string
    {
        if (is_array($value)) {
            return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            return $value ? '✓' : '✗';
        }

        return (string) $value;
    }

    public function render(): View
    {
        return view('livewire.admin.system.activity-log', [
            'users' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
            'modules' => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name', 'log_name')->filter()->all(),
        ])->title(__('admin.activity.title'));
    }
}
