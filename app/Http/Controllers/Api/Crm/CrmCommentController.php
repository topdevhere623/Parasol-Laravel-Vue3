<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\CrmCommentRequest;
use App\Http\Resources\CRM\Lead\LeadCommentResource;
use App\Jobs\Nocrm\UpdateCommentLeadNocrmJob;
use App\Models\Lead\CrmAttachment;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use App\Services\UploadFile\Facades\UploadFile;
use Illuminate\Http\JsonResponse;
use Prsl;
use Symfony\Component\HttpFoundation\Response;

class CrmCommentController extends Controller
{
    public function store(Lead $lead, CrmCommentRequest $request): JsonResponse
    {
        /** @var CrmComment $comment */
        $comment = $lead->crmComments()->create($request->validated());
        $comment->crmAttachments()->saveMany($this->uploadFiles('attachments'));

        // check if planned activity is done
        if ($lead->reminder_activity_id == $request->input('crm_activity_id') && $lead->reminder_activity_log_id == null) {
            $lead->reminderActivityLog()->associate($comment);
            $lead->remind_time = null;
            $lead->reminder_duration = null;
            $lead->save();
        }

        $comment->load('backofficeUser', 'crmActivity', 'crmAttachments', 'actionItem.crmAttachments');

        UpdateCommentLeadNocrmJob::dispatch($comment);

        return Prsl::responseData(
            ['crmComment' => LeadCommentResource::make($comment)],
            'Comment has been successfully created.'
        );
    }

    public function update(Lead $lead, CrmComment $comment, CrmCommentRequest $request): JsonResponse
    {
        abort_if(
            $lead->id != $comment->commentable_id || $lead->getMorphClass() != $comment->commentable_type,
            Response::HTTP_NOT_FOUND
        );

        $comment->update($request->validated());
        $comment->crmAttachments()->saveMany($this->uploadFiles('attachments'));

        $comment->load('backofficeUser', 'crmActivity', 'crmAttachments', 'actionItem.crmAttachments');

        UpdateCommentLeadNocrmJob::dispatch($comment);

        return Prsl::responseData(
            ['crmComment' => LeadCommentResource::make($comment)],
            'Comment has been successfully updated.'
        );
    }

    public function destroy(Lead $lead, CrmComment $comment): JsonResponse
    {
        abort_if(
            $lead->id != $comment->commentable_id || $lead->getMorphClass() != $comment->commentable_type,
            Response::HTTP_NOT_FOUND
        );

        $comment->crmAttachments()->delete();
        $comment->delete();
        return Prsl::responseSuccess('Comment has been successfully deleted');
    }

    public function togglePin(Lead $lead, CrmComment $comment): JsonResponse
    {
        abort_if(
            $lead->id != $comment->commentable_id || $lead->getMorphClass() != $comment->commentable_type,
            Response::HTTP_NOT_FOUND
        );

        $comment->is_pinned = !$comment->is_pinned;
        $comment->save();

        $comment->load('backofficeUser', 'crmActivity', 'crmAttachments', 'actionItem.crmAttachments');
        return Prsl::responseData(
            ['crmComment' => LeadCommentResource::make($comment)],
            'Comment pin has been successfully toggled.'
        );
    }

    private function uploadFiles(string $inputName): array
    {
        $files = [];
        if (request()->hasFile($inputName)) {
            foreach (request()->file($inputName) as $file) {
                $attachment = new CrmAttachment();
                $attachment->name = UploadFile::upload(
                    $file,
                    CrmAttachment::getFilePath('file'),
                );
                $attachment->content_type = $file->getMimeType();

                $files[] = $attachment;
            }
        }

        return $files;
    }
}
