<?php

namespace App\Http\Controllers\Api\Crm;

use App\Actions\SalesQuote\SalesQuoteCalculateAction;
use App\Actions\SalesQuote\SalesQuoteCreatePdfAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\SalesQuote\CalculateRequest;
use App\Http\Resources\SalesQuote\CalculateResource;
use App\Models\SalesQuote;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesQuoteController extends Controller
{
    public function getPdf(SalesQuote $salesQuote): StreamedResponse
    {
        $filePath = $salesQuote->getPdfFilePath();
        if (!file_exists($filePath)) {
            (new SalesQuoteCreatePdfAction())->handle($salesQuote);
        }

        return Storage::response(
            $filePath,
            \Str::slugExtended(
                $salesQuote->corporate_client
            ).'-advplus-'.$salesQuote->updated_at->format('d-M-Y').'.'.\File::extension($filePath)
        );
    }

    public function getCard(CalculateRequest $request): Factory|View|Application
    {
        $storeData = (new CalculateResource($request))->toArray($request);
        /** @var SalesQuote $salesQuote */
        if ($quoteId = $request->id) {
            $salesQuote = SalesQuote::findOrFail($quoteId);
            $salesQuote->setRawAttributes($storeData);
        } else {
            $salesQuote = new SalesQuote($storeData);
        }
        $salesQuote->json_data = [
            'calculated' => (new SalesQuoteCalculateAction($salesQuote))->handle([
                'duration' => SalesQuote::monthsToDays($request->duration),
                'families_count' => $request->families_count,
                'singles_count' => $request->singles_count,
                'clubs_count' => $request->clubs_count,
            ]),
        ];

        return view('sales-quote.card', [
            'displayCommission' => true,
            'salesQuote' => $salesQuote,
            'calculated' => $salesQuote->json_data['calculated'],
        ]);
    }
}
