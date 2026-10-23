<?php

namespace App\Livewire\Admin\Content\Concerns;

/**
 * Per-locale lists stored as `{ar: [...], en: [...]}` (service scope / stages,
 * project scope, job requirements) edited as a repeater of `{ar, en}` rows.
 */
trait PairLists
{
    /**
     * @param  array<string, mixed>|null  $stored
     * @return list<array<string, string>>
     */
    protected function pairsFromLists(?array $stored): array
    {
        $ar = array_values((array) ($stored['ar'] ?? []));
        $en = array_values((array) ($stored['en'] ?? []));
        $rows = [];

        foreach ($ar as $i => $text) {
            $rows[] = ['ar' => (string) $text, 'en' => (string) ($en[$i] ?? '')];
        }

        return $this->withRepeaterKeys($rows);
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array{ar: list<string>, en: list<string>}|null
     */
    protected function listsFromPairs(array $rows): ?array
    {
        $ar = [];
        $en = [];

        foreach ($rows as $row) {
            $a = trim((string) ($row['ar'] ?? ''));

            if ($a === '') {
                continue;
            }

            $ar[] = $a;
            $en[] = trim((string) ($row['en'] ?? ''));
        }

        if ($ar === []) {
            return null;
        }

        return ['ar' => $ar, 'en' => array_filter($en, fn (string $v) => $v !== '') === [] ? [] : $en];
    }
}
