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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('license_plate', 8)->unique();
            $table->string('brand', 50)->nullable();
            $table->string('model', 100)->nullable();
            $table->year('year')->nullable();
            $table->string('color', 30)->nullable();
            $table->string('fuel_type', 20)->nullable();
            $table->integer('mileage')->nullable();
            $table->date('last_service')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['license_plate']);
            $table->index(['client_id']);
            $table->index(['brand', 'model']);
            $table->index(['last_service']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
