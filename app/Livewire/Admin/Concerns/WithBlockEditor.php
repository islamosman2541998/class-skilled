<?php

namespace App\Livewire\Admin\Concerns;

use App\Enums\BlockType;
use App\Support\Blocks\Blocks;
use Illuminate\Support\Str;

/**
 * Block editor state (`$blocks` = list of {id, type, data}) shared by pages,
 * services and projects: add, duplicate, remove, reorder (drag) and
 * validation rules per block type. Item rows inside blocks use WithRepeaters
 * on paths like "blocks.2.data.items".
 */
trait WithBlockEditor
{
    /** @var list<array{id: string, type: string, data: array<string, mixed>}> */
    public array $blocks = [];

    /**
     * @param  array<int, mixed>|null  $stored
     */
    protected function loadBlocks(?array $stored): void
    {
        $blocks = [];

        foreach ($stored ?? [] as $block) {
            $type = BlockType::tryFrom((string) ($block['type'] ?? ''));

            if ($type === null) {
                continue;
            }

            $handler = Blocks::handler($type);
            $data = array_replace_recursive($handler::defaults(), (array) ($block['data'] ?? []));

            foreach (['items'] as $listKey) {
                if (isset($data[$listKey]) && is_array($data[$listKey])) {
                    $data[$listKey] = $this->withRepeaterKeys($data[$listKey]);
                }
            }

            $blocks[] = ['id' => (string) ($block['id'] ?? Str::uuid()), 'type' => $type->value, 'data' => $data];
        }

        $this->blocks = $blocks;
    }

    public function addBlock(string $type): void
    {
        $blockType = BlockType::tryFrom($type);

        if ($blockType === null) {
            return;
        }

        $handler = Blocks::handler($blockType);
        $this->blocks[] = ['id' => (string) Str::uuid(), 'type' => $blockType->value, 'data' => $handler::defaults()];
        $this->dispatch('block-added', id: end($this->blocks)['id']);
    }

    public function duplicateBlock(int $index): void
    {
        if (! isset($this->blocks[$index])) {
            return;
        }

        $copy = $this->blocks[$index];
        $copy['id'] = (string) Str::uuid();
        array_splice($this->blocks, $index + 1, 0, [$copy]);
    }

    public function removeBlock(int $index): void
    {
        unset($this->blocks[$index]);
        $this->blocks = array_values($this->blocks);
    }

    /**
     * @param  array<int, string>  $ids
     */
    public function sortBlocks(array $ids): void
    {
        $byId = collect($this->blocks)->keyBy('id');
        $sorted = [];

        foreach ($ids as $id) {
            if ($byId->has($id)) {
                $sorted[] = $byId->get($id);
                $byId->forget($id);
            }
        }

        $this->blocks = [...$sorted, ...$byId->values()->all()];
    }

    /**
     * @return array<string, mixed>
     */
    protected function blockRules(): array
    {
        return Blocks::rules($this->blocks, 'blocks');
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function blocksForSave(): array
    {
        /** @var list<array<string, mixed>> $clean */
        $clean = $this->stripRepeaterKeys($this->blocks);

        return $clean;
    }

    /**
     * One-line summary shown on a collapsed block.
     */
    public function blockSummary(array $block): string
    {
        $data = $block['data'] ?? [];
        $text = $data['title']['ar'] ?? $data['html']['ar'] ?? $data['text']['ar'] ?? $data['left']['title']['ar'] ?? '';

        if (isset($data['items']) && is_array($data['items'])) {
            $text = trim(($text ? $text.' · ' : '').__('admin.blocks.items_count', ['count' => count($data['items'])]));
        } elseif (isset($data['asset_ids']) && is_array($data['asset_ids'])) {
            $text = trim(($text ? $text.' · ' : '').__('admin.blocks.files_count', ['count' => count($data['asset_ids'])]));
        }

        return Str::limit(trim(strip_tags((string) $text)), 70);
    }
}
