<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HSBCBin;
use App\Models\Package;
use Illuminate\Http\Request;

class HSBCController extends Controller
{
    public function hsbcBinCheck(Request $request)
    {
        $HSBCBins = HSBCBin::active()->get();

        if ($request->has('bin') && $bin = str_replace(' ', '', $request->input('bin'))) {
            if ($HSBCBins->where('bin', $bin)->count()) {
                $id = !$request->has('is_supplementary') && $HSBCBins->where('bin', $bin)
                    ->where('free_checkout', true)->count()
                    ? settings('hsbc_free_checkout_package_id')
                    : settings('hsbc_paid_checkout_package_id');

                $url = $this->getHSBCPlanUrl($id);

                return response()->json([
                    'data' => [
                        'url' => $url,
                        'free_checkout' => $id == settings('hsbc_free_checkout_package_id'),
                    ],
                ]);
            }
        }
        abort(404);
    }

    protected function getHSBCPlanUrl($id): string
    {
        $package = Package::active()->find($id);

        if (!$package) {
            report(new \Exception('HSBC Package not found. ID: '.$id));
        }

        return route('booking.step-1', [
            'package' => optional($package)->slug ?? '',
        ]);
    }
}
