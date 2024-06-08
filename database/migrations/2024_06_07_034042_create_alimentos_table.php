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
        Schema::create('alimentos', function (Blueprint $table) {
            $table->bigInteger('id')->autoIncrement();
            $table->string('nome');
            $table->float('calorias')->nullable();
            $table->float('proteinas')->nullable();
            $table->float('carboidratos')->nullable();
            $table->float('gorduras')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alimentos');
    }
};
