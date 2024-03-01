<?php

namespace App\Http\Requests\CRM\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRefundRequest extends FormRequest
{
    public function rules()
    {
        return [
            'amount' => 'required|gt:0|numeric',
            'payment_method_id' => 'required|gt:0|int',
        ];
    }
}
