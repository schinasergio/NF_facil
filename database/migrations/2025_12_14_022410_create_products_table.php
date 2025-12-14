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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo_sku')->nullable()->index();
            $table->string('ncm', 8);
            $table->string('cest', 7)->nullable();
            $table->string('unidade', 10)->default('UN');
            $table->decimal('preco_venda', 10, 2);
            $table->integer('origem')->default(0); // 0=Nacional
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
