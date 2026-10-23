<?php

namespace App\Support\Blocks;

use App\Models\Faq;
use App\Support\Translate;
use Illuminate\Support\Collection;

/** {title, faq_ids[] | items[{q, a}]} */
class FaqBlock extends Block
{
    public static function rules(string $prefix): array
    {
        return [
            ...static::pairRules($prefix.'.title'),
            $prefix.'.faq_ids' => ['array'],
            $prefix.'.faq_ids.*' => ['integer', 'exists:faqs,id'],
            $prefix.'.items' => ['array'],
            ...static::pairRules($prefix.'.items.*.q', true, 300),
            ...static::pairRules($prefix.'.items.*.a', true, 3000),
        ];
    }

    public static function defaults(): array
    {
        return ['title' => ['ar' => '', 'en' => ''], 'faq_ids' => [], 'items' => []];
    }

    public static function sanitize(array $data): array
    {
        return [
            'title' => Translate::pair($data['title'] ?? []),
            'faq_ids' => static::ids($data['faq_ids'] ?? []),
            'items' => array_values(array_map(fn ($item) => [
                'q' => Translate::pair($item['q'] ?? []),
                'a' => Translate::pair($item['a'] ?? []),
            ], (array) ($data['items'] ?? []))),
        ];
    }

    public static function toView(array $data, string $locale, Collection $assets, array $context = []): array
    {
        /** @var Collection<int, Faq> $faqs */
        $faqs = $context['faqs'] ?? collect();
        $items = [];

        foreach (static::ids($data['faq_ids'] ?? []) as $id) {
            $faq = $faqs->get($id);

            if ($faq instanceof Faq && $faq->is_active) {
                $items[] = [
                    'q' => (string) $faq->getTranslation('question', $locale),
                    'a' => (string) $faq->getTranslation('answer', $locale),
                ];
            }
        }

        foreach ((array) ($data['items'] ?? []) as $item) {
            $items[] = ['q' => static::text($item['q'] ?? [], $locale), 'a' => static::text($item['a'] ?? [], $locale)];
        }

        return ['title' => static::text($data['title'] ?? [], $locale), 'items' => $items];
    }
}
