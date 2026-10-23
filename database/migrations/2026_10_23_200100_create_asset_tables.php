<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->json('name');
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('kind', 20)->index();
            $table->string('original_name')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->string('external_url', 500)->nullable();
            $table->string('provider', 20)->nullable();
            $table->string('external_id', 64)->nullable();
            $table->foreignId('poster_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->json('alt')->nullable();
            $table->json('title')->nullable();
            $table->json('caption')->nullable();
            $table->string('focal', 10)->default('center');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('created_at');
        });

        Schema::create('assetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('assetable_type');
            $table->unsignedBigInteger('assetable_id');
            $table->string('collection', 100);
            $table->integer('sort')->default(0);
            $table->json('meta')->nullable();
            $table->unique(['asset_id', 'assetable_type', 'assetable_id', 'collection'], 'assetables_unique');
            $table->index(['assetable_type', 'assetable_id', 'collection'], 'assetables_owner');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assetables');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('media_folders');
    }
};
