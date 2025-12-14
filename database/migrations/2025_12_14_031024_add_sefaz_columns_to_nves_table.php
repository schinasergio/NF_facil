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
        Schema::table('nves', function (Blueprint $table) {
            $table->string('protocolo', 100)->nullable()->after('status');
            $table->text('mensagem_sefaz')->nullable()->after('protocolo');
            $table->dateTime('data_recebimento')->nullable()->after('mensagem_sefaz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nves', function (Blueprint $table) {
            //
        });
    }
};
