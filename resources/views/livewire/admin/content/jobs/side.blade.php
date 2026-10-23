<x-admin.select model="form.job_type" :label="__('admin.jobs.type')" :options="\App\Enums\JobType::options()" :placeholder="false" />
<x-admin.date model="form.closes_at" :label="__('admin.jobs.closes_at')" :hint="__('admin.jobs.closes_at_hint')" />
@if ($this->recordId)
    <p class="text-[13px]">
        {{ __('admin.jobs.applications', ['count' => $applications]) }}
        @if (Route::has('admin.applications.index'))
            · <a href="{{ route('admin.applications.index', ['f' => ['job' => $this->recordId]]) }}" wire:navigate class="a-link">{{ __('admin.common.view') }}</a>
        @endif
    </p>
@endif
