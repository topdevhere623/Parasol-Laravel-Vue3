<?php

namespace App\Services\Zoho;

use App\Exceptions\ZohoHTTPClientException;
use App\Models\Booking;
use App\Models\Member\Member;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\Payment;
use App\Models\Zoho\ZohoCustomer;
use App\Models\Zoho\ZohoInvoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class ZohoService
{
    public function __construct(private readonly ZohoRestClient $client)
    {
    }

    public function isAvailable(): bool
    {
        return $this->client->isAvailable();
    }

    public function getContactById(string $id): ?array
    {
        return $this->client->getRecord('contacts', $id)['contact'] ?? null;
    }

    public function getContactByEmail(string $email): ?array
    {
        return $this->client->getList('contacts', ['email_contains' => $email])['contacts'][0] ?? null;
    }

    public function getContactByContactsName(string $contactName): ?array
    {
        return $this->client->getList('contacts', ['contact_name_contains' => $contactName])['contacts'] ?? null;
    }

    /**
     * Возвращает первый не пустой адрес контакта
     */
    public function getContactAddress(?string $contactId): ?array
    {
        if ($contactId === null) {
            return null;
        }
        $response = $this->client->getRecord('contacts', $contactId, 'address');

        $addresses = $response['addresses'];

        foreach ($addresses as $address) {
            // В зохо, по дефолту, есть пустой адрес. Если эти поля пустые, считаем что адрес пустой.
            if (empty($address['country']) && empty($address['city']) && empty($address['address'])) {
                continue;
            }

            return $address;
        }

        return null;
    }

    public function createContactByMember(Member $member): array
    {
        $data = [
            'contact_name' => $member->full_name,
            'company_name' => 'N/A',
            'contact_type' => 'customer',
            'currency_id' => settings('zoho_currency_id'),
            'payment_terms' => 0,
            'payment_terms_label' => 'Due on Receipt',
            'credit_limit' => 0,
            'contact_persons' => [
                [
                    'first_name' => $member->first_name,
                    'last_name' => $member->last_name,
                    'phone' => $member->phone,
                    'email' => trim($member->email),
                    'is_primary_contact' => true,
                ],
            ],
            'language_code' => 'en',
            'customer_sub_type' => 'individual',
            'opening_balances' => [
                [
                    'opening_balance_amount' => '',
                    'exchange_rate' => 1,
                ],
            ],
        ];
        return $this->client->createRecord(
            'contacts',
            ['JSONString' => json_encode($data, JSON_UNESCAPED_SLASHES)]
        )['contact'];
    }

    public function createContactAddress(string $contactId, array $data): ?array
    {
        return $this->client->createRecord('contacts', $data, $contactId, 'address')['address_info'] ?? null;
    }

    public function getContactByMember(Member $member): ?array
    {
        $contact = null;
        if (!empty($member->zoho_contact_id)) {
            $contact = $this->getContactById($member->zoho_contact_id);
        }

        if ($contact === null) {
            $contact = $this->getContactByContactsName($member->full_name)[0] ?? null;
        }

        return $contact;
    }

    public function getChartOfAccounts(): array
    {
        return $this->client->getList('chartofaccounts', ['per_page' => 5000])['chartofaccounts'] ?? [];
    }

    public function getCurrencies(): array
    {
        return $this->client->getList('settings/currencies', ['per_page' => 5000])['currencies'] ?? [];
    }

    public function getTaxes(): array
    {
        return $this->client->getList('settings/taxes', ['per_page' => 5000])['taxes'] ?? [];
    }
    public function getInvoicesTemplates(): array
    {
        return $this->client->getList('invoices/templates', ['per_page' => 5000])['templates'] ?? [];
    }
    public function getItems(): array
    {
        return $this->client->getList('items', ['per_page' => 5000])['items'] ?? [];
    }

    public function createPaymentByPayment(Payment $payment): array
    {
        try {
            $contact = $this->getContactByMember($payment->member);
        } catch (ZohoHTTPClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $contact = null;
            } else {
                throw $exception;
            }
        }

        $zohoInvoiceId = $payment->booking?->zohoInvoice->id;

        if ($zohoInvoiceId === null) {
            $paymentSchedule = MemberPaymentSchedule::query()
                ->with('booking')
                ->whereHas('payments', fn (Builder $q) => $q->where('id', $payment->id))
                ->first();
            $zohoInvoiceId = $paymentSchedule->booking->zohoInvoice->id;
        }

        $paymentData = [
            'customer_id' => $contact['contact_id'],
            'payment_mode' => 'cash',
            'amount' => $payment->total_amount,
            'date' => Carbon::now()->format('Y-m-d'),
            'reference_number' => $payment->reference_id,
            'invoices' => [
                [
                    'invoice_id' => $zohoInvoiceId,
                    'amount_applied' => $payment->total_amount,
                ],
            ],
            'exchange_rate' => 1,
            'invoice_id' => $zohoInvoiceId,
            'amount_applied' => $payment->total_amount,
        ];

        $payment = $this->client->createRecord(
            'customerpayments',
            [
                'JSONString' => json_encode($paymentData, JSON_UNESCAPED_SLASHES),
            ],
        );

        return $payment;
    }

    public function createPaymentByRaw(Member $member, array $data): array
    {
        try {
            $contact = $this->getContactByMember($member);
        } catch (ZohoHTTPClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $contact = null;
            } else {
                throw $exception;
            }
        }

        $paymentData = [
            'customer_id' => $contact['contact_id'],
            'payment_mode' => 'cash',
            'date' => Carbon::now()->format('Y-m-d'),
            'exchange_rate' => 1,
        ];

        $paymentData = array_merge($paymentData, $data);

        $payment = $this->client->createRecord(
            'customerpayments',
            [
                'JSONString' => json_encode($paymentData, JSON_UNESCAPED_SLASHES),
            ],
        );

        return $payment;
    }

    public function createInvoiceByPayment(Payment $payment, bool $isMonthlyInvoice): ?array
    {
        try {
            $contact = $this->getContactByMember($payment->member);
        } catch (ZohoHTTPClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $contact = null;
            } else {
                throw $exception;
            }
        }

        if ($contact === null) {
            $contact = $this->createContactByMember($payment->member);
            ZohoCustomer::updateOrCreate(
                ['contact_id' => $contact['contact_id']],
                [
                    'member_id' => $payment->member?->member_id,
                    'contact_name' => $contact['contact_name'],
                    'email' => $contact['email'],
                ],
            );
        }

        $address = $this->getContactAddress($contact['contact_id'] ?? null);
        $details = $payment->member->memberBillingDetail;

        if ($address === null && $details !== null) {
            $address = $this->createContactAddress($contact['contact_id'], [
                'JSONString' => json_encode([
                    'address' => $details->street,
                    'city' => $details->city,
                    'state' => $details->country->iso,
                    'country' => $details->country->country_name,
                    'phone' => $payment->member->phone,
                ], JSON_UNESCAPED_SLASHES),
            ]);
        }

        $nowDate = Carbon::now()->format('Y-m-d');

        $invoiceData = [
            'reference_number' => $payment->booking->reference_id,
            'payment_terms' => 0,
            'payment_terms_label' => 'Due on Receipt',
            'customer_id' => $contact['contact_id'],
            'date' => $nowDate,
            'due_date' => $isMonthlyInvoice ? $payment->member->end_date->format('Y-m-d') : $nowDate,
            'is_inclusive_tax' => false,
            'line_items' => [
                [
                    'item_order' => 1,
                    'item_id' => settings('zoho_membership_item_id'),
                    'rate' => $isMonthlyInvoice
                        ? $payment->member->memberPaymentSchedule->booking->total_price
                        : $payment->subtotal_amount,
                    'name' => 'Membership Fee',
                    'description' => '',
                    'quantity' => '1.00',
                    'discount' => $payment->discount_amount,
                    'tax_id' => settings('zoho_tax_id'),
                    'tax_treatment_code' => '',
                    'project_id' => '',
                    //                            'account_id' => config('zoho.invoices.account_id'),
                    'item_custom_fields' => [
                    ],
                    'tags' => [
                    ],
                    'unit' => '',
                ],
            ],
            'allow_partial_payments' => false,
            'is_discount_before_tax' => true,
            'discount' => 0,
            'discount_type' => 'item_level',
            'adjustment' => '',
            'adjustment_description' => 'Adjustment',
            'pricebook_id' => '',
            'template_id' => settings('zoho_template_id'),
            'project_id' => '',
            'quick_create_payment' => [
                'account_id' => $payment->paymentMethod->zoho_chartofaccount_id ?? settings('zoho_account_deposit_id'),
                'payment_mode' => 'Cash',
            ],
            'tax_reg_no' => '',
            'tax_treatment' => 'vat_not_registered',
            'place_of_supply' => 'DU',
            'country_code' => 'DU',
            'billing_address_id' => $address['address_id'] ?? '',
        ];

        if ($isMonthlyInvoice === true) {
            $invoiceData['allow_partial_payments'] = true;
            unset($invoiceData['quick_create_payment']);
        }

        $invoiceResponse = $this->createInvoice($invoiceData);
        $invoice = $invoiceResponse['invoice'];

        ZohoInvoice::updateOrCreate(
            [
                'id' => $invoice['invoice_id'],
            ],
            [
                'customer_id' => $invoice['customer_id'],
                'invoice_number' => $invoice['invoice_number'],
                'status' => $invoice['status'] ?? null,
                'date' => $invoice['date'] ?? null,
                'created_time' => $invoice['created_time'] ?? null,
                'total' => $invoice['total'] ?? null,
                'discount' => $invoice['discount'] ?? null,
                'tax_total' => $invoice['tax_total'] ?? null,
                'invoice_url' => $invoice['invoice_url'] ?? null,
                'full_response' => json_encode($invoice),
                'booking_id' => $payment->booking->id,
            ],
        );

        $payment->zohoInvoice()->associate(ZohoInvoice::find($invoice['invoice_id']));
        $payment->save();

        return $invoice;
    }

    public function createInvoiceBySchedulePayment(Member $member, Booking $booking, array $data): ?array
    {
        try {
            $contact = $this->getContactByMember($member);
        } catch (ZohoHTTPClientException $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $contact = null;
            } else {
                throw $exception;
            }
        }

        if ($contact === null) {
            $contact = $this->createContactByMember($member);
            ZohoCustomer::updateOrCreate(
                ['contact_id' => $contact['contact_id']],
                [
                    'member_id' => $member?->member_id,
                    'contact_name' => $contact['contact_name'],
                    'email' => $contact['email'],
                ],
            );
        }

        $address = $this->getContactAddress($contact['contact_id'] ?? null);
        $details = $member->memberBillingDetail;

        if ($address === null && $details !== null) {
            $address = $this->createContactAddress($contact['contact_id'], [
                'JSONString' => json_encode([
                    'address' => $details->street,
                    'city' => $details->city,
                    'state' => $details->country->iso,
                    'country' => $details->country->country_name,
                    'phone' => $member->phone,
                ], JSON_UNESCAPED_SLASHES),
            ]);
        }

        $nowDate = Carbon::now()->format('Y-m-d');

        $invoiceData = [
            'payment_terms' => 0,
            'payment_terms_label' => 'Due on Receipt',
            'customer_id' => $contact['contact_id'],
            'date' => $nowDate,
            'due_date' => $member->end_date->format('Y-m-d'),
            'is_inclusive_tax' => false,
            'allow_partial_payments' => true,
            'is_discount_before_tax' => true,
            'discount' => 0,
            'discount_type' => 'item_level',
            'adjustment' => '',
            'adjustment_description' => 'Adjustment',
            'pricebook_id' => '',
            'template_id' => settings('zoho_template_id'),
            'project_id' => '',
            'tax_reg_no' => '',
            'tax_treatment' => 'vat_not_registered',
            'place_of_supply' => 'DU',
            'country_code' => 'DU',
            'billing_address_id' => $address['address_id'] ?? '',
        ];

        $invoiceData = array_merge($invoiceData, $data);

        $invoiceResponse = $this->createInvoice($invoiceData);
        $invoice = $invoiceResponse['invoice'];

        ZohoInvoice::updateOrCreate(
            [
                'id' => $invoice['invoice_id'],
            ],
            [
                'customer_id' => $invoice['customer_id'],
                'invoice_number' => $invoice['invoice_number'],
                'status' => $invoice['status'] ?? null,
                'date' => $invoice['date'] ?? null,
                'created_time' => $invoice['created_time'] ?? null,
                'total' => $invoice['total'] ?? null,
                'discount' => $invoice['discount'] ?? null,
                'tax_total' => $invoice['tax_total'] ?? null,
                'invoice_url' => $invoice['invoice_url'] ?? null,
                'full_response' => json_encode($invoice),
                'booking_id' => $booking->id,
            ],
        );

        return $invoice;
    }

    public function createInvoice(array $data): ?array
    {
        return $this->client->createRecord(
            'invoices',
            [
                'is_quick_create' => 'true',
                'JSONString' => json_encode($data, JSON_UNESCAPED_SLASHES),
            ],
        );
    }
}
