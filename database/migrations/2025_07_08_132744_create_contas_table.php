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
        Schema::create('contas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('familia_id')->constrained('familias')->onDelete('cascade');
            $table->string('nome');
            $table->decimal('saldo_inicial')->default(0.00);
            $table->decimal('saldo_atual')->default(0.00);
            $table->string('tipo');
            $table->decimal('limite_credito')->nullable(); // Limite do cartão de crédito se o tipo for = a cartão de crédito
            $table->text('descricao')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas');
    }
};
