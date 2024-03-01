<div class="club-item">
  <div class="block">
    <div class="club-item__swiper swiper">
      <div class="swiper-wrapper">
        <a class="swiper-slide"
           href="{{ file_url($club, 'club_photo', 'large') }}"
           data-fancybox="gallery_{{$club->id}}">
          <img class="lazy" data-src="{{ file_url($club, 'club_photo', 'medium') }}"
               title="{{$club->title}}"/>
          <span class="d-none swiper-lazy-preloader swiper-lazy-preloader-black"></span>
        </a>
        @foreach($club->gallery as $key => $image)
          <a class="swiper-slide"
             href="{{ file_url($image, 'name', 'large') }}"
             data-fancybox="gallery_{{$club->id}}">
            <img class="lazy" data-src="{{ file_url($image, 'name', 'medium') }}" alt="{{ $club->title }} {{ $key }}"/>
            <span class="d-none swiper-lazy-preloader swiper-lazy-preloader-black"></span>
          </a>
        @endforeach
      </div>
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
      @if($club->youtube)
        <a data-fancybox href="{{ str_replace("/embed/", "/watch?v=", $club->youtube) }}"
           class="youtube-watch" data-lightbox="iframe" itemprop="url">
          Watch video
          <img src="{{ asset('assets/images/youtube.svg') }}" alt="Youtube" width="18"
               height="18">
        </a>
      @endif
    </div>

    <div class="inner-pd">
      <div class="inner">
        <div class="tags d-flex flex-wrap mb-1">
          @foreach($club->tags as $tag)
            <a href="{{ route('website.clubs.index', ['tag' => $tag->slug]) }}"
               class="border border-1 rounded-4"
               style="border-color: {{ $tag->color }} !important; color: {{ $tag->color }}"
               onmouseover="this.style.backgroundColor='{{ adjust_brightness($tag->color, 0.9) }}'"
               onmouseout="this.style.backgroundColor='white'"
            >
              {{ $tag->name }}
            </a>
          @endforeach
        </div>

        <a href="{{ $club->slug ? route('website.clubs.show', $club->slug) : route('website.clubs.index') }}"
           class="inner__title">{{$club->title}}</a>
        <a class="inner__address text-truncate" href="{{ $club->gmap_link }}" target="_blank" rel="noopener"
           title="{{ $club->address }}">
          <img style="margin-right: 4px;"
               src="{{ asset('assets/images/pin.png') }}"
               width="12" height="16" alt="pin"/>
          {{$club->address}}
        </a>

        <h3>What our members love:</h3>
        <div class="inner__info">{!! $club->what_members_love !!}</div>
        <div class="inner__footer">
          <a
            href="{{ $club->slug ? route('website.clubs.show', $club->slug) : route('website.clubs.index') }}"
            class="link primary">View benefits</a>
        </div>
      </div>
    </div>
  </div>
</div>
