<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member\MemberPasskit;
use Illuminate\Http\Request;

class PassKitWebhookHandler extends Controller
{
    public function __invoke(Request $request, $requestApiKey = '')
    {
        abort_if(config('services.passkit.webhook_token') !== $requestApiKey, 401, 'Invalid token');

        $data = $request->toArray();

        $pass = MemberPasskit::where('passkit_id', $data['pass']['id'])->firstOrFail();

        if ($data['event'] == 'PASS_EVENT_RECORD_DELETED') {
            $pass->delete();
            response()->json('ok');
        } else {
            $event = $data['event'] == 'PASS_EVENT_INSTALLED' ? 'installed' : 'uninstalled';
            if (isset($data['pass']['recordData']['universal.installDeviceAttributes'])) {
                $platform = $data['pass']['recordData']['universal.installDeviceAttributes'] == 'Android' ? 'google' : 'apple';
                $pass->setAttribute("has_{$platform}_{$event}", true);
            }
            $pass->status = $data['pass']['recordData']['universal.status'];
            $pass->save();

            response()->json('ok');
        }
    }
}
