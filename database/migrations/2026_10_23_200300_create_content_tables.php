<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->string('hero_type', 10)->default('none');
            $table->json('blocks')->nullable();
            $table->json('attachments_config')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('template', 10)->default('normal');
            $table->string('status', 12)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('show_in_sitemap')->default(true);
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->json('summary')->nullable();
            $table->json('blocks')->nullable();
            $table->json('scope')->nullable();
            $table->json('stages')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('project_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('slug');
            $table->integer('sort')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->foreignId('category_id')->nullable()->constrained('project_categories')->nullOnDelete();
            $table->json('location')->nullable();
            $table->decimal('area_m2', 10, 2)->nullable();
            $table->json('duration')->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('project_status', 12)->default('completed');
            $table->json('client_name')->nullable();
            $table->boolean('show_client')->default(false);
            $table->json('excerpt')->nullable();
            $table->json('blocks')->nullable();
            $table->json('scope')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->string('status', 12)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('project_service', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'service_id']);
            $table->index('service_id');
        });

        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('slug');
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();
            $table->foreignId('post_category_id')->nullable()->constrained('post_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('reading_minutes')->default(1);
            $table->boolean('is_featured')->default(false)->index();
            $table->string('status', 12)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->json('question');
            $table->json('answer');
            $table->nullableMorphs('faqable');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('company')->nullable();
            $table->json('body');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('url', 500)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('role')->nullable();
            $table->json('bio')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('number', 100)->nullable();
            $table->json('authority')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable()->index();
            $table->string('verify_url', 500)->nullable();
            $table->boolean('show_on_site')->default(true)->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('department')->nullable();
            $table->string('job_type', 12)->default('full_time');
            $table->json('location')->nullable();
            $table->json('description')->nullable();
            $table->json('requirements')->nullable();
            $table->string('status', 12)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->date('closes_at')->nullable()->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->json('cta_label')->nullable();
            $table->json('cta_link')->nullable();
            $table->json('data')->nullable();
            $table->string('source', 10)->default('all');
            $table->unsignedTinyInteger('items_limit')->nullable();
            $table->string('background', 12)->default('white');
            $table->boolean('is_visible')->default(true)->index();
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['home_sections', 'job_posts', 'certificates', 'team_members', 'clients', 'testimonials', 'faqs',
            'posts', 'post_categories', 'project_service', 'projects', 'project_categories', 'services', 'pages'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
