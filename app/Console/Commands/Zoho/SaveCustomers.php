<?php

namespace App\Console\Commands\Zoho;

use App\Models\Member\MemberPrimary;
use App\Services\Zoho\ZohoRestClient;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class SaveCustomers extends Command
{
    protected $signature = 'zoho:save_customers';

    protected $description = 'Save customers from zoho books and match zoho customer id to member.';

    public function handle()
    {
        /** @var ZohoRestClient $client */
        $client = app(ZohoRestClient::class);
        $currentPage = 1;
        do {
            $response = $client->getList('contacts', ['page' => $currentPage]);
            $currentPage++;

            $dataToInsert = [];
            foreach ($response['contacts'] as $contact) {
                $dataToInsert[] = [
                    'contact_id' => $contact['contact_id'],
                    'contact_name' => $contact['contact_name'],
                    'email' => $contact['email'],
                ];
            }

            DB::table('zoho_customers')->upsert($dataToInsert, ['contact_id'], ['email', 'contact_name']);
        } while ($response['page_context']['has_more_page'] === true);

        $existInZohoMembers = MemberPrimary::query()
            ->addSelect('*')
            ->addSelect('zc.email as zoho_email')
            ->join(
                'zoho_customers as zc',
                fn (JoinClause $join) => $join
                    ->on('zc.email', '=', 'members.email')
                    ->orWhere('zc.email', '=', 'members.recovery_email')
            )
            ->get();

        $updateData = [];
        foreach ($existInZohoMembers as $member) {
            $updateData[] = [
                'member_id' => $member->id, 'contact_id' => $member->zoho_contact_id, 'email' => $member->zoho_email,
            ];
        }

        // сохранение id мембера
        DB::table('zoho_customers')->upsert($updateData, ['contact_id'], ['email', 'member_id']);

        return Command::SUCCESS;
    }
}
