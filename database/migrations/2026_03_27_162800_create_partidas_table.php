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
        Schema::create('partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campeonato_id')->constrained('campeonatos')->cascadeOnDelete();
            $table->enum('fase', ['quartas_de_final', 'semifinal', 'terceiro_lugar', 'final']);
            $table->foreignId('time_mandante_id')->constrained('times')->cascadeOnDelete();
            $table->foreignId('time_visitante_id')->constrained('times')->cascadeOnDelete();
            $table->unsignedTinyInteger('gols_mandante')->nullable();
            $table->unsignedTinyInteger('gols_visitante')->nullable();
            $table->foreignId('time_vencedor_id')->nullable()->constrained('times')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};
