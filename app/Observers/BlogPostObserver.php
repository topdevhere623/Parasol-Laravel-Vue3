<?php

namespace App\Observers;

use App\Models\BlogPost\BlogPost;
use Illuminate\Support\Str;

class BlogPostObserver
{
    public function saving(BlogPost $blogPost): void
    {
        $blogPost->slug = Str::slugExtended(strip_tags($blogPost->title));
        $blogPost->wrapped_title = $blogPost->wrapped_title ?: wrapText($blogPost->title, 24);
        $blogPost->meta_title = $blogPost->meta_title ?: $blogPost->title;
        $blogPost->meta_description = $blogPost->meta_description ?: $blogPost->title;
        if ($blogPost->featured) {
            $blogPost->status = BlogPost::STATUSES['active'];
            if ($featuredBlogPost = BlogPost::where('featured', true)
                ->first()) {
                $featuredBlogPost->updateQuietly([
                    'featured' => false,
                ]);
            }
        }
    }
}
