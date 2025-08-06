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
        Schema::table('services', function (Blueprint $table) {
            $table->text('description')->nullable()->after('service_number'); // Descrição do serviço
            $table->text('complaint')->nullable()->after('description'); // Reclamação do cliente
            $table->text('diagnosis')->nullable()->after('complaint'); // Diagnóstico do problema
            $table->text('solution')->nullable()->after('diagnosis'); // Solução do problema
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['description', 'complaint', 'diagnosis', 'solution']);
        });
    }
};
