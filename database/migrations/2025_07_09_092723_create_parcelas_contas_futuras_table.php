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
        Schema::create('parcelas_contas_futuras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_futura_id')->constrained('contas_futuras')->cascadeOnDelete();
            $table->unsignedInteger('qtd_parcelas');
            $table->decimal('valor');
            $table->date('vencimento');
            $table->boolean('is_pad')->default(false);
            $table->decimal('valor_pago')->nullable();
            $table->date('pago_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelas_contas_futuras');
    }
};
