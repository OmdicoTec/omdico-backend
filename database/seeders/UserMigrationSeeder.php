<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch data from the old database
        $oldUsers = DB::connection('sqlite')->table('users')->get();

        foreach ($oldUsers as $oldUser) {
            DB::connection('mysql')->table('users')->insert(
                [
                    'name' => $oldUser->name,
                    'family' => $oldUser->family,
                    'type' => $oldUser->type,
                    'mobile_number' => $oldUser->mobile_number,
                    'mobile_verified_at' => $oldUser->mobile_verified_at,
                    'mobile_verify_code_sent_at' => $oldUser->mobile_verify_code_sent_at,
                    'is_active' => $oldUser->is_active,
                    'password' => Hash::make('123456'), // Set a secure password here
                    'created_at' => $oldUser->created_at,
                    'updated_at' => $oldUser->updated_at,
                ]
            );
            // var_dump($oldUser);
            // Output a message for each seeded record
            $this->command->info('User seeded: ' . $oldUser->id);
        }
    }
}
