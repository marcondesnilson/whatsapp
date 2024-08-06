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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->primary();
            $table->string('message_id')->nullable();
            $table->string('message_type');
            $table->longText('message_body')->nullable();
            $table->uuid('contact_id')->nullable();
            $table->uuid('chat_uuid')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->string('error')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('received_at')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contact_id')->references('id')->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('chat_uuid')->references('uuid')->on('chats')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
