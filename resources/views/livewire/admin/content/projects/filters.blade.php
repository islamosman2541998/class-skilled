<x-admin.table.filter key="category" :label="__('admin.projects.category')" :options="$categories" :value="$filters['category'] ?? null" />
<x-admin.table.filter key="year" :label="__('admin.projects.year')" :options="$years" :value="$filters['year'] ?? null" />
<div class="ms-auto"><x-admin.table.columns :columns="['category' => __('admin.projects.category'), 'year' => __('admin.projects.year')]" /></div>
