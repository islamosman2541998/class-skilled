<x-admin.toggle model="form.is_active" :label="__('admin.content.active')" :hint="__('admin.services.active_hint')" />
<x-admin.toggle model="form.is_featured" :label="__('admin.content.featured')" />
@if ($this->recordId)
    <p class="text-[13px] text-[var(--a-muted)]">{{ __('admin.services.projects_count', ['count' => $projectsCount]) }}</p>
@endif
