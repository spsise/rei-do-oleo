<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('active');
            $table->unsignedBigInteger('service_center_id')->nullable()->after('last_login_at');
            $table->softDeletes();

            // Indexes
            $table->index(['active']);
            $table->index(['service_center_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['active', 'last_login_at', 'service_center_id']);
        });
    }
};
