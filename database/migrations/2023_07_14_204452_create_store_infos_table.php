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
        Schema::create('store_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // store name
            $table->string('store_name')->nullable();
            // phone number
            $table->string('phone_number')->nullable();
            // Working days
            $table->string('working_days')->nullable();
            // website
            $table->string('website')->nullable();
            // about store
            $table->string('about_store')->nullable();
            // seller code, created by system randomly and unique
            $table->string('seller_code')->nullable();
            // activity area
            $table->string('activity_area')->nullable();
            // category
            $table->string('category')->nullable();
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
        Schema::dropIfExists('store_infos');
    }
};
