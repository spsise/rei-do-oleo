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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('sku', 50)->unique();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity')->nullable();
            $table->integer('min_stock')->nullable();
            $table->string('unit', 10)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['sku']);
            $table->index(['category_id']);
            $table->index(['active']);
            $table->index(['name']);
            $table->index(['price']);
            $table->index(['stock_quantity']);

            // Full-text search
            $table->fullText(['name', 'description', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
