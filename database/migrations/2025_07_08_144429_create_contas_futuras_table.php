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
        Schema::create('contas_futuras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('familia_id')->constrained('familias')->onDelete('cascade');
            $table->foreignId('conta_id')->constrained('contas')->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->string('descricao');
            $table->decimal('valor_total', 15, 2);
            $table->decimal('juros', 5, 2)->nullable();
            $table->decimal('valor_parcelas', 15, 2);
            $table->integer('qtd_parcelas');
            $table->integer('parcelas_pagas')->default(0);
            $table->string('tipo');
            $table->string('frequencia');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->string('status')->default('ativo');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_futuras');
    }
};
