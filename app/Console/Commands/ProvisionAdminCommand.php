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
        $email = $this->adminSetting('admin_email');
        $name = $this->adminSetting('admin_name') ?: 'Administrator';
        $id = $this->adminSetting('admin_id') ?: 'admin-1';
        $password = $this->adminSetting('admin_password');
        $passwordHash = $this->adminSetting('admin_password_hash');

        if ($email === '') {
            $this->error('Missing ISKOLARLINK_ADMIN_EMAIL.');
            $this->line('Add it under Laravel Cloud → Environment → Custom environment variables, then Save.');
            $this->line('If you already added it, redeploy or run: php artisan config:clear');
            $this->line('Values with # must be quoted, e.g. ISKOLARLINK_ADMIN_PASSWORD="#nemsu_2026!"');

            return self::FAILURE;
        }

        if ($password === '' && $passwordHash === '') {
            $this->error('Missing ISKOLARLINK_ADMIN_PASSWORD or ISKOLARLINK_ADMIN_PASSWORD_HASH.');
            $this->line('Quote passwords that contain # : ISKOLARLINK_ADMIN_PASSWORD="#your-password"');

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

        $user->password = $passwordHash !== '' ? $passwordHash : $password;
        $user->save();

        $this->info("Admin ready: {$email} (id: {$user->id})");
        $this->line('Log in on production with that email and your admin password.');

        if ($passwordHash !== '') {
            $this->comment('Used ISKOLARLINK_ADMIN_PASSWORD_HASH.');
        }

        return self::SUCCESS;
    }

    /**
     * Read admin settings after config:cache (env() alone is empty on Laravel Cloud).
     */
    private function adminSetting(string $key): string
    {
        $envKeys = [
            'admin_id' => 'ISKOLARLINK_ADMIN_ID',
            'admin_name' => 'ISKOLARLINK_ADMIN_NAME',
            'admin_email' => 'ISKOLARLINK_ADMIN_EMAIL',
            'admin_password' => 'ISKOLARLINK_ADMIN_PASSWORD',
            'admin_password_hash' => 'ISKOLARLINK_ADMIN_PASSWORD_HASH',
        ];

        $envKey = $envKeys[$key] ?? strtoupper($key);

        $fromEnv = getenv($envKey);
        if (is_string($fromEnv) && $fromEnv !== '') {
            return $fromEnv;
        }

        $fromServer = $_ENV[$envKey] ?? $_SERVER[$envKey] ?? null;
        if (is_string($fromServer) && $fromServer !== '') {
            return $fromServer;
        }

        $value = config("iskolarlink.{$key}");

        return is_string($value) ? $value : '';
    }
}
