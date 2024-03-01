<?php

namespace App\Http\Requests\CRM\SalesQuote;

class UpdateRequest extends StoreRequest
{
    public function rules()
    {
        return array_merge(
            [
                'id' => 'required|integer',
            ],
            parent::rules()
        );
    }
}
