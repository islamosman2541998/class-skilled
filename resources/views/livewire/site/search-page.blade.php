<div>
    <form role="search" wire:submit.prevent class="flex gap-2" action="{{ \App\Support\LocalizedRoute::url('search') }}" method="get">
        <label for="site-search" class="sr-only">{{ __('search.placeholder') }}</label>
        <div class="relative flex-1">
            <x-heroicon-m-magnifying-glass class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-muted" aria-hidden="true" />
            <input id="site-search" type="search" name="q" wire:model.live.debounce.400ms="query" placeholder="{{ __('search.placeholder') }}" maxlength="80" autocomplete="off" class="h-13 w-full rounded-[12px] border border-line bg-white ps-11 pe-4 text-[16px] focus:border-sky focus:outline-none" autofocus>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('site.search') }}</button>
    </form>

    <div class="mt-8" aria-live="polite" wire:loading.class="opacity-60">
        @if ($throttled)
            <p class="rounded-[14px] border border-line bg-paper p-6 text-center text-muted">{{ __('search.too_many') }}</p>
        @elseif (mb_strlen($term) < 2)
            <p class="text-muted">{{ __('search.min_chars') }}</p>
        @elseif ($results === [])
            <div class="rounded-[14px] border border-line bg-paper p-8 text-center">
                <p class="font-bold text-navy">{{ __('search.no_results') }}</p>
                <p class="mt-2 text-muted">{{ __('search.suggestions') }}</p>
                <ul class="mt-4 flex flex-wrap justify-center gap-2">
                    @foreach (['services.index', 'projects.index', 'posts.index', 'contact'] as $route)
                        <li><a href="{{ \App\Support\LocalizedRoute::url($route) }}" class="chip">{{ __('nav.'.str($route)->before('.index')) }}</a></li>
                    @endforeach
                </ul>
            </div>
        @else
            <p class="t-small text-muted">{{ __('search.results_for', ['query' => $term]) }} · {{ __('search.results_count', ['count' => $total]) }}</p>
            <div class="mt-6 space-y-10">
                @foreach ($results as $key => $group)
                    <section aria-labelledby="results-{{ $key }}" wire:key="group-{{ $key }}">
                        <h2 id="results-{{ $key }}" class="t-card text-navy">{{ $group['label'] }} <span class="text-muted">(<bdi>{{ count($group['items']) }}</bdi>)</span></h2>
                        <ul class="mt-3 divide-y divide-line rounded-[14px] border border-line bg-white">
                            @foreach ($group['items'] as $item)
                                <li>
                                    <a href="{{ $item['url'] }}" class="block p-4 transition hover:bg-paper">
                                        <span class="block font-bold text-ink">{!! \App\Support\Highlight::mark($item['title'], $term) !!}</span>
                                        @if ($item['excerpt'] !== '')
                                            <span class="t-small mt-1 block text-muted">{!! \App\Support\Highlight::mark($item['excerpt'], $term) !!}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</div>
