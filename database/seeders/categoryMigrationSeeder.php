<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class categoryMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch data from the old database
        $categories = DB::connection('sqlite')->table('categories')->get();

        foreach ($categories as $category) {
            // Convert the object to an array and remove the 'id' field
            $categoryArray = (array) $category;
            // Insert the category into the new database
            DB::connection('mysql')->table('categories')->insert([$categoryArray]);
            $this->command->info('Category seeded: ' . $category->id);
        }
    }
}
