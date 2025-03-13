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
        Schema::create('purchase_offers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            // purchase request id
            // $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests', 'id');
            // user id
            $table->foreignId('user_id')->nullable()->constrained('users', 'id');
            $table->text('details')->nullable();
            $table->tinyText('proposed_price')->nullable();
            // $table->string('status', 32)->default('pending')->index();
            $table->text('image')->nullable(); // -Meta
            // suggested date for delivery
            $table->date('suggested_date')->nullable();
            // confirmed date offered by buyer
            $table->boolean('confirmed_date')->default(false);
            // is supplier winned the offer ? (accepted by buyer)
            $table->boolean('is_winner')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_offers');
    }
};
