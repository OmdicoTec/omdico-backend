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
        Schema::create('natural_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // first name
            $table->string('first_name')->nullable();
            // last name
            $table->string('last_name')->nullable();
            // national code unique
            $table->string('national_code')->nullable();
            // birth date
            // $table->date('birth_date')->nullable();
            // gender man or women
            // $table->enum('gender', ['man', 'women'])->nullable();
            // mobile number
            $table->string('mobile_number')->nullable();
            // other mobile number format for Notices
            $table->string('other_mobile_number')->nullable();
            // is Legal person or not
            $table->boolean('is_legal_person')->default(false);
            // foreign key to status_infos table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('natural_infos');
    }
};
