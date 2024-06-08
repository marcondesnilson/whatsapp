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
        Schema::create('ingredientes', function (Blueprint $table) {
            $table->bigInteger('id')->autoIncrement();
            $table->bigInteger('alimento_id');
            $table->float('quantidade');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('alimento_id')->references('id')->on('alimentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredientes');
    }
};
