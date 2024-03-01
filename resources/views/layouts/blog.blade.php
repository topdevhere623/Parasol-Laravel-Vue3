@extends('layouts.main')

@section('content')

  <div class="blog">
    @if($page=='index')
      <section class="blog-header">
        <span class="overlay-bg"></span>
        <div class="container d-flex flex-column align-items-center">
          <div class="mb-3 fw-normal text-uppercase">
            <a href="/">Home</a> > <span>Blogs</span>
          </div>
          <h1>
            Blogs
          </h1>
        </div>
      </section>
    @endif

    @yield('blog-content')

    <section class="blog-footer background-{{ $page }} d-flex flex-column">
      <p class="blogs-slogan">
        {!! $footerHeadingText !!}
      </p>
      <p>
        {{ $footerSubheadingText }}
      </p>
      <a
        href="{{ $theme->joinLink }}"
        class="btn bg-white text-black rounded-pill text-transform-none py-1"
      >
        Join today
      </a>
    </section>

  </div>

@endsection
