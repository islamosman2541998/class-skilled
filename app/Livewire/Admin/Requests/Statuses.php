<?php

namespace App\Livewire\Admin\Requests;

use App\Enums\RequestType;
use App\Enums\StatusColor;
use App\Livewire\Admin\Concerns\WithToasts;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\QuoteRequest;
use App\Models\RequestStatus;
use App\Support\Translate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Request statuses per type: name AR/EN, colour, order (drag & drop), default, final.
 *
 * @property-read Collection<int, RequestStatus> $statuses
 * @property-read array<int, int> $usage
 */
#[Layout('layouts.admin')]
class Statuses extends Component
{
    use WithToasts;

    #[Url]
    public string $type = 'quote';

    public ?int $editingId = null;

    /** @var array{name: array{ar: string, en: string}, color: string, is_default: bool, is_final: bool} */
    public array $form = ['name' => ['ar' => '', 'en' => ''], 'color' => 'sky', 'is_default' => false, 'is_final' => false];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage request statuses'), 403);
        $this->type = (RequestType::tryFrom($this->type) ?? RequestType::Quote)->value;
    }

    public function requestType(): RequestType
    {
        return RequestType::from($this->type);
    }

    #[Computed]
    public function statuses(): Collection
    {
        return RequestStatus::query()->for($this->requestType())->get();
    }

    /**
     * Number of requests per status (deletion is only allowed when unused).
     *
     * @return array<int, int>
     */
    #[Computed]
    public function usage(): array
    {
        $model = match ($this->requestType()) {
            RequestType::Quote => QuoteRequest::class,
            RequestType::Message => ContactMessage::class,
            RequestType::Application => JobApplication::class,
        };

        return $model::query()->withTrashed()->toBase()
            ->select('status_id', DB::raw('count(*) as aggregate'))->groupBy('status_id')
            ->pluck('aggregate', 'status_id')->map(fn ($n) => (int) $n)->all();
    }

    public function setType(string $type): void
    {
        $this->type = (RequestType::tryFrom($type) ?? RequestType::Quote)->value;
        $this->cancel();
        unset($this->statuses, $this->usage);
    }

    public function create(): void
    {
        $this->editingId = 0;
        $this->form = ['name' => ['ar' => '', 'en' => ''], 'color' => 'sky', 'is_default' => false, 'is_final' => false];
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $status = $this->statuses->firstWhere('id', $id);
        abort_if($status === null, 404);

        $this->editingId = $id;
        $this->form = [
            'name' => Translate::pair($status->getTranslations('name')),
            'color' => $status->color->value,
            'is_default' => $status->is_default,
            'is_final' => $status->is_final,
        ];
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->editingId = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('manage request statuses'), 403);

        $this->validate([
            'form.name.ar' => ['required', 'string', 'max:60'],
            'form.name.en' => ['nullable', 'string', 'max:60'],
            'form.color' => ['required', Rule::in(array_column(StatusColor::cases(), 'value'))],
            'form.is_default' => ['boolean'],
            'form.is_final' => ['boolean'],
        ], [], ['form.name.ar' => __('admin.requests.status_name')]);

        DB::transaction(function (): void {
            $status = $this->editingId ? RequestStatus::query()->findOrFail($this->editingId) : new RequestStatus([
                'request_type' => $this->requestType(),
                'key' => 'custom-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'sort' => (int) $this->statuses->max('sort') + 1,
            ]);

            abort_if($status->request_type !== $this->requestType(), 404);

            $status->fill([
                'name' => Translate::pair($this->form['name']),
                'color' => $this->form['color'],
                'is_default' => (bool) $this->form['is_default'],
                'is_final' => (bool) $this->form['is_final'],
            ])->save();

            // Exactly one default per type.
            if ($status->is_default) {
                RequestStatus::query()->where('request_type', $this->type)->whereKeyNot($status->id)->update(['is_default' => false]);
            }
        });

        $this->editingId = null;
        unset($this->statuses);
        $this->toast('success', __('admin.common.saved'));
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function reorder(array $ids): void
    {
        abort_unless(auth()->user()?->can('manage request statuses'), 403);
        $own = $this->statuses->pluck('id')->all();

        foreach (array_values($ids) as $index => $id) {
            if (in_array((int) $id, $own, true)) {
                RequestStatus::query()->whereKey((int) $id)->update(['sort' => $index + 1]);
            }
        }

        unset($this->statuses);
        $this->toast('success', __('admin.common.reordered'));
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('manage request statuses'), 403);
        $status = $this->statuses->firstWhere('id', $id);

        if ($status === null || ($this->usage[$id] ?? 0) > 0 || $status->is_default || $this->statuses->count() <= 1) {
            $this->toast('error', __('admin.requests.status_in_use'));

            return;
        }

        $status->delete();
        unset($this->statuses);
        $this->toast('success', __('admin.common.deleted'));
    }

    public function render(): View
    {
        return view('livewire.admin.requests.statuses')->title(__('admin.nav.request_statuses'));
    }
}
