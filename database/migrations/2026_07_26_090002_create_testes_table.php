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
        Schema::create('testes', function (Blueprint $table) {
            $table->id();
            $table->string('repo_name');
            $table->unsignedInteger('issue_number');
            $table->string('titulo')->nullable();
            $table->string('status')->default('nao_iniciado');
            $table->decimal('percent_complete', 5, 2)->default(0);
            $table->timestamps();

            $table->index(['repo_name', 'issue_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testes');
    }
};
