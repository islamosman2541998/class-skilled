<div aria-busy="true">
    @if ($name === 'stats')
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="a-panel space-y-3 p-5"><span class="a-skeleton block w-1/2"></span><span class="a-skeleton block h-7 w-1/3"></span><span class="a-skeleton block w-2/3"></span></div>
            @endfor
        </div>
    @else
        <div class="a-panel space-y-3 p-5">
            <span class="a-skeleton block w-1/3"></span>
            <span class="a-skeleton block h-40 w-full"></span>
        </div>
    @endif
</div>
