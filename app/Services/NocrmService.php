<?php

namespace App\Services;

use App\Models\BackofficeUser;
use App\Models\Lead\CrmComment;
use App\Models\Lead\Lead;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class NocrmService
{
    protected PendingRequest $httpClient;

    protected bool $isAvailable;

    public function __construct(null|string $apiKey, null|string $subdomain)
    {
        if (!$this->isAvailable = $apiKey && $subdomain) {
            return;
        }

        $this->httpClient = new PendingRequest();
        $this->httpClient->baseUrl(
            "https://{$subdomain}.nocrm.io/api/v2/"
        )
            ->acceptJson()
            ->asJson()
            ->retry(3, 5000, function ($exception) {
                $code = $exception->getCode();
                return $exception instanceof RequestException && ($code == Response::HTTP_REQUEST_TIMEOUT || $code >= 500);
            })
            ->withHeaders(['X-API-KEY' => $apiKey])
            ->withOptions([
                // 'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function get(string $path, array $queryParams = []): array
    {
        $response = $this->httpClient->get($path, $queryParams);
        throw_unless(
            $response->ok(),
            'Unable to send NOCRM data: '.PHP_EOL.$response->body().PHP_EOL.json_encode($queryParams)
        );

        return $response->json();
    }

    public function post(string $path, array $data = []): array
    {
        $response = $this->httpClient->post($path, $data);
        throw_unless(
            $response->created(),
            'Unable to send NOCRM data: '.PHP_EOL.$response->status().PHP_EOL.$response->body().PHP_EOL.json_encode(
                $data
            )
        );

        return $response->json();
    }

    public function put(string $path, array $data = []): array
    {
        $response = $this->httpClient->put($path, $data);
        throw_unless(
            $response->ok(),
            'Unable to send NOCRM data: '.PHP_EOL.$response->status().PHP_EOL.$response->body().PHP_EOL.json_encode(
                $data
            )
        );

        return $response->json();
    }

    public function deleteLead(Lead $lead): array
    {
        $response = $this->httpClient->delete("leads/{$lead->nocrm_id}");
        throw_unless(
            $response->ok(),
            'Unable to delete NOCRM lead: '.PHP_EOL.$response->status().PHP_EOL.$response->body().PHP_EOL.json_encode(
                $lead->toArray()
            )
        );

        return $response->json();
    }

    public function createLead(Lead $lead): array
    {
        $data = $this->leadData($lead);
        $data['user_id'] = BackofficeUser::find(Lead::DEFAULT_OWNER)?->nocrm_id ?? $data['user_id'];
        return $this->post('leads', $data);
    }

    public function updateLead(int $leadId, array $data): array
    {
        return $this->put("leads/{$leadId}", $data);
    }

    public function updateLead2(Lead $lead): array
    {
        return $this->put("leads/{$lead->nocrm_id}", $this->leadData($lead));
    }

    /**
     * @throws RequestException
     */
    public function pushLead(Lead $lead): array
    {
        $tries = 1;
        do {
            $tries++;
            try {
                if (!$lead->nocrm_id) {
                    $response = $this->createLead($lead);
                    $lead->nocrm_id = $response['id'];
                    $lead->saveQuietly();
                }

                $response = $this->updateLead2($lead);
                $lead->nocrm_id = $response['id'];
                $lead->saveQuietly();

                return $response;
            } catch (RequestException $exception) {
                if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                    $lead->nocrm_id = null;
                    continue;
                }
                throw $exception;
            }
        } while ($tries < 3);

        return [];
    }

    public function leadData(Lead $lead): array
    {
        $data = [
            'status' => $lead->status,
            'step_id' => $lead->crmStep->nocrm_id,
            'tags' => $lead->leadTags->pluck('name'),
            'title' => $lead->title,
            'remind_date' => optional($lead->remind_date)->format('Y-m-d'),
            'remind_time' => optional($lead->remind_time)->format('H:i'),
            'reminder_duration' => $lead->reminder_duration,
            'reminder_activity_id' => optional($lead->reminderActivity)->nocrm_id,
            'reminder_note' => $lead->reminder_note,
            'description' => Arr::humanizeKeyValue([
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email' => $lead->email,
                'mobile' => $lead->phone,
            ]),
        ];

        if ($lead->amount) {
            $data['amount'] = $lead->amount;
        }

        if ($lead->backofficeUser?->nocrm_id) {
            $data['user_id'] = $lead->backofficeUser->nocrm_id;
        }

        return $data;
    }

    public function getLeads(array $params = []): array
    {
        $response = $this->httpClient->get('leads', $params);

        throw_unless(
            $response->ok(),
            'Unable to get NOCRM leads: '.PHP_EOL.$response->status().PHP_EOL.$response->body().PHP_EOL.json_encode(
                $params
            )
        );

        return $response->json();
    }

    public function getLead(string $leadId): array
    {
        $response = $this->httpClient->get("leads/{$leadId}");
        return $response->json();
    }

    public function createComment(CrmComment $comment): array
    {
        return $this->post("leads/{$comment->commentable->nocrm_id}/comments", $this->commentData($comment));
    }

    public function updateComment(CrmComment $comment): array
    {
        return $this->put(
            "leads/{$comment->commentable->nocrm_id}/comments/{$comment->nocrm_id}",
            $this->commentData($comment)
        );
    }

    public function deleteComment(CrmComment $comment): array
    {
        return $this->httpClient->delete("leads/{$comment->commentable->nocrm_id}/comments/{$comment->nocrm_id}")
            ->json();
    }

    /**
     * @throws RequestException
     */
    public function pushComment(CrmComment $comment): array
    {
        $tries = 1;
        do {
            $tries++;
            try {
                if ($comment->nocrm_id) {
                    $response = $this->updateComment($comment);
                } else {
                    $response = $this->createComment($comment);
                }

                $comment->nocrm_id = $response['id'];
                $comment->saveQuietly();

                return $response;
            } catch (RequestException $exception) {
                if ($exception->getCode() === Response::HTTP_NOT_FOUND && $this->pushLead($comment->commentable)) {
                    $comment->nocrm_id = null;
                    continue;
                }
                throw $exception;
            }
        } while ($tries < 3);

        return [];
    }

    public function commentData(CrmComment $comment): array
    {
        $data = [
            'lead_id' => $comment->commentable->nocrm_id,
            'content' => $comment->content,
        ];

        if ($comment->backofficeUser?->nocrm_id) {
            $data['user_id'] = $comment->backofficeUser->nocrm_id;
        }

        if ($comment->crmActivity?->nocrm_id) {
            $data['activity_id'] = $comment->crmActivity->nocrm_id;
        }

        return $data;
    }

}
