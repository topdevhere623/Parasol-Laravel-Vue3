<?php

namespace App\Http\Requests\Web\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingStepThreeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $booking = $this->route('booking');
        $booking->load('plan', 'membershipRenewal');
        $main = $this->get('member')['main_email'];

        $rules = [
            'member' => 'required|array',
            'member.first_name' => 'required|string',
            'member.last_name' => 'required|string',
            'member.phone' => 'required|string',
            'member.photo' => 'mimes:jpg,png'.($booking->membershipRenewal ? '' : '|required'),
            'member.email' => $main == 'personal_email' ? 'required|email|unique:members,login_email,'.($booking->member ? $booking->member->login_email : '').',login_email,deleted_at,NULL' : 'required|email',
            'member.recovery_email' => $main == 'recovery_email' ? 'nullable|email|unique:members,login_email,'.($booking->member ? $booking->member->login_email : '').',login_email,deleted_at,NULL' : 'nullable|email',
            'member.main_email' => ['required', Rule::in(['recovery_email', 'personal_email'])],
            'member.start_date' => 'date|nullable',
            'member.birthday' => 'required|date',
            'billing.first_name' => 'sometimes|string',
            'billing.last_name' => 'sometimes|string',
            'billing.company_name' => 'sometimes|string',
            'billing.country' => 'sometimes|string',
            'billing.city' => 'sometimes|string',
            'billing.state' => 'nullable|string',
            'billing.street' => 'sometimes|string',
            'billing.is_needed' => 'sometimes|boolean',
            'billing.is_gift' => 'sometimes|boolean',
        ];
        if ($booking->plan->is_partner_available) {
            $rules = array_merge($rules, [
                'partner' => 'required|array',
                'partner.photo' => 'mimes:jpg,png'.($booking->membershipRenewal ? '' : '|required'),
                'partner.first_name' => 'required|string',
                'partner.last_name' => 'required|string',
                'partner.phone' => 'required|string',
                'partner.birthday' => 'required|date',
                'partner.uuid' => 'nullable|string',
            ]);
            if ($booking->member && $booking->member->partner) {
                $rules = array_merge($rules, [
                    'partner.email' => 'required|email|unique:members,login_email,'.$booking->member->partner->login_email.',login_email,deleted_at,NULL',
                ]);
            } else {
                // TODO: refactor this unique validation
                $rules = array_merge(
                    $rules,
                    ['partner.email' => 'required|email|unique:members,login_email,login_email,,deleted_at,NULL']
                );
            }
        }

        if ($booking->number_of_children >= 1) {
            $rules = array_merge($rules, [
                'kids' => 'required|array|size:'.$booking->number_of_children,
                'kids.*.first_name' => 'required|string',
                'kids.*.last_name' => 'required|string',
                'kids.*.birthday' => 'required|date',
                'kids.*.uuid' => 'nullable|string',
            ]);
        }
        if ($booking->number_of_juniors >= 1) {
            $juniors = $booking->member ? $booking->member->juniors : collect([]);
            $rules = array_merge($rules, [
                'junior' => 'required|array|size:'.$booking->number_of_juniors,
                'junior.*.first_name' => 'required|string',
                'junior.*.last_name' => 'required|string',
                'junior.*.phone' => 'required|string',
                'junior.*.birthday' => 'required|date',
                'junior.*.uuid' => 'nullable|string',
                'junior.*.photo' => 'mimes:jpg,png'.($booking->membershipRenewal ? '' : '|required'),
                'junior.*.email' => [
                    'required',
                    'email',
                    Rule::unique('members', 'login_email')->where(function ($query) use ($juniors) {
                        $query->whereNull('deleted_at');
                        if (!$juniors) {
                            return $query;
                        }
                        return $query->whereNotIn('id', $juniors->pluck('id'))->whereNotIn(
                            'login_email',
                            $juniors->pluck('email')
                        );
                    }),
                ],
            ]);
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'member.email.unique' => 'This Primary email ID already exists. Please enter a unique ID or contact the customer service team.',
            'member.recovery_email.unique' => 'This Primary email ID already exists. Please enter a unique ID or contact the customer service team.',
            'partner.email.unique' => 'This Partner email ID already exists. Please enter a unique ID or contact the customer service team.',
        ];
        if ($this->get('junior')) {
            foreach ($this->get('junior') as $key => $val) {
                $messages["junior.{$key}.email"] = $val['email'].' email ID already exists. Please enter a unique ID or contact the customer service team.';
                ;
            }
        }
        return $messages;
    }
}
