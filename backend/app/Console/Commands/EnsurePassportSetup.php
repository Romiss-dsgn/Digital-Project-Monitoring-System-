<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class EnsurePassportSetup extends Command
{
    protected $signature = 'contrackpro:ensure-passport
        {--client-name=ConTrackPro Personal Access Client : Passport personal access client name}
        {--redirect=http://localhost : Passport personal access client redirect URI}';

    protected $description = 'Ensure Passport keys and a personal access client exist.';

    public function handle(ClientRepository $clients): int
    {
        $privateKey = Passport::keyPath('oauth-private.key');
        $publicKey = Passport::keyPath('oauth-public.key');

        if (! file_exists($privateKey) || ! file_exists($publicKey)) {
            $this->info('Passport keys are missing. Generating keys...');
            $this->call('passport:keys', ['--force' => true]);
        } else {
            $this->line('Passport keys already exist.');
        }

        $personalAccessClientModel = Passport::personalAccessClient();
        $personalAccessClient = $personalAccessClientModel->newQuery()
            ->whereHas('client', function ($query) {
                $query->where('personal_access_client', true)
                    ->where('revoked', false);
            })
            ->latest($personalAccessClientModel->getKeyName())
            ->first();

        if ($personalAccessClient) {
            $this->line('Passport personal access client already exists.');
            $this->info('Client ID: ' . $personalAccessClient->client_id);

            return self::SUCCESS;
        }

        $client = $clients->createPersonalAccessClient(
            null,
            (string) $this->option('client-name'),
            (string) $this->option('redirect')
        );

        $this->info('Passport personal access client created.');
        $this->info('Client ID: ' . $client->getKey());

        return self::SUCCESS;
    }
}
