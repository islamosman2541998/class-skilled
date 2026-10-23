<?php

namespace App\Support\Blocks;

use App\Enums\BlockType;
use App\Models\Asset;
use App\Models\Faq;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Registry + helpers for a `blocks` JSON array: validation rules, sanitising,
 * referenced asset ids and view-model preparation with batched lookups.
 */
class Blocks
{
    /**
     * @return class-string<Block>
     */
    public static function handler(BlockType $type): string
    {
        return match ($type) {
            BlockType::RichText => RichTextBlock::class,
            BlockType::MediaText => MediaTextBlock::class,
            BlockType::Gallery => GalleryBlock::class,
            BlockType::Video => VideoBlock::class,
            BlockType::Cards => CardsBlock::class,
            BlockType::Faq => FaqBlock::class,
            BlockType::Cta => CtaBlock::class,
            BlockType::Files => FilesBlock::class,
            BlockType::Quote => QuoteBlock::class,
            BlockType::TwoColumns => TwoColumnsBlock::class,
            BlockType::ValuesList => ValuesListBlock::class,
        };
    }

    /**
     * A new, empty block of the given type.
     *
     * @return array{id: string, type: string, data: array<string, mixed>}
     */
    public static function make(BlockType $type, array $data = []): array
    {
        $handler = static::handler($type);

        return [
            'id' => (string) Str::uuid(),
            'type' => $type->value,
            'data' => $handler::sanitize(array_replace_recursive($handler::defaults(), $data)),
        ];
    }

    /**
     * Validation rules for every block in `$blocks` under `$field` (e.g. "blocks").
     *
     * @param  array<int, mixed>  $blocks
     * @return array<string, mixed>
     */
    public static function rules(array $blocks, string $field = 'blocks'): array
    {
        $rules = [
            $field => ['array'],
            $field.'.*.type' => ['required', 'in:'.implode(',', BlockType::values())],
        ];

        foreach (array_values($blocks) as $index => $block) {
            $type = BlockType::tryFrom((string) ($block['type'] ?? ''));

            if ($type !== null) {
                $rules += static::handler($type)::rules($field.'.'.$index.'.data');
            }
        }

        return $rules;
    }

    /**
     * Normalise and purify all blocks before saving; unknown types are dropped.
     *
     * @param  array<int, mixed>|null  $blocks
     * @return list<array{id: string, type: string, data: array<string, mixed>}>
     */
    public static function sanitize(?array $blocks): array
    {
        $clean = [];

        foreach ($blocks ?? [] as $block) {
            $type = BlockType::tryFrom((string) ($block['type'] ?? ''));

            if ($type === null) {
                continue;
            }

            $clean[] = [
                'id' => is_string($block['id'] ?? null) && $block['id'] !== '' ? $block['id'] : (string) Str::uuid(),
                'type' => $type->value,
                'data' => static::handler($type)::sanitize((array) ($block['data'] ?? [])),
            ];
        }

        return $clean;
    }

    /**
     * @param  array<int, mixed>|null  $blocks
     * @return list<int>
     */
    public static function assetIds(?array $blocks): array
    {
        $ids = [];

        foreach ($blocks ?? [] as $block) {
            $type = BlockType::tryFrom((string) ($block['type'] ?? ''));

            if ($type !== null) {
                $ids = array_merge($ids, static::handler($type)::assetIds((array) ($block['data'] ?? [])));
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * View models for rendering: `[['id', 'type', 'view', 'data']]`, two queries max.
     *
     * @param  array<int, mixed>|null  $blocks
     * @return list<array{id: string, type: string, view: string, data: array<string, mixed>}>
     */
    public static function prepare(?array $blocks, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $blocks = $blocks ?? [];
        $assetIds = static::assetIds($blocks);

        /** @var Collection<int, Asset> $assets */
        $assets = $assetIds === []
            ? collect()
            : Asset::query()->with(['media', 'poster.media'])->whereIn('id', $assetIds)->get()->keyBy('id');

        $faqIds = [];

        foreach ($blocks as $block) {
            if (($block['type'] ?? null) === BlockType::Faq->value) {
                $faqIds = array_merge($faqIds, array_map('intval', (array) ($block['data']['faq_ids'] ?? [])));
            }
        }

        $context = [
            'faqs' => $faqIds === [] ? collect() : Faq::query()->whereIn('id', $faqIds)->get()->keyBy('id'),
        ];

        $prepared = [];

        foreach ($blocks as $block) {
            $type = BlockType::tryFrom((string) ($block['type'] ?? ''));

            if ($type === null) {
                continue;
            }

            $prepared[] = [
                'id' => (string) ($block['id'] ?? Str::uuid()),
                'type' => $type->value,
                'view' => 'site.blocks.'.str_replace('_', '-', $type->value),
                'data' => static::handler($type)::toView((array) ($block['data'] ?? []), $locale, $assets, $context),
            ];
        }

        return $prepared;
    }
}
