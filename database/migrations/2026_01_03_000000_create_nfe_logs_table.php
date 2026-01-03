<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nfe_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nfe_id')->constrained('nves')->onDelete('cascade');
            $table->string('status'); // processing, authorized, rejected, etc.
            $table->string('protocolo')->nullable();
            $table->text('message')->nullable();
            $table->longText('payload')->nullable(); // JSON or XML content for deeper debugging
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfe_logs');
    }
};
