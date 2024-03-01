@extends('layouts.main')

@include('partials.theme-variables')

@section('content')
  @if($club)
    <section class="page-header page-header-club-detail pb-3 pb-md-5">
      <div class="container">
        <ul vocab="http://schema.org/" typeof="BreadcrumbList" class="breadcrumbs">
          <li property="itemListElement" typeof="ListItem">
            <a property="item" typeof="WebPage" href="{{ route('home') }}">Home</a>
          </li>
          <li property="itemListElement" typeof="ListItem">
            <a property="item" typeof="WebPage" href="{{ route('website.clubs.index') }}">Clubs</a>
          </li>
          <li>{{$club->title}}</li>
        </ul>

        <h1 class="text-uppercase mt-4 fw-bolder">{{$club->title}}</h1>
        <a class="address" href="{{$club->gmap_link}}" target="_blank" rel="noopener"
           title="{{$club->address}}">
          <img class="me-2 mb-1" src="{{ asset('assets/images/pin.png') }}" alt="pin"
               width="12" height="16" /> {{$club->address}}</a>
      </div>
    </section>

    <section class="bg-white club-detail py-2 py-md-5">
      <div class="container">
        <div class="club-photos d-none d-md-flex">
          <div class="club-photos__left-col">
            <a class="d-block" data-fancybox="gallery" href="{{ file_url($club, 'club_photo', 'large') }}">
              <img src="{{ file_url($club, 'club_photo', 'medium') }}"
                   alt="{{ $club->title }}" />
            </a>
            @if($club->youtube)
              <a data-fancybox
                 href="{{ str_replace("/embed/","/watch?v=", $club->youtube) }}"
                 class="club-photos__youtube" data-lightbox="iframe" itemprop="url">
                Watch video
                <img src="{{ asset('assets/images/youtube.svg') }}" style="margin-left:3px;"
                     alt="Youtube" width="18" height="18"></a>
            @endif
          </div>
          <div class="club-photos__right-col">
            @foreach($club->gallery as $key => $image)
              @if($key < 4)
                <a class="d-block" data-fancybox="gallery"
                   href="{{ file_url($image, 'name', 'large') }}">
                  <img src="{{ file_url($image, 'name', 'medium') }}" alt="{{ $club->title }} {{ $key + 1 }}"/>
                </a>
              @endif
            @endforeach
          </div>
        </div>

        <div class="club-detail__swiper swiper d-md-none mb-4">
          <div class="swiper-wrapper">
            <a class="swiper-slide" href="{{ file_url($club, 'club_photo', 'large') }}"
               data-fancybox="gallery_{{$club->id}}">
              <img class="swiper-lazy" data-src="{{ file_url($club, 'club_photo', 'medium') }}"
                   alt="{{$club->title}}"
                   title="{{$club->title}}"/>
              <span class="swiper-lazy-preloader swiper-lazy-preloader-black"></span>
            </a>
            @foreach($club->gallery as $key => $image)
              <a class="swiper-slide" href="{{ file_url($image, 'name', 'large') }}"
                 data-fancybox="gallery_{{$image->id}}">
                <img class="swiper-lazy" data-src="{{ file_url($image, 'name', 'medium') }}"
                     alt="{{$club->title}} {{ $key + 1 }}"
                     title="{{$club->title}}"/>
                <span class="swiper-lazy-preloader swiper-lazy-preloader-black"></span>
              </a>
            @endforeach
          </div>
          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
          @if($club->youtube)
            <a data-fancybox
               href="{{ str_replace("/embed/","/watch?v=",$club->youtube) }}" class="club-photos__youtube"
               data-lightbox="iframe" itemprop="url">Watch video <img
                src="{{ asset('assets/images/youtube.svg') }}" style="margin-left:3px;" alt="Youtube"
                width="18" height="18"></a>
          @endif
        </div>

        @if($club->club_overview)

          <x-header class="my-4">
            About <span class="font-mr text-capitalize">Club</span>
          </x-header>
        @endif

        <div class="info">{!! $club->club_overview !!}</div>

        <x-header subtitle="What our members love:" class="my-4">
          @if(!$club->club_overview)
            About <span class="font-mr">Club</span>
          @endif
        </x-header>
        <div class="info">{!! $club->what_members_love !!}</div>

        <hr class="border-dark border-opacity-50 my-5"/>

        <x-header>
          Unlimited <span class="font-mr text-capitalize">Access</span>
        </x-header>
        <div class="info">{!! $club->description !!}</div>
      </div>
    </section>

    <div class="container pt-5">

      <x-header class="text-center">
        You might <span class="font-mr text-lowercase">also</span> Like
      </x-header>
      <div class="row">
        @foreach ($clubs as $item)
          <div class="col-12 col-md-6 col-lg-4">
            @include('club.club-list-item', ['club' => $item])
          </div>
        @endforeach
      </div>
    </div>

  @endif
@endsection
