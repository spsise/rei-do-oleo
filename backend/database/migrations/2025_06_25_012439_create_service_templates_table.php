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
        Schema::create('service_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description');
            $table->enum('category', ['maintenance', 'repair', 'inspection', 'emergency', 'preventive', 'general'])->default('general');
            $table->integer('estimated_duration')->nullable()->comment('Duration in minutes');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->text('notes')->nullable();
            $table->json('service_items')->nullable()->comment('Predefined service items');
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['category']);
            $table->index(['active']);
            $table->index(['sort_order']);
            $table->index(['category', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_templates');
    }
};
