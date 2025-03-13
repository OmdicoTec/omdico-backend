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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // user id of product owner
            $table->unsignedBigInteger('user_id')->required();
            // title of prodouct
            $table->string('title', 128)->required();
            // category of product (foreign key to categories table)
            $table->unsignedBigInteger('category_id')->required();
            $table->string('category')->nullable();
            // features of product json format, color, type, size, garantee is default and required but have more features like weight, height, width, length, etc is optional
            $table->json('features')->nullable();
            // depo of product
            $table->integer('depo')->default(0)->nullable();
            // details of product
            $table->text('details')->nullable();
            // media of product
            $table->json('media')->nullable();
            // product inventory (stock), limited is true if product is limited and quantity is number of product in stock
            $table->boolean('limited')->default(false)->required();
            $table->integer('quantity')->default(0)->nullable();
            // time of product is available per each product quantity, json format
            $table->json('time')->nullable();
            // commeision of product
            $table->string('commission')->nullable(); // TODO: in next version, deploy this field

            // price of product, not impelmented in this version
            $table->integer('price')->default(0)->nullable();

            // product is actived or not, 0 is not actived, 1 is actived
            $table->boolean('is_actived')->default(false)->required();
            // product is approved or not, 0 is not approved, 1 is approved
            $table->boolean('is_approved')->default(false)->required();
            // product is rejected or not, 0 is not rejected, 1 is rejected
            $table->boolean('is_rejected')->default(false)->required();
            // product comment from admin
            $table->text('comment')->nullable();
            // new data of product, if product is edited, this field is filled
            $table->json('new_data')->nullable();

            $table->timestamps();

            // foreign key to users table
            $table->foreign('user_id')->references('id')->on('users');
            // foreign key to categories table
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
