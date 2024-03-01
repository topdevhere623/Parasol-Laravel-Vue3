<?php

namespace App\Console\Commands\Zoho;

use App\Services\Zoho\ZohoRestClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SaveInvoices extends Command
{
    protected $signature = 'zoho:save_invoices';

    protected $description = 'Save invoices from zoho books and match zoho invoice id to our payment.';

    public function handle()
    {
        /** @var ZohoRestClient $client */
        $client = app(ZohoRestClient::class);
        $currentPage = 1;
        do {
            $response = $client->getList('invoices', ['page' => $currentPage, 'per_page' => 50]);
            $currentPage++;

            $dataToInsert = [];
            foreach ($response['invoices'] as $item) {
                $dataToInsert[] = [
                    'id' => $item['invoice_id'],
                    'customer_id' => $item['customer_id'],
                    'invoice_number' => $item['invoice_number'],
                    'status' => $item['status'] ?? null,
                    'date' => $item['date'] ?? null,
                    'created_time' => $item['created_time'] ?? null,
                    'total' => $item['total'] ?? null,
                    'discount' => $item['discount'] ?? null,
                    'tax_total' => $item['tax_total'] ?? null,
                    'invoice_url' => $item['invoice_url'] ?? null,
                    'full_response' => json_encode($item),
                ];
            }

            DB::table('zoho_invoices')->upsert($dataToInsert, ['id']);
        } while ($response['page_context']['has_more_page'] === true);

        return Command::SUCCESS;
    }
}
