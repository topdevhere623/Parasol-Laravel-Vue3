<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead\CrmAttachment;
use Illuminate\Http\JsonResponse;
use Prsl;

class CrmAttachmentController extends Controller
{
    public function destroy(CrmAttachment $attachment): JsonResponse
    {
        $attachment->delete();
        return Prsl::responseSuccess('Attachment has been successfully deleted');
    }
}
