<?php

namespace App\Http\Controllers\Web;

use App\Jobs\Lead\CreateFromWebFormRequestLeadJob;
use App\Mail\CustomerClubGuide\CustomerClubGuideMail;
use App\Mail\CustomerClubGuide\EntertainerCustomerClubGuideMail;
use App\Models\WebFormRequest;
use App\Notifications\WebFormRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Validator;

class WebFormRequestController extends Controller
{
    public function clubInformation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()], 400);
        }
        $inputs = $request->all();

        $message = is_entertainer_subdomain() ? '<b>Entertainer</b>'.PHP_EOL : '';
        $message .= '<b>FORM "Looking for more detailed Club information?"</b>'.PHP_EOL.'Name: '.$inputs['name'].PHP_EOL.'Email: '.$inputs['email'].PHP_EOL.'Phone: '.$inputs['phone'];

        $webFormRequest = new WebFormRequest();
        $webFormRequest->type = 'Club Information';
        $webFormRequest->name = $request->input('name');
        $webFormRequest->email = $request->input('email');
        $webFormRequest->phone = $request->input('phone');
        $webFormRequest->is_entertainer = is_entertainer_subdomain();

        if (!$webFormRequest->save()) {
            return response('error', 400);
        }

        CreateFromWebFormRequestLeadJob::dispatch($webFormRequest);
        Notification::send(get_telegram_notifiable(), new WebFormRequestNotification($message));
        Mail::to($request->input('email'))
            ->send(
                $webFormRequest->is_entertainer ? new EntertainerCustomerClubGuideMail() : new CustomerClubGuideMail()
            );
        return response()->json(['status' => 'success']);
    }

    public function corporatePricing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'memberships' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()], 400);
        }
        $inputs = $request->all();

        $membership = !empty($inputs['memberships']) ? $inputs['memberships'] : 0;

        $message = '<b>FORM "Corporate Pricing"</b>'.PHP_EOL.'Name: '.$inputs['name'].PHP_EOL.'Email: '.$inputs['email'].PHP_EOL.'Phone: '.$inputs['phone'].PHP_EOL.'Memberships: '.$membership;

        $webFormRequest = new WebFormRequest();
        $webFormRequest->type = 'Corporate Pricing';
        $webFormRequest->name = $request->input('name');
        $webFormRequest->email = $request->input('email');
        $webFormRequest->phone = $request->input('phone');
        $webFormRequest->data = ['memberships' => $request->input('memberships')];

        if ($webFormRequest->save()) {
            CreateFromWebFormRequestLeadJob::dispatch($webFormRequest);
            Notification::send(get_telegram_notifiable(), new WebFormRequestNotification($message));
            return response()->json(['status' => 'success']);
        }
        return response('error', 400);
    }

    public function suggestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'clubs' => 'nullable|string',
            'why' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()], 400);
        }
        $inputs = $request->all();

        $why = !empty($inputs['why']) ? $inputs['why'] : '-';
        $clubs = !empty($inputs['clubs']) ? $inputs['clubs'] : '-';

        $message = '<b>FORM "Submit your suggestion here"</b>'.PHP_EOL.'Name: '.$inputs['name'].PHP_EOL.'Email: '.$inputs['email'].PHP_EOL.'Clubs: '.$clubs.PHP_EOL.'Why: '.$why;

        $webFormRequest = new WebFormRequest();
        $webFormRequest->type = 'Suggestion';
        $webFormRequest->name = $request->input('name');
        $webFormRequest->email = $request->input('email');
        $webFormRequest->data = [
            'clubs' => $request->input('clubs'),
            'why' => $request->input('why'),
        ];

        if ($webFormRequest->save()) {
            Notification::send(get_telegram_notifiable(), new WebFormRequestNotification($message));
            return response()->json(['status' => 'success']);
        }
        return response('error', 400);
    }

    public function signIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()], 400);
        }

        $queryParams = $request->query->all();

        $webFormRequest = new WebFormRequest();
        $webFormRequest->type = 'Advplus landing page';
        $webFormRequest->name = $request->input('name');
        $webFormRequest->email = $request->input('email');
        $webFormRequest->phone = $request->input('phone');
        if (!empty($queryParams)) {
            $webFormRequest->data = $queryParams;
        }
        $webFormRequest->save();

        CreateFromWebFormRequestLeadJob::dispatch($webFormRequest);

        return response()->json(['status' => 'success']);
    }
}
