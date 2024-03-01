<?php

namespace App\Actions\SalesQuote;

use App\Models\SalesQuote;
use App\Services\PdfService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SalesQuoteCreatePdfAction
{
    public function handle(SalesQuote $salesQuote)
    {
        $tempPath = \Storage::disk('temp')->path($salesQuote->getPdfFilePath());

        (new PdfService())->create(
            view('sales-quote.pdf', [
                'salesQuote' => $salesQuote,
                'companyDetails' => (object)settings('company_details'),
                'calculated' => $salesQuote->json_data['calculated'],
            ])->render(),
            $tempPath
        );

        Storage::put(
            $salesQuote->getPdfFilePath(),
            file_get_contents($tempPath)
        );

        File::delete($tempPath);
    }
}
