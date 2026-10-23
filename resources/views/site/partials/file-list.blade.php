{{-- Download list: type badge, title, size, open + download links. --}}
<ul class="divide-y divide-line rounded-[14px] border border-line bg-white">
    @foreach ($files as $file)
        @continue(! $file->url())
        <li class="flex flex-wrap items-center gap-4 p-4 sm:flex-nowrap">
            <span class="grid h-11 w-12 shrink-0 place-items-center rounded-[10px] bg-paper text-[12px] font-extrabold text-navy" aria-hidden="true">{{ $file->extensionLabel() }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate font-bold text-ink">{{ $file->titleText() }}</p>
                <p class="t-small text-muted"><span class="sr-only">{{ $file->extensionLabel() }} · </span><bdi dir="ltr">{{ $file->humanSize() }}</bdi></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ $file->url() }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">{{ __('site.open') }}</a>
                <a href="{{ $file->url() }}" download class="btn btn-primary btn-sm">{{ __('site.download') }}</a>
            </div>
        </li>
    @endforeach
</ul>
