<?php

namespace Database\Factories\BlogPost;

use App\Models\BlogPost\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Intervention\Image\Facades\Image;
use Storage;

class BlogPostFactory extends Factory
{
    public const PUBLIC_DIR = 'storage/app/public';
    public const BLOG_POST_DIR = 'blog-post';
    public const IMAGES_DIR = self::PUBLIC_DIR.'/'.self::BLOG_POST_DIR.'/';

    protected $model = BlogPost::class;

    public function definition()
    {
        $faker = \Faker\Factory::create('en_EN');
        $title = $faker->text(100);
        $coverPath = self::IMAGES_DIR.'cover/980x450';
        $coverFilePath = $faker->image($coverPath, 980, 450);
        $coverFileName = str_replace("{$coverPath}/", '', $coverFilePath);
        $this->resize($coverFilePath, $coverFileName, 'cover', 707, 402);
        $this->resize($coverFilePath, $coverFileName, 'cover', 333, 323);
        $bloggerPhotoPath = self::IMAGES_DIR.'blogger-photo/72x72';
        $bloggerPhoto = str_replace("{$bloggerPhotoPath}/", '', $faker->image($bloggerPhotoPath, 72, 72));

        return [
            'title' => $title,
            'meta_title' => $title,
            'meta_description' => $title,
            'cover_image' => $coverFileName,
            'preview_image' => $coverFileName,
            'text' => $faker->text(1001),
            'blogger_show' => $faker->boolean,
            'blogger_link' => $faker->url,
            'blogger_name' => "{$faker->firstName} {$faker->lastName}",
            'blogger_photo' => $bloggerPhoto,
            'date' => $faker->dateTime,
        ];
    }

    private function resize($filePath, $fileName, $folder, $width, $height)
    {
        $filePath = str_replace(self::PUBLIC_DIR, '', $filePath);
        $file = Storage::disk('public')->get($filePath);
        $image = Image::make($file)->resize($width, $height)->stream();
        Storage::disk('public')->put(self::BLOG_POST_DIR."/{$folder}/{$width}x{$height}/{$fileName}", $image);
    }
}
