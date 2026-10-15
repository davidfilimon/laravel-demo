<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Create an administrator account using a privately entered password';

    public function handle(): int
    {
        $email = $this->ask('Email');
        $data = [
            'name' => $this->ask('Name'),
            'email' => is_string($email) ? mb_strtolower($email) : $email,
            'password' => $this->secret('Password (at least 12 characters)'),
            'password_confirmation' => $this->secret('Confirm password'),
        ];
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $admin = new User($validator->safe()->only(['name', 'email', 'password']));
        $admin->is_admin = true;
        $admin->save();
        $this->info('Administrator created. Sign in to create users and assign tasks.');

        return self::SUCCESS;
    }
}
