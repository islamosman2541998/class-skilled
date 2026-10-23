<?php

namespace App\Services\Menus;

/**
 * Plain view model of a menu entry for the public site.
 */
class MenuNode
{
    /**
     * @param  list<MenuNode>  $children
     * @param  array{label: string, url: string}|null  $viewAll
     */
    public function __construct(
        public string $label,
        public ?string $url = null,
        public bool $newTab = false,
        public string $visibility = 'all',
        public array $children = [],
        public ?string $description = null,
        public bool $isDynamic = false,
        public ?array $viewAll = null,
        public bool $active = false,
    ) {}

    public function hasChildren(): bool
    {
        return $this->children !== [];
    }

    public function showOn(string $device): bool
    {
        return $this->visibility === 'all' || $this->visibility === $device;
    }

    /**
     * Copy with `active` flags for the given path (decoded, leading slash).
     */
    public function markActive(string $currentPath, string $homePath): self
    {
        $copy = clone $this;
        $copy->children = array_map(fn (MenuNode $child) => $child->markActive($currentPath, $homePath), $this->children);

        $path = $this->url ? rtrim(rawurldecode((string) parse_url($this->url, PHP_URL_PATH)), '/') : null;
        $path = $path === '' ? '/' : $path;

        $self = $path !== null && (
            $path === $currentPath
            || ($path !== $homePath && $path !== '/' && str_starts_with($currentPath, $path.'/'))
        );

        $copy->active = $self || collect($copy->children)->contains(fn (MenuNode $c) => $c->active);

        return $copy;
    }
}
