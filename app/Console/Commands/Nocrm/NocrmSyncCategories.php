<?php

namespace App\Console\Commands\Nocrm;

use App\Models\Lead\LeadCategory;
use App\Models\Lead\LeadTag;
use App\Services\NocrmService;
use Illuminate\Console\Command;

class NocrmSyncCategories extends Command
{
    protected $signature = 'nocrm:sync-categories';

    protected $description = '';

    public function handle(NocrmService $nocrmService)
    {
        LeadCategory::firstOrCreate([
            'name' => 'Other',
        ]);

        foreach ($nocrmService->get('categories', ['include_tags' => true]) as $category) {
            $leadCategory = LeadCategory::firstOrCreate([
                'nocrm_id' => $category['id'],
            ]);

            $leadCategory->updateQuietly(['name' => trim($category['name'])]);

            foreach ($category['supertags'] ?? [] as $tag) {
                LeadTag::firstOrCreate([
                    'nocrm_id' => $tag['id'],
                ])->updateQuietly(['name' => trim($tag['name']), 'lead_category_id' => $leadCategory->id]);
            }
        }

        return self::SUCCESS;
    }
}
