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
        Schema::create('cenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teste_id')->constrained('testes')->cascadeOnDelete();
            $table->foreignId('caso_de_teste_id')->nullable()->constrained('casos_de_teste')->nullOnDelete();
            $table->foreignId('cloned_from_cenario_id')->nullable()->constrained('cenarios')->nullOnDelete();
            $table->string('titulo');
            $table->json('passos_snapshot');
            $table->string('status')->default('a_fazer');
            $table->string('severidade');
            $table->timestamps();

            $table->index('teste_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cenarios');
    }
};
