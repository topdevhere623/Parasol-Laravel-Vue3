<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembershipSourceResource;
use App\Models\Member\MembershipSource;
use Illuminate\Http\Request;

class MembershipSourceSortingController extends Controller
{
    public function index()
    {
        abort_unless(\Prsl::checkGatePolicy('view', MembershipSource::class), 403, 'Not Allowed');

        return MembershipSourceResource::collection(MembershipSource::sort()->get());
    }

    public function update(Request $request)
    {
        abort_unless(\Prsl::checkGatePolicy('update', MembershipSource::class), 403, 'Not Allowed');

        if ($request->has('membershipSources')) {
            $i = 1;
            foreach ($request->input('membershipSources') as $membershipSource) {
                MembershipSource::where('id', $membershipSource['id'])
                    ->update(['sort' => $i++]);
            }
            \Prsl::responseSuccess('Membership source sorting has been successfully updated');
        }
        abort(401, 'Membership source field is required');
    }
}
