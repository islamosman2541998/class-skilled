@php
    $request = $this->request;
    $key = $this->routeKey();
    $actions = $this->quickActions();
    $attachments = $this->attachments();
    $source = (array) $request->source;
    $locale = app()->getLocale();
    $canUpdate = auth()->user()->can('update', $request);
@endphp
<div x-data="{ preview: null }">
    <x-admin.page-header :title="$number.' — '.$request->name"
        :breadcrumbs="[__('admin.nav.requests') => null, __('admin.nav.'.$key) => route('admin.'.$key.'.index'), $number => null]">
        <x-slot:actions>
            @if ($request->status)
                <x-admin.badge :color="$request->status->color->value">{{ $request->status->getTranslation('name', $locale) }}</x-admin.badge>
            @endif
            @if ($actions['whatsapp'])
                <a href="{{ $actions['whatsapp'] }}" target="_blank" rel="noopener" class="a-btn a-btn-primary">{{ __('admin.requests.whatsapp') }}</a>
            @endif
            @if ($actions['call'])
                <a href="{{ $actions['call'] }}" class="a-btn a-btn-secondary">{{ __('admin.requests.call') }}</a>
            @endif
            @if ($actions['email'])
                <a href="{{ $actions['email'] }}" class="a-btn a-btn-secondary">{{ __('admin.requests.email') }}</a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="min-w-0 space-y-6">
            <x-admin.panel :title="__('admin.requests.details')">
                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    @foreach ($fields as $label => $value)
                        <div @class(['sm:col-span-2' => mb_strlen($value) > 60])>
                            <dt class="text-[12.5px] font-semibold text-[var(--a-muted)]">{{ $label }}</dt>
                            <dd class="mt-0.5 whitespace-pre-line text-[14.5px] text-[var(--c-deep)]"><bdi>{{ $value }}</bdi></dd>
                        </div>
                    @endforeach
                    @if ($request instanceof \App\Models\JobApplication && $request->jobPost)
                        <div>
                            <dt class="text-[12.5px] font-semibold text-[var(--a-muted)]">{{ __('admin.requests.job_page') }}</dt>
                            <dd class="mt-0.5"><a href="{{ route('admin.jobs.edit', $request->jobPost->id) }}" wire:navigate class="a-link">{{ $request->jobPost->getTranslation('title', 'ar') }}</a></dd>
                        </div>
                    @endif
                </dl>
            </x-admin.panel>

            @if ($attachments)
                <x-admin.panel :title="__('admin.requests.attachments').' ('.count($attachments).')'">
                    <ul class="grid gap-3 sm:grid-cols-2">
                        @foreach ($attachments as $file)
                            <li class="flex items-center gap-3 rounded-xl border border-a-border p-3">
                                <span class="grid h-10 w-12 shrink-0 place-items-center rounded-lg bg-[#eef1f6] text-[11px] font-extrabold text-[var(--c-navy)]">{{ $file['ext'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-bold" dir="ltr">{{ $file['name'] }}</p>
                                    <p class="text-[12px] text-[var(--a-muted)]" dir="ltr">{{ $file['size'] }}</p>
                                </div>
                                @if ($file['image'] || $file['pdf'])
                                    <button type="button" class="a-link text-[13px]" @click="preview = @js(['url' => $file['url'], 'image' => $file['image'], 'name' => $file['name']]); $dispatch('open-modal', 'attachment')">{{ __('admin.requests.preview') }}</button>
                                @endif
                                <a href="{{ $file['download'] }}" class="a-link text-[13px]">{{ __('admin.requests.download') }}</a>
                            </li>
                        @endforeach
                    </ul>
                </x-admin.panel>
            @endif

            <x-admin.panel :title="__('admin.requests.timeline')">
                @if ($canUpdate)
                    <form wire:submit="addNote" class="mb-6">
                        <label for="note" class="sr-only">{{ __('admin.requests.note') }}</label>
                        <textarea id="note" wire:model="note" rows="3" class="a-input h-auto py-2" placeholder="{{ __('admin.requests.note_placeholder') }}"></textarea>
                        @error('note') <p class="mt-1 text-[12.5px] text-[#b42318]">{{ $message }}</p> @enderror
                        <div class="mt-2 text-end"><button type="submit" class="a-btn a-btn-primary a-btn-sm">{{ __('admin.requests.add_note') }}</button></div>
                    </form>
                @endif
                <ol class="relative space-y-5 border-s border-a-border ps-5">
                    @foreach ($request->activities as $activity)
                        <li class="relative" wire:key="act-{{ $activity->id }}">
                            <span @class([
                                'absolute -start-[26px] top-1 size-3 rounded-full ring-4 ring-white',
                                'bg-[var(--c-sky)]' => $activity->type->value === 'note',
                                'bg-[var(--c-navy)]' => in_array($activity->type->value, ['status', 'assign'], true),
                                'bg-[#c5cdd9]' => ! in_array($activity->type->value, ['note', 'status', 'assign'], true),
                            ])></span>
                            <p class="text-[13.5px]">
                                <span class="font-bold text-[var(--c-deep)]">{{ $activity->user?->name ?? __('admin.requests.system') }}</span>
                                <span class="text-[var(--a-muted)]">
                                    @switch($activity->type->value)
                                        @case('status')
                                            {{ __('admin.requests.activity_status', ['from' => $activity->meta['from'] ?? '—', 'to' => $activity->meta['to'] ?? '—']) }}
                                            @break
                                        @case('assign')
                                            {{ ($activity->meta['to'] ?? null) ? __('admin.requests.activity_assign', ['name' => $activity->meta['to']]) : __('admin.requests.activity_unassign') }}
                                            @break
                                        @case('note')
                                            {{ __('admin.requests.activity_note') }}
                                            @break
                                        @default
                                            {{ __('admin.requests.activity_'.($activity->meta['event'] ?? 'system')) }}
                                    @endswitch
                                </span>
                                · <time class="text-[12px] text-[var(--a-muted)]" datetime="{{ $activity->created_at?->toIso8601String() }}" title="{{ $activity->created_at?->format('Y-m-d H:i') }}">{{ $activity->created_at?->diffForHumans() }}</time>
                            </p>
                            @if ($activity->body)
                                <p class="mt-1.5 whitespace-pre-line rounded-lg bg-[#f5f7fa] p-3 text-[14px]">{{ $activity->body }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </x-admin.panel>
        </div>

        <aside class="space-y-6">
            <x-admin.panel :title="__('admin.requests.client')">
                <p class="font-bold text-[var(--c-deep)]">{{ $request->name }}</p>
                @if ($request->phone)<p class="mt-1 text-[14px]" dir="ltr">{{ $request->phone }}</p>@endif
                @if ($request->email)<p class="mt-1 break-all text-[14px]" dir="ltr">{{ $request->email }}</p>@endif
                <p class="mt-2 text-[12.5px] text-[var(--a-muted)]">{{ __('admin.requests.locale') }}: {{ strtoupper($request->locale) }} · {{ $request->created_at?->format('Y-m-d H:i') }}</p>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.requests.follow_up')">
                <div class="space-y-4">
                    <div>
                        <label for="st" class="a-label">{{ __('admin.requests.status') }}</label>
                        <select id="st" wire:model.live="statusId" class="a-input" @disabled(! $canUpdate)>
                            @foreach ($this->statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->getTranslation('name', $locale) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="as" class="a-label">{{ __('admin.requests.assignee') }}</label>
                        <select id="as" wire:model.live="assignee" class="a-input" @disabled(! $canUpdate)>
                            <option value="">{{ __('admin.requests.unassigned') }}</option>
                            @foreach ($this->assignees as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($request instanceof \App\Models\QuoteRequest)
                        <div>
                            <label for="fu" class="a-label">{{ __('admin.requests.follow_up_date') }}</label>
                            <input id="fu" type="date" wire:model.live="followUp" class="a-input" @disabled(! $canUpdate)>
                        </div>
                    @endif
                </div>
            </x-admin.panel>

            <x-admin.panel :title="__('admin.requests.source')">
                <dl class="space-y-2 text-[13.5px]">
                    @foreach (['page', 'landing', 'referrer', 'device'] as $sourceKey)
                        @if (filled($source[$sourceKey] ?? null))
                            <div><dt class="text-[12px] text-[var(--a-muted)]">{{ __('admin.requests.source_'.$sourceKey) }}</dt><dd class="break-all" dir="{{ $sourceKey === 'device' ? 'auto' : 'ltr' }}">{{ $sourceKey === 'device' ? __('admin.requests.devices.'.$source[$sourceKey]) : $source[$sourceKey] }}</dd></div>
                        @endif
                    @endforeach
                    @foreach ((array) ($source['utm'] ?? []) as $utmKey => $utmValue)
                        <div><dt class="text-[12px] text-[var(--a-muted)]">utm_{{ $utmKey }}</dt><dd dir="ltr">{{ $utmValue }}</dd></div>
                    @endforeach
                    @if (! array_filter($source))
                        <p class="text-[var(--a-muted)]">—</p>
                    @endif
                </dl>
            </x-admin.panel>

            <div class="flex flex-wrap gap-3">
                @if ($canUpdate)
                    <button type="button" class="a-btn a-btn-secondary a-btn-sm" wire:click="markUnread">{{ __('admin.requests.mark_unread') }}</button>
                @endif
                @can('delete', $request)
                    <button type="button" class="a-link-danger px-2 text-[13.5px]"
                            @click="$confirm({ title: @js(__('admin.common.confirm_delete_title')), text: @js(__('admin.common.confirm_delete_text', ['name' => $number])), confirmText: @js(__('admin.common.yes_delete')), danger: true }).then(ok => ok && $wire.delete())">
                        {{ __('admin.common.delete') }}
                    </button>
                @endcan
            </div>
        </aside>
    </div>

    <x-admin.modal name="attachment" :title="__('admin.requests.preview')" width="max-w-4xl">
        <template x-if="preview && preview.image"><img :src="preview.url" :alt="preview.name" class="mx-auto max-h-[70vh] rounded-lg"></template>
        <template x-if="preview && ! preview.image"><iframe :src="preview.url" class="h-[70vh] w-full rounded-lg border border-a-border" :title="preview.name"></iframe></template>
    </x-admin.modal>
</div>
