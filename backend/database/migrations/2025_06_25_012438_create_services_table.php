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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_center_id')->constrained()->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('attendant_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('service_number', 20)->unique();

            // Timing
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Status and payment
            $table->foreignId('service_status_id')->constrained('service_statuses');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods');

            // Vehicle info at service time
            $table->integer('mileage_at_service')->nullable();

            // Financial
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('final_amount', 10, 2)->nullable();

            // Additional info
            $table->text('observations')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['service_number']);
            $table->index(['client_id']);
            $table->index(['vehicle_id']);
            $table->index(['service_center_id']);
            $table->index(['technician_id']);
            $table->index(['attendant_id']);
            $table->index(['service_status_id']);
            $table->index(['scheduled_at']);
            $table->index(['service_status_id', 'scheduled_at']);
            $table->index(['service_center_id', 'scheduled_at']);
            $table->index(['active']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
