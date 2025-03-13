<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            // id starts from 1030
            $table->id();
            $table->timestamps();
            $table->string('title', 512)->nullable(); // Performance sensitive search field
            // constrained type is unsignedBigInteger
            $table->foreignId('category_id')->nullable()->constrained('categories', 'id');
            $table->tinyText('category')->nullable();
            $table->text('details')->nullable();
            $table->tinyText('amount')->nullable();
            $table->string('quality', 32)->nullable(); // -Meta
            $table->date('delivery_date')->nullable();
            $table->timestampTz('active_time')->nullable(); // with timezone, after active with admin add 72 hours to get proposal end time
            $table->tinyText('proposed_price')->nullable();
            $table->string('status', 32)->default('pending')->index(); // in next update this will be enum
            $table->text('features')->nullable(); // -Meta
            $table->text('image')->nullable(); // -Meta

            // user id
            $table->foreignId('user_id')->nullable()->constrained('users', 'id');
            // purchase offers
            // TODO: must run this migration after purchase_offers migration
            $table->foreignId('purchase_offer_id')->nullable()->constrained('purchase_offers', 'id');
        });

        DB::statement("ALTER TABLE purchase_requests AUTO_INCREMENT = 1030;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
