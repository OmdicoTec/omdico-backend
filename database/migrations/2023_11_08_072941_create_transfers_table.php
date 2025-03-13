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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            // user id
            $table->foreignId('user_id')->nullable()->constrained('users', 'id');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices', 'id');
            $table->enum('type', [
                'prepay',
                'checkout',
                'advance_request',
                'settlement_request',
                'deposit',
                'withdraw'
            ])->default('advance_request');

            // requset created by buyer, seller or self
            $table->enum('from', ['buyer', 'seller', 'self'])->default('self');
            // bank status
            $table->enum('bank_status', ['pending', 'approved', 'rejected'])->default('pending');
            // bank message
            $table->tinyText('bank_message')->nullable();
            $table->tinyText('amount')->nullable();
            // verfication time by check transaction id in bank
            $table->timestamp('verify_at')->nullable();
            // tracking code for bank receipts
            $table->tinyText('tracking_code')->nullable();
            // uuid for bank receipts
            $table->tinyText('uuid')->nullable();
            // transaction id for bank receipts
            $table->tinyText('transaction_id')->nullable();
            // bank provider
            $table->tinyText('provider')->nullable();
            // is shaparak
            $table->boolean('is_shaparak')->default(false);
            // url
            $table->tinyText('url')->nullable();
            // expiration date
            // $table->timestamp('expiration_date')->nullable();
            // bank card number hased
            $table->tinyText('card_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
