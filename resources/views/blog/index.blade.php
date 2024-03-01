@extends('layouts.blog', [
    'page' => 'index',
    'footerHeadingText' => 'The<span>Lifestyle</span>Membership You Deserve',
    'footerSubheadingText' => 'at a price you can afford',
])

@section('blog-content')

  <div class="blog-container blog-list">
    <div class="header d-flex align-items-center">
      @if(empty($query))
        <h1>{{ settings('blogs_heading') }}</h1>
      @else
        <h2>Search results for <span class="query">{{ $query }}</span></h2>
      @endif

      @include('blog.filter-form', compact('query', 'sort'))
    </div>

    <div class="contents d-flex flex-column">

      @if(empty($query))
        <div class="posts row first d-flex flex-row flex-wrap">
          @if($blog)
            <div class="col-sm-12 col-md-6 col-lg-8">
              <div class="main-post d-flex flex-column">
                @include('blog.arrow-link', [
                  'link' => $blog->link,
                ])
                <span class="overlay-bg"></span>
                <img src="{{ file_url($blog, 'cover_image', 'medium') }}" alt="{{ $blog->title }}">
                <div class="main-post-contents d-flex flex-column position-absolute justify-content-end">
                <span class="create-date">
                  {{ $blog->date_formatted }}
                </span>
                  <a href="{{ $blog->link }}" class="title">
                    {!! $blog->wrapped_title !!}
                  </a>
                </div>
              </div>
            </div>
          @endif

          <div class="col-sm-12 col-md-6 col-lg-4">
            <div class="banner d-flex flex-column">
              @include('blog.arrow-link', ['link' => settings('blogs_banner_link')])
              <img src="{{ url('/assets/images/blog/rocket.png') }}" alt="rocket">
              <h4>Want to Upgrade Your Lifestyle</h4>
              <p>Choose the right plan for you</p>

              <a href="{{ settings('blogs_banner_link') }}">Check Plans</a>
            </div>
          </div>
        </div>
        <hr/>

      @endif
      @if(count($blogs))
        @include('blog.blog-posts', compact('blogs'))

        {{ $blogs->onEachSide(1)->links('blog.pagination') }}
      @endif

    </div>
  </div>

@endsection
