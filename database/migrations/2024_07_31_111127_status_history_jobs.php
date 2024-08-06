<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("history_jobs", function (Blueprint $table) {
            $table->uuid('uuid')->after('id');
            $table->string('status')->default('pending')->after('dados');
            $table->integer('wait_time')->nullable()->after('status');
            $table->integer('execution_time')->nullable()->after('wait_time');
            $table->text('error_message')->nullable()->after('execution_time');
        });

        $registros = DB::table('history_jobs')->get();
        foreach($registros as $registro) {
            DB::table('history_jobs')
                ->where('id', $registro->id)
                ->update([
                    'uuid' => Uuid::uuid4(),
                ]);
        }

        Schema::table("history_jobs", function (Blueprint $table) {
            $table->dropColumn('id');
            $table->uuid('uuid')->primary()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("history_jobs", function (Blueprint $table) {
            $table->dropColumn('uuid');
            $table->bigInteger('id')->autoIncrement();
            $table->dropColumn('status');
            $table->dropColumn('wait_time');
            $table->dropColumn('execution_time');
            $table->dropColumn('error_message');
        });
    }
};
