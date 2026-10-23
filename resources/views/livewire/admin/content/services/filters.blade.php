<x-admin.table.filter key="active" :label="__('admin.content.active')" :options="['1' => __('admin.common.active'), '0' => __('admin.common.inactive')]" :value="$filters['active'] ?? null" />
