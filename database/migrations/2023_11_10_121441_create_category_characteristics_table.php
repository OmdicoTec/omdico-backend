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
        Schema::create('category_characteristics', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            // foreign keys
            $table->foreignId('category_id')->nullable()->constrained('categories', 'id');
            $table->foreignId('characteristic_id')->nullable()->constrained('characteristics', 'id');
            $table->tinyText('default_value')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_characteristics');
    }
};
