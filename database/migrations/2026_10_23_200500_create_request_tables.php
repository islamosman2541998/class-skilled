<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('request_type', 12);
            $table->string('key', 30);
            $table->json('name');
            $table->string('color', 10)->default('gray');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_final')->default(false);
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
            $table->unique(['request_type', 'key']);
        });

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->nullable()->unique();
            $table->string('name');
            $table->string('phone', 40);
            $table->string('email')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('facility_type')->nullable();
            $table->string('project_state')->nullable();
            $table->string('emirate')->nullable();
            $table->string('area_text')->nullable();
            $table->decimal('area_m2', 10, 2)->nullable();
            $table->string('start_when')->nullable();
            $table->text('description')->nullable();
            $table->string('preferred_contact', 12)->nullable();
            $table->string('locale', 5)->default('ar');
            $table->foreignId('status_id')->nullable()->constrained('request_statuses')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('follow_up_at')->nullable();
            $table->json('source')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('locale', 5)->default('ar');
            $table->foreignId('status_id')->nullable()->constrained('request_statuses')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('source')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 40);
            $table->string('email')->nullable();
            $table->string('specialty')->nullable();
            $table->text('message')->nullable();
            $table->string('locale', 5)->default('ar');
            $table->foreignId('status_id')->nullable()->constrained('request_statuses')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('source')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
        });

        Schema::create('request_activities', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 10);
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('site_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index();
            $table->string('path', 500)->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('device', 10)->nullable();
            $table->json('utm')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });

        Schema::create('page_views_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('path', 255);
            $table->string('locale', 5);
            $table->unsignedInteger('views')->default(0);
            $table->unique(['date', 'path', 'locale']);
        });
    }

    public function down(): void
    {
        foreach (['page_views_daily', 'site_events', 'request_activities', 'job_applications', 'contact_messages',
            'quote_requests', 'request_statuses'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
