<?php

namespace App\Observers;

use App\Actions\SalesQuote\SalesQuoteCalculateAction;
use App\Actions\SalesQuote\SalesQuoteCreatePdfAction;
use App\Models\SalesQuote;
use Illuminate\Support\Str;

class SalesQuoteObserver
{
    private SalesQuoteCalculateAction $action;

    public function creating(SalesQuote $salesQuote): void
    {
        $salesQuote->uuid = Str::uuid()->toString();
    }

    public function created(SalesQuote $salesQuote): void
    {
        $this->action = new SalesQuoteCalculateAction($salesQuote);
        $salesQuote->updateQuietly([
            'json_data' => [
                'adjustments' => SalesQuoteCalculateAction::ADJUSTMENTS,
                'discounts' => SalesQuoteCalculateAction::DISCOUNTS,
                'single_prices' => SalesQuoteCalculateAction::SINGLE_PRICES,
                'commissions' => SalesQuoteCalculateAction::COMMISSIONS,
                'calculated' => $this->action->handle(),
            ],
        ]);
        (new SalesQuoteCreatePdfAction())->handle($salesQuote);
    }

    public function updating(SalesQuote $salesQuote): void
    {
        $action = new SalesQuoteCalculateAction($salesQuote);
        $jsonData = $salesQuote->json_data;
        $jsonData['calculated'] = $action->handle();
        $salesQuote->json_data = $jsonData;
        $this->action = new SalesQuoteCalculateAction($salesQuote);
        $calculateData = $this->getCalculateData($salesQuote);
        $salesQuote->updateQuietly([
            'json_data' => [
                'adjustments' => $salesQuote->json_data['adjustments'],
                'discounts' => $salesQuote->json_data['discounts'],
                'single_prices' => $salesQuote->json_data['single_prices'],
                'commissions' => $salesQuote->json_data['commissions'],
                'calculated' => $this->action->handle($calculateData),
            ],
        ]);
        (new SalesQuoteCreatePdfAction())->handle($salesQuote);
    }

    private function getCalculateData($salesQuote)
    {
        return [
            'duration' => SalesQuote::monthsToDays($salesQuote->duration),
            'families_count' => $salesQuote->families_count,
            'singles_count' => $salesQuote->singles_count,
            'clubs_count' => $salesQuote->clubs_count,
        ];
    }
}
