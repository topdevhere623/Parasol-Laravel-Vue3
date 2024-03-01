<?php

namespace App\Http\Controllers\Web;

use App\Models\Club\Club;
use App\Models\Club\ClubTag;
use App\Models\Program;
use App\Services\WebsiteThemeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ClubController extends Controller
{
    public function index(Request $request, WebsiteThemeService $websiteThemeService): View
    {
        $tag = null;
        $request->whenFilled('tag', function ($inputTag) use (&$tag) {
            $tag = ClubTag::where('slug', $inputTag)->first();
        });

        $websiteThemeService->setMetaTitle($tag ? $tag->name : 'Clubs');
        $websiteThemeService->setMetaDescription(__($websiteThemeService->metaTagsFile.'.clubs_description'));

        $clubs = $this->getClubsQuery()
            ->with('city')
            ->with('tags')
            ->when(
                $tag,
                fn ($query) => $query->whereHas('tags', fn ($query) => $query->where('club_tag.club_tag_id', $tag->id))
            )
            ->get();

        $clubCities = $clubs->groupBy('city_id')
            ->map(function ($clubs) {
                return [
                    'name' => $clubs->first()->city->name,
                    'count' => $clubs->count(),
                ];
            });

        $tags = ClubTag::has('clubs')
            ->get();

        return view('club.club-list', compact('clubs', 'clubCities', 'tag', 'tags'));
    }

    public function show($slug, WebsiteThemeService $websiteThemeService): View|RedirectResponse
    {
        $club = $this->getClubsQuery()
            ->where('slug', $slug)
            ->first();

        if (!$club) {
            return redirect()->route(
                route: 'website.clubs.index',
                status: Response::HTTP_TEMPORARY_REDIRECT
            );
        }

        // TODO: need to get clubs in this city or by the type of view
        $clubs = $this->getClubsQuery()
            ->inRandomOrder()
            ->limit(3)
            ->get();

        $websiteThemeService->setMetaTitle(
            $club->meta_title
            ?? $club->title.' - Club Membership'
        );
        $websiteThemeService->setMetaDescription(
            $club->meta_description
            ?? $club->title.' '.__($websiteThemeService->metaTagsFile.'.club_detail_description_postfix')
        );

        return view('club.club-detail', compact('club', 'clubs'));
    }

    private function getClubsQuery(): Builder
    {
        if (is_entertainer_subdomain()) {
            return Program::find(Program::ENTERTAINER_SOLEIL_ID)
                ->clubDocumentPlan
                ->availableClubs()
                ->sort(Program::ENTERTAINER_SOLEIL_ID);
        }

        return Club::query()
            ->webSite()
            ->with('gallery')
            ->sort();
    }
}
