@if(count($clubs) > 0)

  <div class="my-5">
    <div class="clubs-slider-swiper swiper">
      <div class="swiper-wrapper">
        @foreach($clubs as $key => $club)
          <a class="swiper-slide d-flex justify-content-between flex-column"
             href="{{ $club->slug ? route('website.clubs.show', $club->slug) : route('website.clubs.index') }}"
             title="{{$club->title}}">
            <img class="swiper-lazy" data-src="{{ file_url($club, 'home_photo', 'medium') }}"
                 alt="{{$club->title}}"/>
            <span class="d-block text-black fs-4 text-truncate mt-3">{{$club->title}}</span>
            <span class="swiper-lazy-preloader swiper-lazy-preloader-black"></span>
          </a>
        @endforeach
      </div>
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div>
  </div>

  <div class="w-100 d-flex flex-column flex-md-row justify-content-center mt-5">
    @if($map)
      <a href="{{ $map }}" data-fancybox title="UAE map"
         class="btn btn-outline-info mb-2 mb-md-0 btn-sm mx-2">Show on
        map</a>
    @endif
    <a href="{{ route('website.clubs.index') }}" class="btn btn-info btn-sm mx-2">View all clubs</a>
  </div>

@endif
