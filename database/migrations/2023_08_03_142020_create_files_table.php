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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            // user id
            $table->unsignedBigInteger('user_id')->nullable();
            // file alt
            $table->string('alt')->nullable();
            // file category
            $table->string('category')->nullable();
            // file path
            $table->string('path')->nullable();
            // file type
            $table->string('type')->nullable();
            // is chunked
            $table->boolean('is_chunked')->default(true);
            // is approved by admin
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
