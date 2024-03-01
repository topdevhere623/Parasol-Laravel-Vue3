<?php

namespace App\Http\Controllers\Web;

use App\Models\Program;
use App\Models\WebSite\Page;
use App\Services\WebsiteThemeService;

class PageController extends Controller
{
    public function redirectSlug()
    {
        return redirect('/checkout?package=exclusive-steppi&plan=14', 301);
    }

    public function page($slug)
    {
        $page = Page::active()
            ->where(['slug' => $slug])
            ->firstOrFail();

        return view('page.page', compact('page'));
    }

    public function hsbcFaq(WebsiteThemeService $theme)
    {
        $page = Page::active()
            ->where(['slug' => 'entertainer-hsbc-soleil-faqs'])
            ->firstOrFail();

        $hsbcProgram = Program::where('source', Program::SOURCE_MAP['hsbc'])->first();
        $theme->setFromProgram($hsbcProgram);
        $theme->hideFooter();
        $theme->showHeaderMenu = false;
        $theme->showBreadcrumbs = false;

        return view('page.page', compact('page'));
    }

    public function getCountries()
    {
        return json_decode(file_get_contents('data/country.json'));
    }

    public function links()
    {
        return view('page.links', [
            'links' => settings('links'),
        ]);
    }

    public function instalmentsPayments(WebsiteThemeService $theme)
    {
        $theme->hidePreFooterContacts();

        return view('page.how-it-work');
    }
}
