<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\CRM\Lead\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $data = [
            'notifications' => NotificationResource::collection(
                optional(auth()->user())
                    ->notifications()
                    ->latest()
                    ->take(30)
                    ->get()
            ),
            'unreadCount' => (auth()->user())->unreadNotifications()->count(),
        ];

        return \Prsl::responseData($data);
    }

    public function markNotification(Request $request): JsonResponse
    {
        optional(auth()->user())
            ->unreadNotifications
            ->when($request->input('id'), function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })
            ->markAsRead();

        return \Prsl::responseSuccess();
    }
}
