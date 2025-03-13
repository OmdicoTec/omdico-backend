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
        Schema::create('legal_infos', function (Blueprint $table) {
            $table->id();
            // foreign key to users table
            $table->unsignedBigInteger('user_id');
            // company name
            $table->string('company_name')->nullable();
            // company type options: 6 types of companies 1-6
            $table->enum('company_type', [1, 2, 3, 4, 5, 6])->nullable();
            // registration number
            $table->string('registration_number', 25)->nullable();
            // national code (national code of company)
            $table->string('national_code', 25)->nullable();
            // economic code
            $table->string('economic_code', 25)->nullable();
            // signatory
            $table->string('signatory', 25)->nullable();
            // store name (optional)
            $table->string('store_name')->nullable();
            // foreign key to status_infos table
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_infos');
    }
};
