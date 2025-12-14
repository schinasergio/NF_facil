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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade');
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj', 18)->unique();
            $table->string('ie')->nullable(); // Inscrição Estadual
            $table->string('im')->nullable(); // Inscrição Municipal
            $table->string('cnae')->nullable();
            $table->string('regime_tributario'); // 1=Simples, 2=Presumido, 3=Real
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->string('status')->default('ativo'); // ativo, inativo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
