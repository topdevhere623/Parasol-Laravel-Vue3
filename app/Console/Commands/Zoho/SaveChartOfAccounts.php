<?php

namespace App\Console\Commands\Zoho;

use App\Models\Zoho\ZohoChartOfAccount;
use App\Services\Zoho\ZohoService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SaveChartOfAccounts extends Command
{
    protected $signature = 'zoho:save_chartofaccounts';

    protected $description = 'Save accounts from zoho books.';

    public function handle()
    {
        /** @var ZohoService $client */
        $service = app(ZohoService::class);

        $response = $service->getChartOfAccounts();

        foreach ($response as $item) {
            $item['id'] = $item['account_id'];
            $item = Arr::only($item, [
                'id',
                'account_name',
                'account_code',
                'account_type',
                'is_user_created',
                'is_system_account',
                'is_standalone_account',
                'is_active',
                'can_show_in_ze',
                'is_involved_in_transaction',
                'current_balance',
                'parent_account_id',
                'parent_account_name',
                'depth',
                'has_attachment',
                'is_child_present',
                'child_count',
                'created_time',
                'last_modified_time',
            ]);

            ZohoChartOfAccount::query()->upsert($item, ['id']);
        }

        return Command::SUCCESS;
    }
}
