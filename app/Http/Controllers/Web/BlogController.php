<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\BlogRequest;
use App\Jobs\SaveVisit;
use App\Models\BlogPost\BlogPost;
use App\Models\QueryFilters\BlogPostFilter;
use App\Services\WebsiteThemeService;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public const PAGE_SIZE = 6;

    public function index(BlogRequest $request, BlogPostFilter $filter, WebsiteThemeService $websiteThemeService): View
    {
        $blogs = BlogPost::notFeatured()
            ->active()
            ->filter($filter)
            ->paginate(self::PAGE_SIZE)
            ->withQueryString();

        $websiteThemeService->hidePreFooterContacts();
        $websiteThemeService->setMetaTitle(settings('blogs_heading'));
        $websiteThemeService->setMetaDescription(settings('blogs_meta_description'));

        return view('blog.index', [
            'blogs' => $blogs,
            'blog' => BlogPost::featured()->active()->first(),
            'sort' => $request->input('sort', 'recent'),
            'query' => $request->input('query'),
        ]);
    }

    public function show(BlogPost $blog, WebsiteThemeService $websiteThemeService): View
    {
        //        SaveVisit::dispatch([
        //            'visitable_type' => BlogPost::class,
        //            'visitable_id' => $blog->id,
        //        ]);

        $websiteThemeService->hidePreFooterContacts();
        $websiteThemeService->setMetaTitle($blog->meta_title);
        $websiteThemeService->setMetaDescription($blog->meta_description);

        return view('blog.details', [
            'blog' => $blog,
            'blogs' => $blog->relatedBlogs()
                ->limit(3)
                ->get(),
        ]);
    }
}
