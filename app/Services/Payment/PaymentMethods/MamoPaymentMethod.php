<?php

namespace App\Services\Payment\PaymentMethods;

use App\Exceptions\Payments\MakePaymentException;
use App\Models\Member\MemberPaymentSchedule;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentMamoLink;

use App\Services\Payment\Models\Customer;
use App\Services\Payment\Models\Product;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Symfony\Component\HttpFoundation\Response;

// Note: Last slash on tabby api is required

class MamoPaymentMethod
{
    protected PendingRequest $httpClient;

    public function __construct(?string $apiKey, $sandboxMode = false)
    {
        $apiUrl = $sandboxMode ? 'https://sandbox.dev.business.mamopay.com/manage_api/v1/' : 'https://business.mamopay.com/manage_api/v1/';

        $http = new PendingRequest();

        $this->httpClient = $http->baseUrl($apiUrl)
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->retry(3, 5000, function ($exception) {
                $code = $exception->getCode();
                return $exception instanceof RequestException && ($code == Response::HTTP_REQUEST_TIMEOUT || $code >= 500);
            })
            ->withOptions([
                'debug' => config('app.debug') && app()->runningInConsole(),
            ]);
    }

    public function getPaymentLink(
        Payment|MemberPaymentSchedule $payable,
        Customer $customer,
        Product $product,
        array $params
    ): \stdClass {
        $paymentMamoLink = PaymentMamoLink::whereMorphedTo('payable', $payable)
            ->active()
            ->first();

        if ($paymentMamoLink) {
            return (object)[
                'paymentMamoLink' => $paymentMamoLink,
                'url' => $paymentMamoLink->link,
            ];
        }

        return $this->createPaymentLink($payable, $customer, $product, $params);
    }

    public function createPaymentLink(
        Payment|MemberPaymentSchedule $payable,
        Customer $customer,
        Product $product,
        array $params
    ): \stdClass {
        $paymentMamoLink = new PaymentMamoLink();
        $paymentMamoLink->payable()->associate($payable);
        $paymentMamoLink->save();

        $saveCard = $params['save_card'] ?? false;

        $request = [
            'title' => $product->getReferenceId(),
            'capacity' => 1,
            'return_url' => route('mamo-result', [
                'paymentMamoLink' => $paymentMamoLink->uuid,
            ]),
            'failure_return_url' => route('mamo-result', [
                'paymentMamoLink' => $paymentMamoLink->uuid,
            ]),
            'amount' => $product->getTotalPrice(),
            'first_name' => $customer->getFirstName(),
            'last_name' => $customer->getLastName(),
            'email' => $customer->getEmail(),
            'external_id' => $product->getReferenceId(),
            'send_customer_receipt' => false,
            'save_card' => $saveCard ? 'required' : 'off',
        ];

        try {
            $response = $this->httpClient->post('links', $request);
        } catch (\Exception $exception) {
            report($exception);
            throw new MakePaymentException(0, $exception);
        }

        $paymentMamoLink->mamo_id = $response->json('id');
        $paymentMamoLink->response = $response->json();
        $paymentMamoLink->link = $response->json('payment_url');
        $paymentMamoLink->save();

        return (object)[
            'paymentMamoLink' => $paymentMamoLink,
            'url' => $paymentMamoLink->link,
        ];
    }

    public function getPayment(string $id): array|null|object
    {
        $response = $this->httpClient
            ->get('charges/'.$id);

        if ($response->successful()) {
            return $response->object();
        }

        return null;
    }

    public function getLink(string $id): array|null|object
    {
        $response = $this->httpClient
            ->get('links/'.$id);

        if ($response->successful()) {
            return $response->object();
        }

        return null;
    }

}
