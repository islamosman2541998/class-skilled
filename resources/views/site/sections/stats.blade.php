{{-- lg:grid-cols-1 lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4 --}}
<x-site.section :section="$section">
    <div class="container-site">
        {{-- lg:grid-cols-1 lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4 --}}
<x-site.section-head :section="$section" />
        <dl class="grid grid-cols-2 gap-8 lg:grid-cols-{{ min(4, count($section['items'])) }}" data-reveal="stagger">
            @foreach ($section['items'] as $stat)
                <div class="flex flex-col-reverse border-s-2 border-sky ps-5" style="--i: {{ $loop->index }}">
                    <dt class="t-small mt-1 text-muted in-[.on-dark]:text-[#C9D3E3]">{{ $stat['label'] }}</dt>
                    <dd class="font-heading text-[28px] leading-tight font-extrabold text-navy in-[.on-dark]:text-white lg:text-[32px]">
                        <bdi dir="ltr" class="tabular-nums" @if ($section['count_up'] && is_numeric($stat['value'])) data-count="{{ $stat['value'] }}" @endif>{{ $stat['value'] }}</bdi>{{ $stat['suffix'] }}
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</x-site.section>
