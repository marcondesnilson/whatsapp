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
        Schema::create('webhook_in', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->primary();
            $table->uuid('sessionServer_id')->nullable();
            $table->string('event', 100)->nullable();
            $table->json('dados');
            $table->timestamps();

            $table->foreign('sessionServer_id')->references('id')->on('session_servers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_in');
    }
};
