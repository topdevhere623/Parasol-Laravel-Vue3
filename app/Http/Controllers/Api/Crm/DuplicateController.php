<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\DuplicateRequest;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\Partner\PartnerTranche;
use App\Models\Plan;
use App\Services\UploadFile\Facades\UploadFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DuplicateController extends Controller
{
    public function store(DuplicateRequest $request, string $name): JsonResponse
    {
        $method = lcfirst(str_replace('-', '', ucwords($name, '-')));

        if (method_exists($this, $method)) {
            return $this->{$method}($request->input('id'));
        }

        return \Prsl::responseError('Alias not found', Response::HTTP_BAD_REQUEST);
    }

    private function plan($id): JsonResponse
    {
        abort_unless(\Prsl::checkGatePolicy('create', Plan::class), Response::HTTP_FORBIDDEN, 'Not Allowed');

        $plan = Plan::with([
            'belongsToClubs',
            'paymentMethods',
        ])->findOrFail($id);

        /** @var $newPlan Plan */
        $newPlan = $plan->replicate([
            'uuid',
            'deleted_at',
        ]);

        $newClubs = $plan->belongsToClubs->pluck('pivot')->toArray();
        $newPayments = $plan->paymentMethods->pluck('id')->toArray();

        DB::beginTransaction();

        if (!$newPlan->save()) {
            DB::rollBack();
            abort(Response::HTTP_BAD_REQUEST, 'Error');
        }
        array_walk($newClubs, function (&$item) use ($newPlan) {
            $item['plan_id'] = $newPlan->id;
        });

        $newPlan->belongsToClubs()->attach($newClubs);
        $newPlan->paymentMethods()->attach($newPayments);

        DB::commit();
        return \Prsl::responseData(['id' => $newPlan->id], 'Plan has been duplicated');
    }

    private function coupon($id): JsonResponse
    {
        abort_unless(\Prsl::checkGatePolicy('create', Coupon::class), Response::HTTP_FORBIDDEN, 'Not Allowed');

        $coupon = Coupon::with('belongsToPlans')->findOrFail($id);

        /** @var $newCoupon Coupon */
        $newCoupon = $coupon->replicate(['deleted_at', 'code']);
        $newCoupon->code = Coupon::generateCode();

        DB::beginTransaction();

        if (!$newCoupon->save()) {
            DB::rollBack();
            abort(Response::HTTP_BAD_REQUEST, 'Error');
        }
        $newCouponId = $newCoupon->id;

        $newPlans = $coupon->belongsToPlans()->get(['type', 'plan_id'])->toArray();
        array_walk($newPlans, function (&$newPlan) use ($newCouponId) {
            $newPlan['coupon_id'] = $newCouponId;
            unset($newPlan['pivot']);
        });
        $newCoupon->belongsToPlans()->attach($newPlans);

        DB::commit();

        return \Prsl::responseData(['id' => $newCoupon->id], 'Coupon has been duplicated');
    }

    private function offer($id): JsonResponse
    {
        abort_unless(\Prsl::checkGatePolicy('create', Offer::class), Response::HTTP_FORBIDDEN, 'Not Allowed');

        $offer = Offer::with([
            'gallery',
            'clubs',
        ])->findOrFail($id);

        /** @var $newOffer Offer */
        $newOffer = $offer->replicate([
            'uuid',
            'logo',
            'deleted_at',
        ]);

        $logoPath = Offer::getFilePath('logo');
        $newOffer->logo = UploadFile::copy($offer->logo, $logoPath, $logoPath, Offer::getFileSize('logo'));

        $newClubs = $offer->clubs->pluck('id')->toArray();

        DB::beginTransaction();

        if (!$newOffer->save()) {
            DB::rollBack();
            abort(Response::HTTP_BAD_REQUEST, 'Error');
        }
        $newOffer->clubs()->attach($newClubs);

        $newGallery = [];

        $path = Offer::getFilePath('gallery');
        $size = Offer::getFileSize('gallery');
        foreach ($offer->gallery as $key => $item) {
            $newImageName = UploadFile::copy($item->name, $path, $path, $size);

            if ($newImageName) {
                $newGallery[$key]['name'] = $newImageName;
                $newGallery[$key]['sort'] = $item->sort;
            }
        }

        if (count($newGallery)) {
            $newOffer->gallery()->createMany($newGallery);
        }

        DB::commit();
        return \Prsl::responseData(['id' => $newOffer->id], 'Offer has been duplicated');
    }

    private function partnerTranche($id): JsonResponse
    {
        abort_unless(\Prsl::checkGatePolicy('create', PartnerTranche::class), Response::HTTP_FORBIDDEN, 'Not Allowed');

        $partner = PartnerTranche::findOrFail($id);

        /** @var $newPartner \App\Models\Partner\PartnerTranche */
        $newPartner = $partner->replicate([
            'deleted_at',
        ]);

        DB::beginTransaction();

        if (!$newPartner->save()) {
            DB::rollBack();
            abort(Response::HTTP_BAD_REQUEST, 'Error');
        }

        DB::commit();
        return \Prsl::responseData(['id' => $newPartner->id], 'Partner tranche has been duplicated');
    }
}
