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
        Schema::create('nfe_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nfe_id')->constrained('nves')->onDelete('cascade');
            $table->string('status');
            $table->text('message')->nullable();
            $table->text('payload')->nullable();
            $table->string('protocolo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfe_logs');
    }
};
