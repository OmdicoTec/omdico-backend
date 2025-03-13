<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * It's add foreignId province_id
     */
    public function up(): void
    {
        // Modify the existing purchase_offers table
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Add your modifications here
            $table->foreignId('province_id')->nullable()->constrained('provinces', 'id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropColumn('province_id');
        });
    }
};
