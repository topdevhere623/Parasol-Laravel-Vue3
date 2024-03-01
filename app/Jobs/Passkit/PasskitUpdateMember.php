<?php

namespace App\Jobs\Passkit;

use App\Models\Club\Club;
use App\Models\Member\Kid;
use App\Models\Member\Member;
use App\Services\PasskitService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\Response;

class PasskitUpdateMember implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private int $memberId;

    private bool $updateImage;

    private ?Member $member = null;

    private ?PasskitService $passkitService = null;

    public $tries = 5;

    public $timeout = 120;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 10;

    public function __construct($member, $updateImage = false)
    {
        $this->memberId = is_object($member) ? $member->id : $member;
        $this->updateImage = $updateImage;
        $this->onQueue('low');
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return self::class.$this->memberId;
    }

    public function handle(PasskitService $passkitService)
    {
        if (!$passkitService->isAvailable()) {
            return;
        }

        /** @var Member $member */
        $member = Member::with('program')
            ->findOrFail($this->memberId);

        if (!$member->hasPasskitAccess()) {
            return;
        }

        $member->load('passKit', 'membershipType', 'clubs', 'kids');

        $this->member = $member;
        $this->passkitService = $passkitService;

        try {
            if ($member->passKit && $member->passKit->passkit_id) {
                $data = $this->prepareData($this->updateImage);
                $this->update($data);
            } else {
                $data = $this->prepareData(true);
                $this->create($data);
            }
        } catch (RequestException $exception) {
            $data = $this->prepareData(true);

            match ($exception->getCode()) {
                Response::HTTP_REQUEST_TIMEOUT => $this->requestTimeout(),
                Response::HTTP_NOT_FOUND => $this->create($data),
                Response::HTTP_CONFLICT => $this->update($data),
                default => null,
            };

            report_if(
                !in_array($exception->getCode(), [Response::HTTP_NOT_FOUND, Response::HTTP_CONFLICT]),
                new \Exception(
                    'Passkit request failed! Member id: '.$member->id.PHP_EOL.'Data: '.json_encode($data),
                    $exception->getCode(),
                    $exception
                )
            );
        } catch (ConnectionException) {
            $this->requestTimeout();
        } catch (\Exception $exception) {
            report(
                new \Exception(
                    'Passkit request failed! Member id: '.$member->id.PHP_EOL.'Data: '.json_encode($data),
                    $exception->getCode(),
                    $exception
                )
            );

            $this->fail($exception);
        }
    }

    private function create(array $data): void
    {
        if ($this->member->passKit?->passkit_id) {
            $this->passkitService->deleteMember($this->member->passKit->passkit_id);
        }

        $passKitId = $this->passkitService->createMember($data);
        $this->member->passKit()
            ->firstOrNew()
            ->fill([
                'passkit_id' => $passKitId,
            ])
            ->clearState()
            ->save();
    }

    private function update(array $data): void
    {
        $passKitId = $this->passkitService->updateMember($data);
        $passKit = $this->member->passKit()
            ->firstOrNew()
            ->fill([
                'passkit_id' => $passKitId,
            ]);
        if ($this->member->passKit?->passkit_id != $passKitId) {
            $passKit->clearState();
        }

        $passKit->save();
    }

    private function prepareData(bool $updateImage): array
    {
        $member = $this->member;

        $data = [
            'member_id' => $member->member_id,
            'expiry_date' => $member->end_date,
            'start_date' => $member->start_date,
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'email' => app()->isProduction() ? config('services.passkit.member_email_replace') : '',
            'passkit_program_id' => $member->program->passkit_id,
            'membershipType' => $member->membershipType?->card_title,
            'clubs' => $this->prepareClubListData($member->clubs),
            'liveClubs' => $this->prepareLiveClubListData($member->clubs),
            'kids' => $this->prepareChildrenData($member->kids),
        ];

        if ($updateImage && $member->avatar) {
            $data['avatar'] = file_url($member, 'avatar', 'large');
        }

        if ($member->coupon) {
            $data['referralCode'] = $member->coupon->code;
        }

        return $data;
    }

    private function prepareClubListData($clubs): string
    {
        $memberClubList = '';
        $i = 1;
        if ($clubs) {
            $clubs->each(function (Club $club) use (&$memberClubList, &$i) {
                $memberClubList .= "{$i}. {$club->title}\n\n";
                $i++;
            });
        }

        return trim($memberClubList, "\n");
    }

    private function prepareLiveClubListData($clubs): string
    {
        $memberClubList = '';
        $i = 1;

        if ($clubs) {
            $clubs->each(function (Club $club) use (&$memberClubList, &$i) {
                $availability = $club->traffic_is_available ? 'available' : 'unavailable';
                $memberClubList .= "{$i}. {$club->title} is {$availability}\n\n";
                $i++;
            });
        }

        return trim($memberClubList, "\n");
    }

    private function prepareChildrenData($children): string
    {
        $memberChildrenList = '';

        if ($children) {
            $children->each(function (Kid $children) use (&$memberChildrenList) {
                $memberChildrenList .= "{$children->full_name} | {$children->age} years old\n";
            });
        }

        return trim($memberChildrenList, "\n");
    }

    private function requestTimeout(): void
    {
        PasskitUpdateMember::dispatch($this->memberId, $this->updateImage)
            ->delay(now()->addMinutes(3));
    }
}
