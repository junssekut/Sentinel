<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Load users from JSON
        $jsonPath = database_path('seeders/user.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->error("Backup file not found at: $jsonPath minta ke juna :p");
            return;
        }

        $usersData = json_decode(File::get($jsonPath), true);
        
        if (empty($usersData)) {
             $this->command->warn("No users found in backup file.");
             return;
        }

        // 2. Clear existing users OR delete users not in the backup list
        // Strategy: We want to ensure ONLY these users exist. So we can truncate or delete whereNotIn.
        // Truncate might be safer to ensure IDs are reset or just clean slate if we re-insert with IDs.
        // However, if there are foreign keys (like tasks), truncate might fail or cascade.
        // Let's try to delete users not in our list first to be safe, then update/upsert the ones we have.
        
        $allowedEmails = collect($usersData)->pluck('email')->toArray();
        
        // Delete users not in our allowed list
        User::whereNotIn('email', $allowedEmails)->delete();

        // 3. Upsert users from backup
        foreach ($usersData as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']], // Search by email
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'], // Already hashed in backup
                    'role' => $userData['role'],
                    'face_image' => $userData['face_image'],
                    'face_embedding' => $userData['face_embedding'],
                    'email_verified_at' => $userData['email_verified_at'],
                    'remember_token' => $userData['remember_token'],
                ]
            );
        }

        $this->command->info('Users seeded successfully from backup.');
        foreach ($allowedEmails as $email) {
             $this->command->info(" - $email");
        }
    }
}
