<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->default('ar')->after('password');
            $table->boolean('is_active')->default(true)->index()->after('locale');
            $table->boolean('force_two_factor')->default(false)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('force_two_factor');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locale', 'is_active', 'force_two_factor', 'last_login_at']);
        });
    }
};
