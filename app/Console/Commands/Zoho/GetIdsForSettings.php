<?php

namespace App\Console\Commands\Zoho;

use App\Services\Zoho\ZohoService;
use Illuminate\Console\Command;

class GetIdsForSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:settings_ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'IDs for zoho settings page';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var ZohoService $service */
        $service = app(ZohoService::class);

        $currencies = collect($service->getCurrencies())->pluck('currency_id', 'currency_code');
        $this->info('Currencies');
        $currencies->each(fn ($key, $item) => $this->line(sprintf('ID: %s Value: %s', $key, $item)));

        $templates = collect($service->getInvoicesTemplates())->pluck('template_id', 'template_name');
        $this->info('Invoice Templates');
        $templates->each(fn ($key, $item) => $this->line(sprintf('ID: %s Value: %s', $key, $item)));

        $taxes = collect($service->getTaxes())->pluck('tax_id', 'tax_name');
        $this->info('Taxes');
        $taxes->each(fn ($key, $item) => $this->line(sprintf('ID: %s Value: %s', $key, $item)));

        $items = collect($service->getItems())->pluck('item_id', 'item_name');
        $this->info('Items');
        $items->each(fn ($key, $item) => $this->line(sprintf('ID: %s Value: %s', $key, $item)));

        $chartOfAccounts = collect($service->getChartOfAccounts())->pluck('account_id', 'account_name');
        $this->info('Deposit Accounts');
        $chartOfAccounts->each(fn ($key, $item) => $this->line(sprintf('ID: %s Value: %s', $key, $item)));

        return Command::SUCCESS;
    }
}
