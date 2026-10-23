<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('location', 20)->unique();
            $table->json('name');
            $table->boolean('use_header_menu')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('type', 20);
            $table->json('label')->nullable();
            $table->nullableMorphs('linkable');
            $table->string('system_route', 60)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('dynamic_source', 30)->nullable();
            $table->string('dynamic_mode', 10)->nullable();
            $table->unsignedTinyInteger('dynamic_limit')->nullable();
            $table->boolean('show_all_link')->default(false);
            $table->boolean('show_descriptions')->default(true);
            $table->boolean('target_blank')->default(false);
            $table->string('visibility', 10)->default('all');
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('menu_item_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->morphs('entry');
            $table->integer('sort')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_entries');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
