<div class="posts row d-flex flex-row flex-wrap">
  @foreach($blogs as $blog)
    <div class="col-sm-12 col-md-6 col-lg-4">
      <div class="post-preview">
        <div class="cover">
          <img src="{{ file_url($blog, 'preview_image', 'medium', file_url($blog, 'cover_image', 'medium')) }}"
               alt="{{ $blog->title }}">
          @include('blog.arrow-link', [
              'link' => $blog->link,
          ])
        </div>
        <p class="create-date">
          {{ $blog->date_formatted }}
        </p>
        <a href="{{ $blog->link }}" class="title">
          {{ $blog->title }}
        </a>
      </div>
    </div>
  @endforeach
</div>
