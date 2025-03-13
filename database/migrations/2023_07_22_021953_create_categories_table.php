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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            // user id of category owner
            $table->unsignedBigInteger('user_id')->nullable();
            // is actived or not, 0 is not actived, 1 is actived
            $table->boolean('is_actived')->default(false)->required();
            // is root or not, 0 is not root, 1 is root
            $table->boolean('is_root')->default(false)->required();
            // parent id of category
            $table->unsignedBigInteger('parent_id')->nullable();
            // title of category
            $table->string('title', 128)->required(); // maybe not unique fo biq scale
            // other title of category
            $table->text('other_title')->nullable(); // seperated by comma
            // details of category
            $table->text('details')->nullable();
            $table->timestamps();

            // on remove, remove all children childrenRecursive
            // $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade'); // handeled in model
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
