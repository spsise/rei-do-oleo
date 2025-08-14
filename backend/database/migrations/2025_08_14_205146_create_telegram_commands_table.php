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
        Schema::create('telegram_commands', function (Blueprint $table) {
            $table->id();
            $table->string('command_id')->unique();
            $table->json('aliases');
            $table->text('description');
            $table->string('action_handler');
            $table->string('action_method');
            $table->json('action_parameters')->nullable();
            $table->json('permissions');
            $table->string('category');
            $table->json('voice_settings')->nullable();
            $table->json('natural_language')->nullable();
            $table->json('fallback')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(1);
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['command_id', 'is_active']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_commands');
    }
};
