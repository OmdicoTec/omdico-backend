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
        // Modify the existing purchase_offers table
        Schema::table('purchase_offers', function (Blueprint $table) {
            // Add your modifications here
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests', 'id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the foreign key purchase_request_id
        Schema::table('purchase_offers', function (Blueprint $table) {
            $table->dropForeign(['purchase_request_id']);
            $table->dropColumn('purchase_request_id');
        });

        // Drop the purchase_offers table
        // Schema::dropIfExists('purchase_offers');
    }
};
