<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
class ProvisionAdminCommand extends Command
{
    protected $signature = 'iskolarlink:provision-admin
                            {--force : Run in production without confirmation}';

    protected $description = 'Create or update the production admin user from environment variables';

    public function handle(): int
    {
        $email = (string) env('ISKOLARLINK_ADMIN_EMAIL', '');
        $name = (string) env('ISKOLARLINK_ADMIN_NAME', 'Administrator');
        $id = (string) env('ISKOLARLINK_ADMIN_ID', 'admin-1');
        $password = env('ISKOLARLINK_ADMIN_PASSWORD');
        $passwordHash = env('ISKOLARLINK_ADMIN_PASSWORD_HASH');

        if ($email === '') {
            $this->error('Set ISKOLARLINK_ADMIN_EMAIL in your environment (Laravel Cloud custom variables).');

            return self::FAILURE;
        }

        if (! $password && ! $passwordHash) {
            $this->error('Set ISKOLARLINK_ADMIN_PASSWORD or ISKOLARLINK_ADMIN_PASSWORD_HASH.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Provision admin account {$email}?", true)) {
            return self::SUCCESS;
        }

        $user = User::query()->firstOrNew(['email' => $email]);

        $user->fill([
            'id' => $user->exists ? $user->id : $id,
            'name' => $name,
            'role' => 'admin',
            'avatar' => $user->avatar,
            'profile_json' => $user->profile_json,
            'created_at' => $user->created_at ?? now(),
        ]);

        if ($passwordHash) {
            $user->password = (string) $passwordHash;
        } else {
            $user->password = (string) $password;
        }

        $user->save();

        $this->info("Admin ready: {$email} (id: {$user->id})");
        $this->line('Log in on production with the same email and password you configured in env.');

        if ($passwordHash) {
            $this->comment('Used ISKOLARLINK_ADMIN_PASSWORD_HASH (same password as local MySQL).');
        }

        return self::SUCCESS;
    }
}
