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
        Schema::create('caso_de_teste_passos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_de_teste_id')->constrained('casos_de_teste')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem');
            $table->string('palavra_chave');
            $table->text('texto');
            $table->timestamps();

            $table->unique(['caso_de_teste_id', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caso_de_teste_passos');
    }
};
