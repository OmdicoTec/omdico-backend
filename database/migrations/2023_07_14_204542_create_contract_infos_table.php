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
        Schema::create('contract_infos', function (Blueprint $table) {
            $table->id();
            // user id
            $table->unsignedBigInteger('user_id');
            // contract number
            // $table->string('contract_number')->nullable();
            // contract image
            $table->string('contract_image')->nullable();
            // start date of contract
            $table->date('start_date')->nullable();
            // end date of contract
            $table->date('end_date')->nullable();
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
        Schema::dropIfExists('contract_infos');
    }
};
