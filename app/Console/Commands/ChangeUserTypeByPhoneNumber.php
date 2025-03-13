<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ChangeUserTypeByPhoneNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:change-type {phone : The user phone number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the user type based on the phone number';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phoneNumber = $this->argument('phone');

        // Fetch the user by phone number
        $user = User::where('mobile_number', $phoneNumber)->first();

        if ($user) {
            $currentType = $user->type;

            $this->info("Current user type for phone number $phoneNumber: $currentType");

            // Prompt for the new user type
            $newType = $this->ask('Enter the new user type');

            // Update the user type
            $user->update(['type' => $newType]);

            $this->info("User type updated for phone number $phoneNumber: $newType");
        } else {
            $this->error("User not found for phone number: $phoneNumber");
        }
    }
}
