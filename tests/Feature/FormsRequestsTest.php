<?php

use App\Enums\RequestType;
use App\Livewire\Admin\Requests\Quotes\Index as QuotesIndex;
use App\Livewire\Admin\Requests\Quotes\Show as QuoteShow;
use App\Livewire\Admin\Requests\Statuses;
use App\Livewire\Site\ContactForm;
use App\Livewire\Site\JobApplicationForm;
use App\Livewire\Site\QuoteForm;
use App\Mail\NewRequestNotification;
use App\Mail\RequestAutoReply;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\QuoteRequest;
use App\Models\RequestStatus;
use App\Models\Service;
use App\Models\SiteEvent;
use App\Support\LocalizedRoute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('private');
    Storage::fake('local');
    Mail::fake();
    RateLimiter::clear('form:quote:'.hash_hmac('sha256', '127.0.0.1', (string) config('app.key')));
});

function filledQuote(): Testable
{
    $service = Service::create(['title' => ['ar' => 'عزل الأسطح', 'en' => 'Roof insulation']]);

    return Livewire::test(QuoteForm::class)
        ->set('service_id', $service->id)->set('facility_type', '0')->set('project_state', '1')
        ->call('next')->assertSet('step', 2)
        ->set('emirate', '0')->set('area_m2', '250')->set('start_when', '1')
        ->call('next')->assertSet('step', 3)
        ->set('newFiles', [UploadedFile::fake()->create('plan.pdf', 300, 'application/pdf'), UploadedFile::fake()->image('site.jpg')])
        ->assertCount('files', 2)
        ->call('next')->assertSet('step', 4)
        ->set('name', 'أحمد علي')->set('phone', '050 123 4567')->set('email', 'client@example.com');
}

test('quote steps validate before moving on', function () {
    Livewire::test(QuoteForm::class)
        ->call('next')
        ->assertHasErrors(['service_id', 'facility_type', 'project_state'])
        ->assertSet('step', 1);
});

test('quote form preselects the service from the query string', function () {
    $service = Service::create(['title' => ['ar' => 'إيبوكسي', 'en' => 'Epoxy']]);

    Livewire::withQueryParams(['service' => $service->id])->test(QuoteForm::class)->assertSet('service_id', $service->id);
    $this->get(LocalizedRoute::url('quote', 'ar').'?service='.$service->id)->assertOk()->assertSee('إيبوكسي');
});

test('a quote request is stored with files, status, activity, event and mails', function () {
    $form = filledQuote();
    $this->travel(5)->seconds();
    $form->call('submit')->assertHasNoErrors()->assertSet('number', 'QR-0001');

    $quote = QuoteRequest::query()->with('media')->firstOrFail();
    expect($quote->status?->is_default)->toBeTrue()
        ->and($quote->emirate)->toBe('أبوظبي')
        ->and($quote->facility_type)->toBe('فيلا')
        ->and((float) $quote->area_m2)->toBe(250.0)
        ->and($quote->getMedia('attachments'))->toHaveCount(2)
        ->and($quote->getFirstMedia('attachments')->disk)->toBe('private')
        ->and($quote->activities()->count())->toBe(1)
        ->and(SiteEvent::query()->where('type', 'quote_submit')->count())->toBe(1);

    app()->terminate();
    Mail::assertSent(NewRequestNotification::class, fn ($mail) => $mail->hasTo('classskilled@gmail.com'));
    Mail::assertSent(RequestAutoReply::class, fn ($mail) => $mail->hasTo('client@example.com') && $mail->locale === 'ar');
});

test('auto-reply follows the visitor locale', function () {
    $this->refreshApplicationWithLocale('en');
    Mail::fake();
    Storage::fake('private');
    app()->setLocale('en');

    $form = filledQuote();
    $this->travel(5)->seconds();
    $form->call('submit')->assertHasNoErrors();

    app()->terminate();
    Mail::assertSent(RequestAutoReply::class, fn ($mail) => $mail->locale === 'en' && str_contains($mail->envelope()->subject, 'QR-'));
});

test('spam protection: honeypot, minimum fill time and rate limit', function () {
    $form = filledQuote();
    $form->call('submit')->assertHasErrors('form');
    expect(QuoteRequest::count())->toBe(0);

    $this->travel(5)->seconds();
    $form->set('website', 'http://spam.example')->call('submit')->assertHasErrors('form');
    expect(QuoteRequest::count())->toBe(0);

    RateLimiter::clear('form:contact:'.hash_hmac('sha256', '127.0.0.1', (string) config('app.key')));
    foreach (range(1, 6) as $i) {
        $contact = Livewire::test(ContactForm::class)->set('name', 'زائر '.$i)->set('phone', '0501234567')->set('message', 'رسالة تجريبية رقم '.$i);
        $this->travel(5)->seconds();
        $contact->call('submit');
    }
    expect(ContactMessage::count())->toBe(5);
});

test('contact form stores the message and validates inline', function () {
    Livewire::test(ContactForm::class)->set('email', 'not-an-email')->assertHasErrors('email');

    $form = Livewire::test(ContactForm::class)
        ->set('name', 'سارة')->set('email', 'sara@example.com')->set('subject', '1')->set('message', 'أرغب في زيارة للموقع');
    $this->travel(5)->seconds();
    $form->call('submit')->assertHasNoErrors()->assertSet('sent', true);

    $message = ContactMessage::query()->firstOrFail();
    expect($message->subject)->toBe('استفسار عن خدمة')->and($message->status_id)->not->toBeNull();

    $this->get(LocalizedRoute::url('contact', 'ar'))->assertOk()->assertSee('wire:submit="submit"', false);
});

test('job applications need a PDF CV and closed jobs refuse them', function () {
    $job = JobPost::create(['title' => ['ar' => 'مهندس', 'en' => 'Engineer'], 'job_type' => 'full_time', 'status' => 'published', 'published_at' => now()->subDay()]);
    $closed = JobPost::create(['title' => ['ar' => 'محاسب', 'en' => 'Accountant'], 'job_type' => 'full_time', 'status' => 'published', 'published_at' => now()->subMonth(), 'closes_at' => now()->subDay()]);

    Livewire::test(JobApplicationForm::class, ['jobId' => $job->id])
        ->set('cv', UploadedFile::fake()->create('cv.docx', 100, 'application/msword'))
        ->assertHasErrors('cv');

    $form = Livewire::test(JobApplicationForm::class, ['jobId' => $job->id])
        ->set('name', 'خالد')->set('phone', '+971 50 111 2233')->set('email', 'k@example.com')
        ->set('cv', UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'));
    $this->travel(5)->seconds();
    $form->call('submit')->assertHasNoErrors()->assertSet('sent', true);

    expect(JobApplication::query()->firstOrFail()->getFirstMedia('cv')?->disk)->toBe('private');

    Livewire::test(JobApplicationForm::class, ['jobId' => $closed->id])->assertSee(__('forms.job_closed'));
    $this->get($job->localizedUrl('ar'))->assertOk()->assertSee('wire:model="cv"', false);
});

function sampleQuote(): QuoteRequest
{
    Storage::fake('private');
    $quote = QuoteRequest::create(['name' => 'عميل', 'phone' => '0501112233', 'email' => 'c@example.com', 'emirate' => 'دبي', 'locale' => 'ar', 'status_id' => RequestStatus::defaultFor(RequestType::Quote)?->id]);
    $quote->addMedia(UploadedFile::fake()->create('plan.pdf', 50, 'application/pdf'))->toMediaCollection('attachments');

    return $quote->refresh();
}

test('sales can list, filter, export and follow up quotes but not delete', function () {
    $sales = userWithRole('sales');
    $quote = sampleQuote();
    QuoteRequest::create(['name' => 'آخر', 'phone' => '0509998877', 'emirate' => 'أبوظبي', 'locale' => 'ar']);
    $this->actingAs($sales);

    Livewire::test(QuotesIndex::class)
        ->assertSee('عميل')->assertSee('آخر')
        ->call('setFilter', 'emirate', 'دبي')
        ->assertSee('عميل')->assertDontSee('آخر')
        ->call('setFilter', 'files', '1')
        ->assertSee('عميل')
        ->call('export')->assertFileDownloaded();

    $next = RequestStatus::query()->for(RequestType::Quote)->where('is_default', false)->firstOrFail();

    Livewire::test(QuoteShow::class, ['id' => $quote->id])
        ->set('note', 'اتصلت بالعميل')->call('addNote')->assertHasNoErrors()
        ->set('statusId', (string) $next->id)
        ->set('assignee', (string) $sales->id)
        ->set('followUp', now()->addDays(3)->toDateString())
        ->assertSee('اتصلت بالعميل');

    $quote->refresh();
    expect($quote->read_at)->not->toBeNull()
        ->and($quote->status_id)->toBe($next->id)
        ->and($quote->assigned_to)->toBe($sales->id)
        ->and($quote->follow_up_at)->not->toBeNull()
        ->and($quote->activities()->pluck('type')->map->value->all())->toContain('note', 'status', 'assign');

    Livewire::test(QuoteShow::class, ['id' => $quote->id])->call('delete')->assertForbidden();
    Livewire::test(Statuses::class)->assertForbidden();
});

test('editors cannot open requests; attachments need a signed link and permission', function () {
    $quote = sampleQuote();
    $media = $quote->getFirstMedia('attachments');
    $signed = URL::temporarySignedRoute('admin.requests.file', now()->addMinutes(5), ['media' => $media->id]);

    $this->actingAs(userWithRole('editor'))->get(route('admin.quotes.index'))->assertForbidden();
    $this->get($signed)->assertForbidden();

    $this->actingAs(userWithRole('sales'));
    $this->get(route('admin.requests.file', ['media' => $media->id]))->assertForbidden();
    $this->get($signed)->assertOk()->assertDownload();
    $this->get(route('admin.quotes.show', $quote->id))->assertOk()->assertSee('QR-');
});

test('admins manage statuses with a single default', function () {
    $this->actingAs(userWithRole('admin'));

    Livewire::test(Statuses::class)
        ->call('create')
        ->set('form.name', ['ar' => 'بانتظار الدفعة', 'en' => 'Awaiting payment'])
        ->set('form.color', 'amber')
        ->set('form.is_default', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('بانتظار الدفعة');

    expect(RequestStatus::query()->for(RequestType::Quote)->where('is_default', true)->get()->map(fn ($s) => $s->getTranslation('name', 'ar'))->all())->toBe(['بانتظار الدفعة']);
});

test('message and application details open for every request type', function () {
    Storage::fake('private');
    $this->actingAs(userWithRole('admin'));
    $message = ContactMessage::create(['name' => 'زائر', 'email' => 'v@example.com', 'message' => 'رسالة تجريبية', 'locale' => 'ar']);
    $application = JobApplication::create(['name' => 'متقدم', 'phone' => '0501234567', 'email' => 'a@example.com', 'locale' => 'ar']);
    $application->addMedia(UploadedFile::fake()->create('cv.pdf', 40, 'application/pdf'))->toMediaCollection('cv');

    $this->get(route('admin.messages.show', $message->id))->assertOk()->assertSee('رسالة تجريبية');
    $this->get(route('admin.applications.show', $application->id))->assertOk()->assertSee('cv');
});
