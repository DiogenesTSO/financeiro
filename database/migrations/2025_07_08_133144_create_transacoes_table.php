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
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('familia_id')->constrained('familias')->onDelete('cascade');
            $table->foreignId('conta_id')->constrained('contas')->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->string('tipo');
            $table->date('data');
            $table->text('notas')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes');
    }
};
