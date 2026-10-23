<div>
    <x-admin.page-header
        :title="now()->hour < 12 ? __('admin.dashboard.greeting_morning') : __('admin.dashboard.greeting_evening')"
        :subtitle="__('admin.dashboard.subtitle', ['days' => 30])"
    />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.nav.quotes')" value="—" />
        <x-admin.stat-card :label="__('admin.nav.messages')" value="—" />
        <x-admin.stat-card :label="__('admin.nav.applications')" value="—" />
        <x-admin.stat-card :label="__('admin.nav.projects')" value="—" />
    </div>
</div>
