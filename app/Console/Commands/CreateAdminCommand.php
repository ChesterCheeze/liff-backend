<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create {--email=} {--name=}';

    protected $description = 'Create a new admin user';

    public function handle()
    {
        $email = $this->option('email') ?? $this->ask('Enter admin email');
        $name = $this->option('name') ?? $this->ask('Enter admin name');
        $password = $this->secret('Enter admin password');
        $confirmPassword = $this->secret('Confirm admin password');

        // Validate input
        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
            'password_confirmation' => $confirmPassword,
        ], [
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'min:3'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return 1;
        }

        // Create admin user
        $user = User::create([
            'email' => $email,
            'name' => $name,
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->info("Admin user {$user->email} created successfully!");

        return 0;
    }
}
