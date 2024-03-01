<?php

namespace App\Console\Commands\Zoho;

use App\Exceptions\ZohoHTTPClientException;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Services\Zoho\ZohoRestClient;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class SyncMembers extends Command
{
    protected $signature = 'zoho:sync_members';

    protected $description = 'Sync our members to zoho books customers.';
    private ZohoRestClient $client;

    public function handle(): int
    {
        $membersQuery = MemberPrimary::query()
            ->select(['members.*', 'zc.email as zoho_email', 'zc.member_id as zoho_member_id', 'zc.contact_name as zoho_contact_name'])
            ->leftJoin(
                'zoho_customers as zc',
                fn (JoinClause $join) => $join
                    ->on('zc.contact_name', '=', 'members.member_id')
            )
            ->whereNull('zc.contact_id');

        $bar = $this->output->createProgressBar($membersQuery->count());
        $bar->start();

        $this->client = app(ZohoRestClient::class);

        $membersQuery->get()->each(function (Member $member) use ($bar) {
            try {
                $zohoUser = $this->client->createRecord(
                    'contacts',
                    [
                        'JSONString' => json_encode([
                            'contact_name' => $member->member_id,
                            'company_name' => 'N/A',
                            'contact_type' => 'customer',
                            // aed
                            'currency_id' => settings('zoho_currency_id'),
                            'payment_terms' => 0,
                            'payment_terms_label' => 'Due on Receipt',
                            'credit_limit' => 0,
                            'contact_persons' => [
                                [
                                    'first_name' => $member->first_name,
                                    'last_name' => $member->last_name,
                                    'phone' => $member->phone,
                                    'email' => trim($member->email),
                                    'is_primary_contact' => true,
                                ],
                            ],
                            'language_code' => 'en',
                            'customer_sub_type' => 'individual',
                            'opening_balances' => [
                                [
                                    'opening_balance_amount' => '',
                                    'exchange_rate' => 1,
                                ],
                            ],
                        ], JSON_UNESCAPED_SLASHES),
                    ]
                );

                $zohoUser = $zohoUser['contact'];
                DB::table('zoho_customers')->insert([
                    'member_id' => $member->id,
                    'contact_id' => $zohoUser['contact_id'],
                    'contact_name' => $zohoUser['contact_name'],
                    'email' => $zohoUser['email'],
                ]);
            } catch (ZohoHTTPClientException $exception) {
                report($exception);
            }
            $bar->advance();
        });

        $bar->finish();

        return Command::SUCCESS;
    }
}
