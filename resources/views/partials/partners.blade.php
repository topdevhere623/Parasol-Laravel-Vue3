@if(count($partners) > 0)
  <div class="lifestyle__our-partners our-partners-swiper swiper">
    <div class="swiper-wrapper">
      @foreach($partners as $partner)
        <a class="swiper-slide" @if($partner->url) href="{{ $partner->url }}" @else role="button" @endif>
          <img
            class="lazy"
            src="{{ file_url($partner, 'logo', 'large') }}"
            title="{{ $partner->title }}"
            alt="{{ $partner->title }}"
          />
        </a>
      @endforeach
    </div>
  </div>
@endif
