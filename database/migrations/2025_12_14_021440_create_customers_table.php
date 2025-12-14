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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('cascade');
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cpf_cnpj', 18)->unique(); // Supports both CPF and CNPJ
            $table->string('ie')->nullable(); // Inscrição Estadual
            $table->string('indicador_ie')->default('9'); // 1=Contribuinte, 2=Isento, 9=Não Contribuinte
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
