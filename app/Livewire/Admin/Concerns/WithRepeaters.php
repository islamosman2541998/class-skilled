<?php

namespace App\Livewire\Admin\Concerns;

use Illuminate\Support\Str;

/**
 * Server side of <x-admin.repeater>: add / remove / reorder items of an
 * array property addressed by a dot path (e.g. "form.items" or "data.slides").
 * Every item gets a stable `_key` used for wire:key and drag ordering;
 * call `stripRepeaterKeys()` on the data before saving.
 */
trait WithRepeaters
{
    /**
     * @param  array<string, mixed>  $template
     */
    public function addRepeaterItem(string $path, array $template = []): void
    {
        $items = $this->repeaterItems($path);
        $items[] = ['_key' => Str::random(8)] + $template;
        data_set($this, $path, array_values($items));
    }

    public function removeRepeaterItem(string $path, int $index): void
    {
        $items = $this->repeaterItems($path);
        unset($items[$index]);
        data_set($this, $path, array_values($items));
    }

    public function moveRepeaterItem(string $path, int $from, int $to): void
    {
        $items = $this->repeaterItems($path);

        if (! isset($items[$from]) || $to < 0 || $to >= count($items)) {
            return;
        }

        $item = array_splice($items, $from, 1);
        array_splice($items, $to, 0, $item);
        data_set($this, $path, $items);
    }

    /**
     * @param  array<int, string>  $keys  `_key` values in their new order
     */
    public function sortRepeater(string $path, array $keys): void
    {
        $items = $this->repeaterItems($path);
        $byKey = [];

        foreach ($items as $index => $item) {
            $byKey[(string) ($item['_key'] ?? $index)] = $item;
        }

        $sorted = [];

        foreach ($keys as $key) {
            if (isset($byKey[$key])) {
                $sorted[] = $byKey[$key];
                unset($byKey[$key]);
            }
        }

        data_set($this, $path, [...$sorted, ...array_values($byKey)]);
    }

    /**
     * Give items loaded from the database a `_key`.
     *
     * @param  array<int, mixed>|null  $items
     * @return list<array<string, mixed>>
     */
    protected function withRepeaterKeys(?array $items): array
    {
        return array_values(array_map(
            fn ($item) => is_array($item) ? ['_key' => $item['_key'] ?? Str::random(8)] + $item : ['_key' => Str::random(8), 'value' => $item],
            $items ?? [],
        ));
    }

    /**
     * Remove `_key` recursively before persisting.
     */
    protected function stripRepeaterKeys(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        unset($data['_key']);

        foreach ($data as $key => $value) {
            $data[$key] = $this->stripRepeaterKeys($value);
        }

        return $data;
    }

    /**
     * @return array<int, mixed>
     */
    private function repeaterItems(string $path): array
    {
        $items = data_get($this, $path);

        return is_array($items) ? array_values($items) : [];
    }
}
