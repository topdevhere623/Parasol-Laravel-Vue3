<?php

namespace App\Console\Commands\Zoho;

use Illuminate\Console\Command;

class Authentication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:authentication';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OAuth url to complete the Authentication process.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $clientId = config('zoho.client_id');
        $clientDomain = config('zoho.redirect_uri');
        $scope = implode(',', config('zoho.scopes'));
        $prompt = 'consent';
        $response_type = 'code';

        $redirect_url = "https://accounts.zoho.com/oauth/v2/auth?scope={$scope}&prompt={$prompt}&client_id={$clientId}&response_type={$response_type}&access_type=offline&redirect_uri={$clientDomain}";

        $this->info('Copy the following url, past on browser and hit return.');
        $this->line($redirect_url);
        return Command::SUCCESS;
    }
}
