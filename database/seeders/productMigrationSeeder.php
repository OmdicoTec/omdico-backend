<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class productMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch data from the old database
        $products = DB::connection('sqlite')->table('products')->get();

        foreach ($products as $product) {
            // Convert the object to an array and remove the 'id' field
            $productArray = (array) $product;
            // Insert the productArray into the new database
            DB::connection('mysql')->table('products')->insert([$productArray]);
            $this->command->info('Product seeded: ' . $product->id);
        }
    }
}
