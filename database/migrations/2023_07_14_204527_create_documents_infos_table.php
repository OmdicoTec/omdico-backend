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
        Schema::create('documents_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // national card image
            $table->string('national_card_image')->nullable();
            // national card image back
            $table->string('national_card_image_back')->nullable();
            // foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents_infos');
    }
};
