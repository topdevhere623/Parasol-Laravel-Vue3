<?php

namespace App\Providers;

use App\Services\Payment\PaymentMethods\AmazonPayfortPaymentMethod;
use App\Services\Payment\PaymentMethods\CheckoutPaymentMethod;

use App\Services\Payment\PaymentMethods\MamoPaymentMethod;
use App\Services\Payment\PaymentMethods\PaytabsPaymentMethod;
use App\Services\Payment\PaymentMethods\TabbyPaymentMethod;
use Illuminate\Support\ServiceProvider;

class PaymentMethodServiceProvider extends ServiceProvider
{
    // Executors must be named "payment-executor-{payment_methods.code}"
    public function register()
    {
        $this->app->singleton(CheckoutPaymentMethod::class, function () {
            return new CheckoutPaymentMethod(
                config('services.checkout.secret_key'),
                !app()->environment('production')
            );
        });

        $this->app->singleton(AmazonPayfortPaymentMethod::class, function () {
            return new AmazonPayfortPaymentMethod(
                config('services.amazon_payfort.merchant_identifier'),
                config('services.amazon_payfort.access_code'),
                config('services.amazon_payfort.sha_request_phrase'),
                config('services.amazon_payfort.sha_response_phrase'),
                config('services.amazon_payfort.sha_type'),
                !app()->isProduction()
            );
        });

        $this->app->singleton(TabbyPaymentMethod::class, function () {
            return new TabbyPaymentMethod(
                config('services.tabby.secret_key'),
            );
        });

        $this->app->singleton(PaytabsPaymentMethod::class, function () {
            return new PaytabsPaymentMethod(
                config('services.paytabs.server_key'),
                config('services.paytabs.profile_id'),
            );
        });

        $this->app->singleton(MamoPaymentMethod::class, function () {
            return new MamoPaymentMethod(
                config('services.mamo.api_key'),
                !app()->isProduction()
            );
        });
    }
}
