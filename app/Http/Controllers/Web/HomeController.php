<?php

namespace App\Http\Controllers\Web;

use App\Models\Club\Club;
use App\Models\OurPartner;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Testimonial;
use App\Models\WebSite\PackageInfo;
use App\Services\WebsiteThemeService;
use URL;

class HomeController extends Controller
{
    public function index()
    {
        $theme = app(WebsiteThemeService::class);
        if (is_entertainer_subdomain()) {
            return $this->entertainerSoleil($theme);
        }

        return $this->homePage($theme);
    }

    private function homePage(WebsiteThemeService $theme)
    {
        $theme
            ->setJoinLink('#join')
            ->setSloganBackgroundImage('/assets/images/banner.jpg')
            ->setSloganBackgroundImageMobile('/assets/images/banner-mobile.jpg');

        $instagramPhotos = \Cache::get('homeInstagramFeed', []);
        $clubs = Club::website()->sort(Program::ADV_PLUS_ID)->get();
        $packages = $this->getPackages(Program::ADV_PLUS_ID);
        $testimonials = $this->getTestimonials(Program::ADV_PLUS_ID);
        $partners = $this->getPartners(Program::ADV_PLUS_ID);
        $map = URL::uploads('map/dubai_map.png');

        // $clubs = Cache::remember('homeClubs', 300, fn() => Club::website()->sort()->get());
        // $packages = Cache::remember('homePackageInfo', 300, fn() => PackageInfo::active()->get());
        // $partners = Cache::remember('homeOurPartner', 300, fn() => OurPartner::all());
        // $testimonials = Cache::remember('homeTestimonials', 300, fn() => Testimonial::active()->get());
        // $map = Cache::remember('homeMap', 300, fn() => \URL::uploads('map/dubai_map.png'));

        return view(
            'home.index',
            compact(
                'clubs',
                'instagramPhotos',
                'packages',
                'partners',
                'testimonials',
                'map'
            )
        );
    }

    private function entertainerSoleil(WebsiteThemeService $theme)
    {
        $theme
            ->setFromSoleil()
            ->setBodyClass('entertainer')
            ->setJoinLink('#join');

        $clubs = Program::find(Program::ENTERTAINER_SOLEIL_ID)
            ->clubDocumentPlan
            ->availableClubs()
            ->sort(Program::ENTERTAINER_SOLEIL_ID)
            ->website()
            ->get();

        return view(
            'home.entertainer',
            [
                'clubs' => $clubs,
                'map' => URL::uploads('map/dubai_map.png'),
                'partners' => $this->getPartners(Program::ENTERTAINER_SOLEIL_ID),
                'packages' => $this->getPackages(Program::ENTERTAINER_SOLEIL_ID),
                'testimonials' => $this->getTestimonials(Program::ENTERTAINER_SOLEIL_ID),
            ]
        );
    }

    private function getPackages(int $programId)
    {
        return PackageInfo::where('program_id', $programId)
            ->active()
            ->get();
    }

    private function getTestimonials(int $programId)
    {
        return Testimonial::where('program_id', $programId)
            ->active()
            ->get();
    }

    private function getPartners(int $programId)
    {
        return OurPartner::query()
            ->whereHas(
                'programs',
                function ($query) use ($programId) {
                    $query
                        ->select('id')
                        ->where('id', $programId);
                }
            )
            ->get();
    }

    public function hsbc(WebsiteThemeService $theme)
    {
        $theme
            ->hideHeader()
            ->hidePreFooterContacts()
            ->hideFooter();

        if ($plan = Plan::where('id', Plan::HSBC_LANDING_PLAN_ID)->first()) {
            $clubs = $plan->availableClubs()
                ->sort(Program::ENTERTAINER_HSBC)
                ->get();
        } else {
            report(new \Exception('HSBC Landing plan not found. ID: '.Plan::HSBC_LANDING_PLAN_ID));
            $clubs = [];
        }

        return view(
            'home.hsbc',
            compact('clubs')
        );
    }
}
