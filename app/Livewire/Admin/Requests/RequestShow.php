<?php

namespace App\Livewire\Admin\Requests;

use App\Enums\ActivityType;
use App\Enums\RequestType;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\RequestStatus;
use App\Models\User;
use App\Models\VisitorRequest;
use App\Services\AdminNavigation;
use App\Settings\GeneralSettings;
use App\Support\LinkResolver;
use App\Support\RequestPresenter;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Detail of a visitor request: fields, attachments (signed links), activity
 * timeline with internal notes, and the follow-up panel (status, assignee,
 * follow-up date). Opening it marks the request as read.
 *
 * @property-read VisitorRequest $request
 * @property-read Collection<int, RequestStatus> $statuses
 * @property-read array<int, string> $assignees
 */
#[Layout('layouts.admin')]
abstract class RequestShow extends Component
{
    use WithToasts;

    #[Locked]
    public int $requestId;

    public string $note = '';

    public string $statusId = '';

    public string $assignee = '';

    public string $followUp = '';

    /**
     * @return class-string<VisitorRequest>
     */
    abstract protected function modelClass(): string;

    abstract public function routeKey(): string;

    public function mount(int $id): void
    {
        $this->requestId = $id;
        $request = $this->request;
        $this->authorize('view', $request);

        if ($request->getAttribute('read_at') === null) {
            $request->markAsRead();
            AdminNavigation::forgetCounts();
        }

        $this->statusId = (string) $request->getAttribute('status_id');
        $this->assignee = (string) $request->getAttribute('assigned_to');
        $this->followUp = (string) $request->getAttribute('follow_up_at')?->format('Y-m-d');
    }

    #[Computed]
    public function request(): VisitorRequest
    {
        return ($this->modelClass())::query()
            ->with(['status', 'assignee', 'activities.user', 'media'])
            ->findOrFail($this->requestId);
    }

    public function type(): RequestType
    {
        return RequestPresenter::type($this->request);
    }

    #[Computed]
    public function statuses(): Collection
    {
        return RequestStatus::query()->for($this->type())->get();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function assignees(): array
    {
        return User::query()->where('is_active', true)
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', ['admin', 'sales']))
            ->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * Attachments with signed links: `[{name, size, ext, image, pdf, url, download}]`.
     *
     * @return list<array<string, mixed>>
     */
    public function attachments(): array
    {
        return $this->request->getRelationValue('media')->map(fn (Media $media) => [
            'name' => $media->name.'.'.pathinfo($media->file_name, PATHINFO_EXTENSION),
            'size' => $media->human_readable_size,
            'ext' => strtoupper(pathinfo($media->file_name, PATHINFO_EXTENSION)),
            'image' => str_starts_with((string) $media->mime_type, 'image/'),
            'pdf' => $media->mime_type === 'application/pdf',
            'url' => URL::temporarySignedRoute('admin.requests.file', now()->addMinutes(30), ['media' => $media->id, 'inline' => 1]),
            'download' => URL::temporarySignedRoute('admin.requests.file', now()->addMinutes(30), ['media' => $media->id]),
        ])->values()->all();
    }

    /**
     * Quick actions: WhatsApp with a prefilled message, call, e-mail.
     *
     * @return array<string, string|null>
     */
    public function quickActions(): array
    {
        $request = $this->request;
        $phone = (string) $request->getAttribute('phone');
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        // Local UAE numbers (05x…) become +9715x… for wa.me.
        if (str_starts_with($digits, '05')) {
            $digits = '971'.substr($digits, 1);
        }

        $text = __('admin.requests.whatsapp_text', ['name' => $request->getAttribute('name'), 'number' => RequestPresenter::number($request), 'site' => Translate::pick(app(GeneralSettings::class)->site_name, 'ar')]);

        return [
            'whatsapp' => $digits !== '' ? 'https://wa.me/'.$digits.'?text='.rawurlencode($text) : null,
            'call' => $phone !== '' ? LinkResolver::phoneUrl($phone) : null,
            'email' => filled($request->getAttribute('email')) ? 'mailto:'.$request->getAttribute('email').'?subject='.rawurlencode(RequestPresenter::number($request)) : null,
        ];
    }

    public function addNote(): void
    {
        $this->authorize('update', $this->request);
        $this->validate(['note' => ['required', 'string', 'max:3000']], [], ['note' => __('admin.requests.note')]);

        $this->request->logActivity(ActivityType::Note->value, trim($this->note));
        $this->note = '';
        unset($this->request);
        $this->toast('success', __('admin.requests.note_added'));
    }

    public function updatedStatusId(): void
    {
        $this->authorize('update', $this->request);
        $status = $this->statuses->firstWhere('id', (int) $this->statusId);

        if ($status === null || $status->id === (int) $this->request->getAttribute('status_id')) {
            return;
        }

        $from = $this->request->getRelationValue('status')?->getTranslation('name', 'ar');
        $this->request->forceFill(['status_id' => $status->id])->save();
        $this->request->logActivity(ActivityType::Status->value, null, ['from' => $from, 'to' => $status->getTranslation('name', 'ar')]);
        unset($this->request);
        $this->toast('success', __('admin.requests.status_changed'));
    }

    public function updatedAssignee(): void
    {
        $this->authorize('update', $this->request);
        $userId = $this->assignee === '' ? null : (int) $this->assignee;

        if ($userId !== null && ! array_key_exists($userId, $this->assignees)) {
            return;
        }

        $this->request->forceFill(['assigned_to' => $userId])->save();
        $this->request->logActivity(ActivityType::Assign->value, null, ['to' => $userId ? $this->assignees[$userId] : null]);
        unset($this->request);
        $this->toast('success', __('admin.requests.assigned'));
    }

    public function updatedFollowUp(): void
    {
        $this->authorize('update', $this->request);

        if (! array_key_exists('follow_up_at', $this->request->getAttributes())) {
            return;
        }

        $this->validate(['followUp' => ['nullable', 'date']]);
        $this->request->forceFill(['follow_up_at' => $this->followUp ?: null])->save();
        $this->request->logActivity(ActivityType::System->value, null, ['event' => 'follow_up', 'date' => $this->followUp ?: null]);
        unset($this->request);
        $this->toast('success', __('admin.common.saved'));
    }

    public function markUnread(): void
    {
        $this->authorize('update', $this->request);
        $this->request->forceFill(['read_at' => null])->saveQuietly();
        AdminNavigation::forgetCounts();
        $this->redirectRoute('admin.'.$this->routeKey().'.index', navigate: true);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->request);
        $this->request->delete();
        AdminNavigation::forgetCounts();
        session()->flash('toast', ['type' => 'success', 'message' => __('admin.common.deleted')]);
        $this->redirectRoute('admin.'.$this->routeKey().'.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.requests.show', [
            'number' => RequestPresenter::number($this->request),
            'fields' => RequestPresenter::fields($this->request, app()->getLocale()),
        ])->title(RequestPresenter::number($this->request));
    }
}
