<x-admin.panel :title="__('admin.dashboard.latest_quotes')" :padded="false">
    <x-slot:actions>
        <a href="{{ route('admin.quotes.index') }}" wire:navigate class="a-link text-[13px]">{{ __('admin.dashboard.view_all') }}</a>
    </x-slot:actions>
    <div class="overflow-x-auto">
        <table class="a-table">
            <thead>
                <tr>
                    <th>{{ __('admin.requests.client') }}</th>
                    <th>{{ __('admin.requests.service') }}</th>
                    <th>{{ __('admin.requests.status') }}</th>
                    <th>{{ __('admin.requests.date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotes as $quote)
                    <tr @class(['font-bold' => $quote->read_at === null])>
                        <td>
                            <a href="{{ route('admin.quotes.show', $quote->id) }}" wire:navigate class="text-[var(--c-deep)] hover:underline">{{ $quote->name }}</a>
                            <span class="text-[12px] font-normal text-[var(--a-muted)]" dir="ltr">{{ $quote->number }}</span>
                        </td>
                        <td class="font-normal">{{ $quote->service?->getTranslation('title', app()->getLocale()) ?? '—' }}</td>
                        <td>
                            @if ($quote->status)
                                <x-admin.badge :color="$quote->status->color->value">{{ $quote->status->getTranslation('name', app()->getLocale()) }}</x-admin.badge>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-[13px] font-normal text-[var(--a-muted)]">{{ $quote->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-[var(--a-muted)]">{{ __('admin.dashboard.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.panel>
