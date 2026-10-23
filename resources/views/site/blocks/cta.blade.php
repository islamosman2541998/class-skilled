@if ($block['title'] !== '')
    <div class="on-dark rounded-[20px] bg-deep p-7 text-[#C9D3E3] lg:p-10">
        <div class="flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
            <div class="max-w-2xl">
                <h2 class="t-section text-white">{{ $block['title'] }}</h2>
                @if ($block['text'] !== '')
                    <p class="mt-3">{{ $block['text'] }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach (['primary' => 'btn-primary', 'secondary' => 'btn-ghost-light'] as $key => $class)
                    @if ($block[$key])
                        <a href="{{ $block[$key]['url'] }}" class="btn {{ $class }}" @if ($block[$key]['target_blank'] || str_starts_with($block[$key]['url'], 'https://wa.me')) target="_blank" rel="noopener" @endif>{{ $block[$key]['label'] }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif
