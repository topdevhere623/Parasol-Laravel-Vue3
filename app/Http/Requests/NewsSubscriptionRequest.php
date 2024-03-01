<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsSubscriptionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email|unique:news_subscriptions,email',
            'name' => 'required|string',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
