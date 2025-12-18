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
        Schema::create('nfses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // RPS (Recibo Provisório de Serviços)
            $table->integer('rps_numero')->nullable();
            $table->string('rps_serie', 5)->nullable();
            $table->integer('rps_tipo')->default(1); // 1-RPS

            // NFSe Definitive Data
            $table->string('numero_nfse', 20)->nullable();
            $table->string('codigo_verificacao', 20)->nullable();
            $table->datetime('data_emissao')->nullable();
            $table->string('status', 20)->default('pendente'); // pendente, processamento, autorizada, cancelada, erro

            // Values
            $table->decimal('valor_servico', 15, 2);
            $table->decimal('valor_iss', 15, 2)->default(0);
            $table->decimal('aliquota_iss', 5, 2)->default(0);

            // Files
            $table->string('xml_path')->nullable();
            $table->string('link_pdf')->nullable();

            // Response
            $table->text('mensagem_retorno')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfses');
    }
};
