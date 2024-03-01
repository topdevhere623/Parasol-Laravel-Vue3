<?php

namespace App\Console\Commands\Nocrm;

use App\Models\BackofficeUser;
use App\Models\Lead\CrmStep;
use App\Models\Lead\Lead;
use App\Services\NocrmService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NocrmSyncFromJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nocrm:sync-from-json';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(NocrmService $nocrmService)
    {
        $crmSteps = CrmStep::pluck('id', 'nocrm_id')->toArray();
        $backofficeUsers = BackofficeUser::whereNotNull('nocrm_id')
            ->pluck('id', 'nocrm_id')
            ->toArray();

        $leads = json_decode(file_get_contents(storage_path('leads.json')));

        $bar = $this->output->createProgressBar(count($leads));
        foreach ($leads as $lead) {
            $bar->advance();
            $localLead = Lead::where('nocrm_id', $lead->id)->first();

            if (!$localLead) {
                continue;
            }

            $localLead->timestamps = false;
            $localLead->update([
                'title' => $lead->title,
                'crm_step_id' => $crmSteps[$lead->step_id],
                'remind_date' => $lead->remind_date,
                'remind_time' => $lead->remind_time,
                'created_at' => Carbon::parse($lead->created_at, 'UTC')->addHours(4),
                'updated_at' => Carbon::parse($lead->updated_at, 'UTC')->addHours(4),
                'closed_at' => $lead->closed_at ? Carbon::parse($lead->closed_at, 'UTC')->addHours(4) : null,
                'created_by' => $lead->created_by->id ? $backofficeUsers[$lead->created_by->id] : null,
                'status' => $lead->status,
                'backoffice_user_id' => $lead->user->id ? $backofficeUsers[$lead->user->id] : null,
                'amount' => $lead->amount ?? 0,
            ]);
        }

        $bar->finish();
        $this->newLine();
        return Command::SUCCESS;
    }
}
