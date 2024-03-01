<?php

namespace App\Models\QueryFilters;

use App\Models\BlogPost\BlogPost;

class BlogPostFilter extends QueryFilter
{
    protected $searchable = [
        'title',
    ];

    public function filterQuery($value)
    {
        return $this->builder
            ->where('title', 'like', "%{$value}%")
            ->orWhere('text', 'like', "%{$value}%");
    }

    public function filterSort($value)
    {
        $builder = $this->builder;
        $builder->getQuery()->orders = null;

        switch ($value) {
            case 'alphabetical':
                $builder->orderBy('title');
                break;
                //            case 'popular':
                //                $builder
                //                    ->leftJoin(
                //                        'visits',
                //                        fn ($join) => $join
                //                            ->on('visitable_id', 'blog_posts.id')
                //                            ->where('visitable_type', BlogPost::class)
                //                    )
                //                    ->orderBy('count', 'desc');
                //                break;
            case 'recent':
            default:
                $builder->orderBy('date', 'desc');
        }

        return $builder;
    }
}
