<?php

namespace App\Console\Commands;

use App\Catalog;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UpdatePassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update-password {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update password for user';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $password = Str::random(8);
        $email = $this->argument('email');

        User::whereEmail($email)
            ->update([
                'password' => Hash::make(Str::random(8))
            ]);

        $this->info('Password updated for user ' . $email . ': ' . $password);
    }
}
