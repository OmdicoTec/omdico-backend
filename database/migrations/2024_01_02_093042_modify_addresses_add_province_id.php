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
        Schema::table('addresses', function (Blueprint $table) {
            // Add your modifications here
            $table->foreignId('province_id')->nullable()->constrained('provinces', 'id');
            $table->string('postal_code', 32)->nullable()->change(); // Change to nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropColumn('province_id');
            $table->string('postal_code', 32)->change();
        });
    }
};
