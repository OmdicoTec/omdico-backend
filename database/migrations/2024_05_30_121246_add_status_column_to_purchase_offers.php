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
        Schema::table('purchase_offers', function (Blueprint $table) {
            $table->string('status')->default('pending');
            $table->json('items')->nullable();
            $table->boolean('have_tax')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_offers', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('items');
            $table->dropColumn('have_tax');
        });

    }
};
