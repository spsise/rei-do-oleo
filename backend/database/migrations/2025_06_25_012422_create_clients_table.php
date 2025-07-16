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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone01', 20);
            $table->string('phone02', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('cnpj', 18)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['name']);
            $table->index(['phone01']);
            $table->index(['email']);
            $table->index(['cpf']);
            $table->index(['cnpj']);
            $table->index(['active']);
            $table->index(['created_at']);
            $table->index(['state', 'city']);

            // Full-text search
            $table->fullText(['name', 'phone01', 'cpf', 'cnpj']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
