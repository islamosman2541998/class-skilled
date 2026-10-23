<x-admin.select model="form.parent_id" :label="__('admin.pages.parent')" :options="$parents" :placeholder="__('admin.common.none')" />
<x-admin.segmented model="form.template" :label="__('admin.pages.template')" :options="['normal' => __('admin.pages.template_normal'), 'wide' => __('admin.pages.template_wide')]" />
<x-admin.toggle model="form.show_in_sitemap" :label="__('admin.pages.show_in_sitemap')" />
