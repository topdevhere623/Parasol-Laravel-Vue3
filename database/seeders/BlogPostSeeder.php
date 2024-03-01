<?php

namespace Database\Seeders;

use App\Models\BlogPost\BlogPost;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public const BLOGS_COUNT = 30;

    public function run()
    {
        // ordinary blogs
        $blogPosts = BlogPost::factory()->count(self::BLOGS_COUNT)->create([
            'featured' => false,
        ]);
        // featured blog
        $blogPosts->merge(
            BlogPost::factory()->count(1)->create([
                'featured' => true,
            ])
        );

        // related blogs
        /** @var Generator $faker */
        $faker = Factory::create('en_EN');
        foreach ($blogPosts as $ind => $blogPost) {
            if (!$relatedBlogsCount = $faker->numberBetween(0, self::BLOGS_COUNT - 1)) {
                continue;
            }
            $relatedBlogIds = [];
            for ($i = 0; $i < $relatedBlogsCount; $i++) {
                $relatedBlogIndex = $faker->numberBetween(0, self::BLOGS_COUNT - 1);
                if ($relatedBlogIndex === $ind) {
                    continue;
                }
                $relatedBlogId = $blogPosts[$relatedBlogIndex]->id;
                if (in_array($relatedBlogId, $relatedBlogIds)) {
                    continue;
                }
                $blogPost->relatedBlogs()->attach($relatedBlogId);
                $relatedBlogIds[] = $relatedBlogId;
            }
            $blogPost->save();
        }
    }
}
