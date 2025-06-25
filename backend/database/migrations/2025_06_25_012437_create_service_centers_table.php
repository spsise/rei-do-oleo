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
        Schema::create('service_centers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('slug')->unique();
            $table->string('cnpj', 18)->nullable()->unique();
            $table->string('state_registration', 50)->nullable();
            $table->string('legal_name', 200)->nullable();
            $table->string('trade_name', 150)->nullable();

            // Address fields
            $table->string('address_line')->nullable();
            $table->string('number', 10)->nullable();
            $table->string('complement', 100)->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();

            // Geolocation
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Contact info
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('google_maps_url')->nullable();

            // Management
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('technical_responsible')->nullable();
            $table->date('opening_date')->nullable();
            $table->text('operating_hours')->nullable();

            // Status
            $table->boolean('is_main_branch')->default(false);
            $table->boolean('active')->default(true);
            $table->text('observations')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['code']);
            $table->index(['slug']);
            $table->index(['cnpj']);
            $table->index(['manager_id']);
            $table->index(['active']);
            $table->index(['state', 'city']);
            $table->index(['latitude', 'longitude', 'active']);
            $table->index(['is_main_branch']);

            // Full-text search (nome customizado para evitar limite MySQL)
            $table->fullText(['name', 'legal_name', 'trade_name', 'city', 'neighborhood'], 'service_centers_search_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_centers');
    }
};
