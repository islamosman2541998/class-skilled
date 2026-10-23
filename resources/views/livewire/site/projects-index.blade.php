<div>
    <div class="flex flex-col gap-5">
        <div class="-mx-gutter flex gap-2 overflow-x-auto px-gutter pb-1" role="group" aria-label="{{ __('projects.category') }}">
            <button type="button" class="chip shrink-0" aria-pressed="{{ $category === null ? 'true' : 'false' }}" wire:click="setCategory(null)">
                {{ __('projects.all_categories') }} <span class="ms-1.5 opacity-70"><bdi>{{ $total }}</bdi></span>
            </button>
            @foreach ($this->categories as $cat)
                <button type="button" class="chip shrink-0" aria-pressed="{{ $category === $cat->id ? 'true' : 'false' }}" wire:click="setCategory({{ $cat->id }})">
                    {{ $cat->name }} <span class="ms-1.5 opacity-70"><bdi>{{ $this->counts[$cat->id] ?? 0 }}</bdi></span>
                </button>
            @endforeach
        </div>

        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_220px_200px]">
            <label class="relative block">
                <span class="sr-only">{{ __('projects.search_placeholder') }}</span>
                <x-heroicon-m-magnifying-glass class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-muted" aria-hidden="true" />
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="{{ __('projects.search_placeholder') }}" class="h-12 w-full rounded-[12px] border border-line bg-white ps-11 pe-4 focus:border-sky focus:outline-none" maxlength="80">
            </label>
            @if ($this->services->isNotEmpty())
                <label class="block">
                    <span class="sr-only">{{ __('projects.filter_service') }}</span>
                    <select wire:model.live="service" class="h-12 w-full rounded-[12px] border border-line bg-white px-4 focus:border-sky focus:outline-none">
                        <option value="">{{ __('projects.all_services') }}</option>
                        @foreach ($this->services as $item)
                            <option value="{{ $item->id }}">{{ $item->title }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label class="block">
                <span class="sr-only">{{ __('projects.sort') }}</span>
                <select wire:model.live="sort" class="h-12 w-full rounded-[12px] border border-line bg-white px-4 focus:border-sky focus:outline-none">
                    <option value="newest">{{ __('projects.sort_newest') }}</option>
                    <option value="oldest">{{ __('projects.sort_oldest') }}</option>
                    <option value="featured">{{ __('projects.sort_featured') }}</option>
                </select>
            </label>
        </div>
    </div>

    <p class="t-small mt-6 text-muted" aria-live="polite">{{ __('projects.count', ['count' => $category ? ($this->counts[$category] ?? 0) : $total]) }}</p>

    <div class="relative mt-4">
        {{-- Skeleton while a filter request runs. --}}
        <div wire:loading.delay.flex wire:target="setCategory, service, sort, search" class="absolute inset-0 z-10 hidden grid-cols-1 gap-6 bg-white/70 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
            @for ($i = 0; $i < 3; $i++)
                <div class="h-72 animate-pulse rounded-[20px] bg-paper"></div>
            @endfor
        </div>

        @if ($projects->isEmpty())
            <p class="rounded-[14px] border border-line bg-paper p-8 text-center text-muted">{{ __('projects.empty') }}</p>
        @else
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <li wire:key="p-{{ $project->id }}">@include('site.partials.project-card', ['project' => $project])</li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($hasMore)
        <div class="mt-10 text-center">
            <button type="button" class="btn btn-secondary" wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore">
                <span wire:loading.remove wire:target="loadMore">{{ __('site.load_more') }}</span>
                <span wire:loading wire:target="loadMore">{{ __('site.loading') }}</span>
            </button>
        </div>
    @endif
</div>
