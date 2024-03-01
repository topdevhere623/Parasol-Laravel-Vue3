<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Requests\MemberPortal\ClubFavoritesRequest;
use App\Http\Resources\MemberPortal\ClubResource;
use App\Models\Club\Club;
use App\Models\QueryFilters\ClubFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ClubController extends MemberPortalBaseController
{
    public function index(): AnonymousResourceCollection
    {
        return $this->clubs(Auth::user()->checkinAvailableClubs());
    }

    public function allClubs(): AnonymousResourceCollection
    {
        return $this->clubs(Auth::user()->plan->availableClubs());
    }

    private function clubs(BelongsToMany|Builder $query): AnonymousResourceCollection
    {
        $this->abortNoAccess('clubs');

        $filter = new ClubFilter(request()->all());

        $query->select('clubs.*')
            ->addSelect([
                'is_favorite' => fn ($query) => $query->selectRaw('count(*)')
                    ->from('member_club_favorite')
                    ->whereColumn('member_club_favorite.club_id', 'clubs.id')
                    ->where('member_club_favorite.member_id', Auth::id()),
            ])
            ->reorder()
            ->orderBy('is_favorite', 'desc')
            ->orderBy('title')
            ->filter($filter);

        return ClubResource::collection(
            $query->paginate(config('advplus.default_offer_response_limit'))
        );
    }

    public function show(string $slug): ClubResource
    {
        $this->abortNoAccess('clubs');

        $club = Club::whereSlug($slug)
            ->with('city')
            ->with('gallery')
            ->with('activeOffers')
            ->with(
                ['memberFavorites' => fn ($query) => $query->where('member_club_favorite.member_id', Auth::id())]
            )
            ->firstOrFail();

        return new ClubResource($club);
    }

    public function addToFavorites(ClubFavoritesRequest $request): JsonResponse
    {
        return $this->toggleFavorites($request, 'add');
    }

    public function removeFromFavorites(ClubFavoritesRequest $request): JsonResponse
    {
        return $this->toggleFavorites($request, 'remove');
    }

    private function toggleFavorites(ClubFavoritesRequest $request, $action): JsonResponse
    {
        $club = Club::where('uuid', $request->input('uuid'))->firstOrFail();

        $add = $action === 'add';

        $method = $action === 'add' ? 'syncWithoutDetaching' : 'detach';
        Auth::user()->favoriteClubs()->{$method}($club);

        return response()->json([
            'message' => 'Club was successfully '.($add ? 'added to' : 'removed from').' favorites.',
        ]);
    }
}
