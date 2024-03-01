<?php

use App\Models\BlogPost\BlogPost;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->tinyText('title');
            $table->tinyText('wrapped_title');
            $table->tinyText('meta_title')->nullable();
            $table->tinyText('meta_description')->nullable();
            $table->tinyText('slug');
            $table->tinyText('cover_image');
            $table->tinyText('preview_image');
            $table->text('text');
            $table->tinyText('blogger_photo')->nullable();
            $table->tinyText('blogger_link')->nullable();
            $table->tinyText('blogger_name')->nullable();
            $table->boolean('blogger_show');
            $table->boolean('featured')->default(false);
            $table->enum('status', ['active', 'inactive'])
                ->default('active')
                ->index();
            $table->timestamp('date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_post_related', function (Blueprint $table) {
            $table->foreignId('blog_id');
            $table->foreignId('related_blog_post_id');

            $table->primary(['blog_id', 'related_blog_post_id']);
        });

        seed_permissions(BlogPost::class, 'Blog Posts', ['supervisor', 'manager', 'Marketing manager']);
    }

    public function down()
    {
        Schema::dropIfExists('blog_posts');
    }
};
