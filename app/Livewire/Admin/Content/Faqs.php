<?php

namespace App\Livewire\Admin\Content;

use App\Models\ContentModel;
use App\Models\Faq;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

/**
 * FAQ items: general ones (FAQ page) and those attached to a service.
 */
class Faqs extends SimpleResource
{
    protected function modelClass(): string
    {
        return Faq::class;
    }

    public function titleKey(): string
    {
        return 'admin.nav.faqs';
    }

    public function singularKey(): string
    {
        return 'admin.content.singular.faq';
    }

    public function titleField(): string
    {
        return 'question';
    }

    public function fields(): array
    {
        return [
            ['key' => 'question', 'type' => 'pair', 'label' => 'admin.faqs.question', 'required' => true, 'max' => 300],
            ['key' => 'answer', 'type' => 'pair_textarea', 'label' => 'admin.faqs.answer', 'required' => true, 'max' => 3000, 'rows' => 4],
            ['key' => 'service_id', 'type' => 'select', 'label' => 'admin.faqs.service', 'options' => $this->services()],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'question', 'type' => 'title', 'label' => 'admin.faqs.question'],
            ['key' => 'service', 'label' => 'admin.faqs.service'],
            ['key' => 'is_active', 'type' => 'toggle', 'label' => 'admin.content.active'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function services(): array
    {
        return Service::query()->ordered()->get()->mapWithKeys(fn (Service $s) => [$s->id => $s->getTranslation('title', 'ar')])->all();
    }

    protected function eagerLoads(): array
    {
        return ['faqable'];
    }

    protected function applyModuleFilters(Builder $query): void
    {
        $scope = $this->filters['scope'] ?? '';

        if ($scope === 'general') {
            $query->whereNull('faqable_type');
        } elseif (is_numeric($scope)) {
            $query->where('faqable_type', 'service')->where('faqable_id', (int) $scope);
        }
    }

    public function filterChips(): array
    {
        return [['scope', __('admin.faqs.scope'), ['general' => __('admin.faqs.general')] + $this->services()]];
    }

    protected function fillExtra(ContentModel $model, array $form): array
    {
        $form['service_id'] = $model->getAttribute('faqable_type') === 'service' ? (string) $model->getAttribute('faqable_id') : null;

        return $form;
    }

    protected function beforeSave(ContentModel $model): void
    {
        $serviceId = $model->getAttribute('service_id');
        $model->offsetUnset('service_id');
        $model->setAttribute('faqable_type', $serviceId ? 'service' : null);
        $model->setAttribute('faqable_id', $serviceId ? (int) $serviceId : null);
    }

    public function cell(ContentModel $row, string $key): string
    {
        if ($key === 'service') {
            $owner = $row->getRelationValue('faqable');

            return $owner instanceof Service ? (string) $owner->getTranslation('title', 'ar') : __('admin.faqs.general');
        }

        return parent::cell($row, $key);
    }
}
