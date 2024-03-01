<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Client as OClient;
use Laravel\Passport\ClientRepository;

class PassportRegenerateClientsSecrets extends Command
{
    protected $signature = 'passport:regenerate-clients-secrets
            {--r|revoke-current : Revoke current clients and create new}';

    protected $description = 'Create or regenerate current secrets';

    protected array $providers = [
        'backoffice_users' => 'Backoffice Users Password Grant Client',
        'members' => 'Members Users Password Grant Client',
        'programs' => 'Programs Password Grant Client',
    ];

    public function handle()
    {
        $clientRepository = new ClientRepository();

        if ($this->option('revoke-current')) {
            $this->info('Revoking and creating clients...');
        } else {
            $this->info('Regenerate clients secrets...');
        }

        $tableData = [];
        foreach ($this->providers as $provider => $name) {
            $clientPreQuery = OClient::where('provider', $provider)
                ->where('revoked', 0)
                ->where('password_client', 1)
                ->latest();

            if ($this->option('revoke-current')) {
                $clientPreQuery->get()->each(function ($client) use ($clientRepository) {
                    $clientRepository->delete($client);
                });

                $client = $clientRepository->createPasswordGrantClient(
                    null,
                    $name,
                    'http://localhost',
                    $provider
                );
            } else {
                $client = $clientPreQuery->first();
                $clientRepository->regenerateSecret($client);
            }

            $tableData[] = [$client->id, $provider, $name];
        }

        if ($this->option('revoke-current')) {
            $this->info('Clients secrets has been revoked and created...');
        } else {
            $this->info('Clients secrets has been regenerated...');
        }

        $this->table(['ID', 'Provider', 'Name'], $tableData);

        // Clear cache for passport tokens
        Cache::supportsTags() ? Cache::tags('passport')->clear() : Cache::clear();

        return Command::SUCCESS;
    }
}
