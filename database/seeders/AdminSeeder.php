<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = env('ADMIN_NAME', config('site.admin_user.name', 'Admin Sumber Protein'));
        $email = env('ADMIN_EMAIL', config('site.admin_user.email', 'admin@sumberproteinjogja.com'));
        $password = env('ADMIN_PASSWORD');

        if (empty($password)) {
            if ($this->command) {
                $this->command->warn('ADMIN_PASSWORD environment variable is not set. User creation deferred.');
            }
            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );
    }
}
