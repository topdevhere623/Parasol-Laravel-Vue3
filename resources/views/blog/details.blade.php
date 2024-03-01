@extends('layouts.blog', [
    'page' => 'details',
    'footerHeadingText' => 'Get the<span>Best Deals</span> Across All Clubs with Adv+ Membership',
    'footerSubheadingText' => 'No Limits, Just Savings',
])

@section('blog-content')
  <div class="blog-container d-flex flex-column align-items-center">
    <div class="post-details d-flex flex-column">
      <div class="lasting">
        {{ $blog->reading_time }} MIN READ
      </div>
      <h1>{{ $blog->title }}</h1>

      <div class="about d-flex flex-row align-items-center">
        @if($blog->blogger_show == 1)
          <a href="{{ $blog->blogger_link }}" class="d-flex flex-row" target="_blank">
            <img class="ava" src="{{ file_url($blog, 'blogger_photo', 'small') }}" alt="blogger photo">
            <div class="blogger-name">{{ $blog->blogger_name }}</div>
          </a>
          &nbsp;|&nbsp;
        @endif
        <span>{{ $blog->date_formatted }}</span>
      </div>

      <div class="cover">
        <img src="{{ file_url($blog, 'cover_image', 'large') }}"
             alt="{{ $blog->title }}"
          {{--             srcset="{{ file_url($blog, 'cover_image', 'medium') }} 480w, {{ file_url($blog, 'cover_image', 'large') }} 800w"--}}
          {{--             sizes="(max-width: 600px) 480px,         800px"--}}
        >
      </div>
      <div class="blog-text">
        {!! $blog->text !!}
      </div>
    </div>
  </div>

  @if(count($blogs))
    <div class="related-blogs d-flex flex-column align-items-center w-100">
      <h1>Related Blogs</h1>

      @include('blog.blog-posts', compact('blogs'))
    </div>
  @endif

@endsection

