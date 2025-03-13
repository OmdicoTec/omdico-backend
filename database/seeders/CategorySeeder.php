<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\category;


class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categories = [
            [
                'is_actived' => false,
                'is_root' => false, // true
                'parent_id' => null,
                'title' => 'بی‌دسته',
                'other_title' => '',
                'details' => '',
            ],
        ];

        /**
         * Run the database seeds.
         */

        foreach ($categories as $category) {
            category::create($category);
        }
    }
}
