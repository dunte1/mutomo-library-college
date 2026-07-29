<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    protected $signature = 'token:generate {email?}';
    protected $description = 'Generate a Sanctum API token for diagnostics';

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask('User email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email '{$email}' not found.");
            return Command::FAILURE;
        }

        $token = $user->createToken('api-diagnostics')->plainTextToken;
        $this->line("Token: {$token}");

        return Command::SUCCESS;
    }
}
