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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            // purchase request id
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests', 'id');
            // purchase offer id
            $table->foreignId('purchase_offer_id')->nullable()->constrained('purchase_offers', 'id');
            // buyer id
            $table->foreignId('buyer_id')->nullable()->constrained('users', 'id');
            // seller id
            $table->foreignId('seller_id')->nullable()->constrained('users', 'id');
            $table->tinyText('total_amount')->nullable();
            // buyer status => active, closed, canceled
            $table->enum('buyer_status', ['active', 'closed', 'canceled'])->default('active');
            // seller status => active, closed, canceled
            $table->enum('seller_status', ['active', 'closed', 'canceled'])->default('active');
            // is seller accepted the invoice ?
            $table->boolean('is_seller_accepted')->default(true);
            // total commission amount
            $table->tinyText('commission_amount')->nullable();
            // amount_owed_by_buyer
            $table->tinyText('amount_owed_by_buyer')->nullable();
            // total buyer deposit
            $table->tinyText('total_buyer_deposit')->nullable();
            // amount owed to supplier
            $table->tinyText('amount_owed_to_supplier')->nullable();
            // total supplier deposit
            $table->tinyText('total_supplier_deposit')->nullable();
            // admin note for self
            $table->text('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
