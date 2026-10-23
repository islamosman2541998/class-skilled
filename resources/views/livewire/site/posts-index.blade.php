<div>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="-mx-gutter flex gap-2 overflow-x-auto px-gutter pb-1 lg:mx-0 lg:px-0" role="group" aria-label="{{ __('posts.all_categories') }}">
            <button type="button" class="chip shrink-0" aria-pressed="{{ $category === null ? 'true' : 'false' }}" wire:click="setCategory(null)">{{ __('posts.all_categories') }}</button>
            @foreach ($this->categories as $cat)
                <button type="button" class="chip shrink-0" aria-pressed="{{ $category === $cat->id ? 'true' : 'false' }}" wire:click="setCategory({{ $cat->id }})">{{ $cat->name }}</button>
            @endforeach
        </div>
        <label class="relative block lg:w-80">
            <span class="sr-only">{{ __('posts.search_placeholder') }}</span>
            <x-heroicon-m-magnifying-glass class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-muted" aria-hidden="true" />
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="{{ __('posts.search_placeholder') }}" maxlength="80" class="h-12 w-full rounded-[12px] border border-line bg-white ps-11 pe-4 focus:border-sky focus:outline-none">
        </label>
    </div>

    <div class="relative mt-8" wire:loading.class="opacity-60" wire:target="setCategory, search, gotoPage, nextPage, previousPage">
        @if ($featured)
            <div class="mb-8">@include('site.partials.post-card', ['post' => $featured, 'wide' => true])</div>
        @endif

        @if ($posts->isEmpty() && ! $featured)
            <p class="rounded-[14px] border border-line bg-paper p-8 text-center text-muted">{{ __('posts.empty') }}</p>
        @else
            <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <li wire:key="post-{{ $post->id }}">@include('site.partials.post-card', ['post' => $post])</li>
                @endforeach
            </ul>
        @endif
    </div>

    {{ $posts->links('site.partials.pagination') }}
</div>
