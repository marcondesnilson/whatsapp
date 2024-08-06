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
        Schema::create('chats', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->primary();
            $table->uuid('sessionServer_id');
            $table->uuid('group_id')->nullable();
            $table->uuid('contact_id')->nullable();
            $table->boolean('muted')->default(false);
            $table->boolean('archived')->default(false);
            $table->boolean('pinned')->default(false);
            $table->dateTime('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sessionServer_id')->references('id')->on('session_servers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
