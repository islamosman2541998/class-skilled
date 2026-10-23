{{-- lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5 lg:grid-cols-6 --}}
<x-site.section :section="$section">
    <div class="container-site">
        {{-- lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5 lg:grid-cols-6 --}}
<x-site.section-head :section="$section" />
        <ol class="relative grid gap-8 lg:gap-6 lg:grid-cols-{{ max(2, min(6, count($section['steps']))) }}" data-reveal="draw-line">
            <span class="process-line absolute start-[19px] top-5 bottom-5 w-0.5 bg-sky/50 lg:start-5 lg:end-5 lg:top-5 lg:bottom-auto lg:h-0.5 lg:w-auto" aria-hidden="true"></span>
            @foreach ($section['steps'] as $step)
                <li class="process-step relative flex gap-5 lg:block" style="--i: {{ $loop->index }}">
                    <span class="relative z-[1] grid size-10 shrink-0 place-items-center rounded-full bg-sky font-heading text-[15px] font-extrabold text-deep ring-8 ring-[var(--ring,#fff)]">{{ $loop->iteration }}</span>
                    <div class="lg:mt-5">
                        <h3 class="t-card">{{ $step['title'] }}</h3>
                        @if ($step['text'] !== '')
                            <p class="mt-2 text-[14.5px] text-muted in-[.on-dark]:text-[#C9D3E3]">{{ $step['text'] }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</x-site.section>
