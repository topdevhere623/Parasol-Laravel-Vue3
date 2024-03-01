<?php

namespace App\Http\Controllers\Web;

use App\Models\WebSite\FaqCategory;

class FaqController extends Controller
{
    public function faq()
    {
        $faqs = FaqCategory::active()
            ->with('activeFaqs')
            ->oldest('sort')
            ->get();

        return view('faq', [
            'faqs' => $faqs,
            'body_class' => 'faq',
        ]);
    }
}
