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
        Schema::create('status_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // table name
            $table->string('table_name')->nullable();
            // is_approved
            $table->boolean('is_approved')->default(false);
            // is_editable
            $table->boolean('is_editable')->default(false);
            // is_failed
            $table->boolean('is_failed')->default(false);
            // note
            $table->text('note')->nullable();
            // data json
            $table->json('data')->nullable();

            // foreign key to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('restrict');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_infos');
    }
};
