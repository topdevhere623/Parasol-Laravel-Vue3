<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\CRM\Lead\LeadActivityResource;
use App\Models\Lead\CrmActivity;

class CrmActivityController extends Controller
{
    public function index()
    {
        return LeadActivityResource::collection(
            CrmActivity::sort()
                ->doesnthave('parent')
                ->with('children')
                ->get()
        );
    }
}
