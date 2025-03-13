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
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'slug')) {
                $table->tinyText('slug')->nullable();
                // $table->index(['slug'], 'categories_slug_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Check if the index exists before attempting to drop it
            $indexExists = collect(DB::select("SHOW INDEX FROM categories WHERE Key_name = 'categories_slug_index'"))->isNotEmpty();

            if ($indexExists) {
                $table->dropIndex('categories_slug_index');
            }

            $table->dropColumn('slug');
        });
    }
};
