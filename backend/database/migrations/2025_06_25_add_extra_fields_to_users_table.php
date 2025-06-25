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
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('whatsapp', 20)->nullable()->after('phone');
            $table->string('document', 20)->nullable()->after('whatsapp');
            $table->date('birth_date')->nullable()->after('document');
            $table->date('hire_date')->nullable()->after('birth_date');
            $table->decimal('salary', 10, 2)->nullable()->after('hire_date');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('salary');
            $table->json('specialties')->nullable()->after('commission_rate');

            // Indexes for performance
            $table->index(['phone']);
            $table->index(['document']);
            $table->index(['active', 'service_center_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'whatsapp',
                'document',
                'birth_date',
                'hire_date',
                'salary',
                'commission_rate',
                'specialties'
            ]);
        });
    }
};
