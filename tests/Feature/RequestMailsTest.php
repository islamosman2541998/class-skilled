<?php

use App\Mail\NewRequestNotification;
use App\Mail\RequestAutoReply;
use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Services\RequestIntake;
use App\Settings\NotificationSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
});

function mailQuote(array $attributes = []): QuoteRequest
{
    $service = Service::create(['title' => ['ar' => 'عزل الأسطح', 'en' => 'Roof insulation']]);

    return QuoteRequest::query()->create([
        'name' => 'أحمد علي',
        'phone' => '050 123 4567',
        'email' => 'client@example.com',
        'service_id' => $service->id,
        'facility_type' => 'فيلا',
        'project_state' => 'أرض فارغة',
        'emirate' => 'أبوظبي',
        'area_text' => 'مصفح',
        'area_m2' => 250,
        'start_when' => 'خلال شهر',
        'preferred_contact' => 'whatsapp',
        'description' => "عزل سطح فيلا دورين\nمع ضمان",
        'locale' => 'ar',
        'source' => ['page' => '/طلب-عرض-سعر', 'device' => 'mobile'],
        ...$attributes,
    ]);
}

test('the team e-mail groups every detail and offers call, WhatsApp, reply and dashboard actions', function () {
    $quote = mailQuote();
    $html = (new NewRequestNotification($quote))->render();

    expect($html)
        ->toContain('dir="rtl"')
        ->toContain($quote->number)
        ->toContain(__('mail.section_contact', [], 'ar'))
        ->toContain(__('mail.section_details_quote', [], 'ar'))
        ->toContain('أحمد علي')
        ->toContain('عزل الأسطح')
        ->toContain('مصفح')
        ->toContain('عزل سطح فيلا دورين<br')
        ->toContain('href="tel:0501234567"')
        ->toContain('href="https://wa.me/971501234567"')
        ->toContain('href="mailto:client@example.com"')
        ->toContain(route('admin.quotes.show', $quote->id))
        ->toContain('/طلب-عرض-سعر');
});

test('the team e-mail attaches the visitor files up to 10 MB and lists bigger ones only', function () {
    $quote = mailQuote();
    $quote->addMedia(UploadedFile::fake()->create('plan.pdf', 300, 'application/pdf'))->toMediaCollection('attachments');

    $mail = new NewRequestNotification($quote->fresh());
    expect($mail->attachments())->toHaveCount(1)
        ->and($mail->render())->toContain('plan.pdf');

    // Fake files are stored empty, so the recorded size is set to what an 11 MB upload would have.
    $quote->addMedia(UploadedFile::fake()->create('big.pdf', 10, 'application/pdf'))->toMediaCollection('attachments')
        ->forceFill(['size' => 11 * 1024 * 1024])->save();
    $mail = new NewRequestNotification($quote->fresh());
    expect($mail->attachments())->toHaveCount(0)
        ->and($mail->render())->toContain('big.pdf')->toContain(__('mail.attachments_in_dashboard', [], 'ar'));
});

test('the auto-reply is in the visitor language with a summary of the request', function () {
    $quote = mailQuote(['locale' => 'en']);
    $html = (new RequestAutoReply($quote))->render();

    expect($html)
        ->toContain('dir="ltr"')
        ->toContain($quote->number)
        ->toContain(__('mail.your_request', [], 'en'))
        ->toContain('Roof insulation')
        ->toContain('Villa')
        ->not->toContain('فيلا')
        ->not->toContain('050 123 4567');

    expect((new RequestAutoReply(mailQuote(['locale' => 'ar'])))->render())->toContain('dir="rtl"')->toContain('عزل الأسطح');
});

test('both e-mails have a plain-text version', function () {
    $quote = mailQuote();

    expect((new NewRequestNotification($quote))->content()->text)->toBe('mail.requests.notification-text')
        ->and((new RequestAutoReply($quote))->content()->text)->toBe('mail.requests.autoreply-text');

    $text = view('mail.requests.notification-text', (new NewRequestNotification($quote))->content()->with)->render();
    expect($text)->toContain('أحمد علي')->toContain($quote->number)->not->toContain('<table');
});

test('every address in the settings gets the team e-mail and the visitor only gets one when they gave an e-mail', function () {
    Mail::fake();
    $settings = app(NotificationSettings::class);
    $settings->quote_emails = ['info@class-skilled.com', 'classskilled@gmail.com'];
    $settings->save();

    app(RequestIntake::class)->sendMails(mailQuote());
    Mail::assertSent(NewRequestNotification::class, fn ($mail) => $mail->hasTo('info@class-skilled.com') && $mail->hasTo('classskilled@gmail.com'));
    Mail::assertSent(RequestAutoReply::class, 1);

    app(RequestIntake::class)->sendMails(mailQuote(['email' => null]));
    Mail::assertSent(NewRequestNotification::class, 2);
    Mail::assertSent(RequestAutoReply::class, 1);
});

test('contact messages and job applications use the same design with their own fields', function () {
    $message = ContactMessage::query()->create(['name' => 'سارة', 'phone' => '+971 52 111 2222', 'email' => 's@example.com', 'subject' => 'استفسار', 'message' => 'هل تعملون في العين؟', 'locale' => 'ar']);
    expect((new NewRequestNotification($message))->render())
        ->toContain(__('mail.section_details_message', [], 'ar'))
        ->toContain('هل تعملون في العين؟')
        ->toContain('href="https://wa.me/971521112222"');

    $job = JobPost::query()->create(['title' => ['ar' => 'مهندس موقع', 'en' => 'Site engineer'], 'status' => 'published']);
    $application = JobApplication::query()->create(['job_post_id' => $job->id, 'name' => 'خالد', 'phone' => '0559998888', 'specialty' => 'مدني', 'message' => 'خبرة 5 سنوات', 'locale' => 'en']);
    $application->addMedia(UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'))->toMediaCollection('cv');

    $mail = new NewRequestNotification($application->fresh());
    expect($mail->render())->toContain(__('mail.section_details_application', [], 'ar'))->toContain('مهندس موقع')->toContain('cv.pdf')
        ->and($mail->attachments())->toHaveCount(1)
        ->and((new RequestAutoReply($application))->render())->toContain('Site engineer');
});
