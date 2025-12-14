<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nfe_inutilizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->integer('serie');
            $table->integer('numero_inicial');
            $table->integer('numero_final');
            $table->string('justificativa');
            $table->string('protocolo')->nullable();
            $table->string('status')->default('created'); // created, processed, error
            $table->string('xml_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfe_inutilizations');
    }
};
