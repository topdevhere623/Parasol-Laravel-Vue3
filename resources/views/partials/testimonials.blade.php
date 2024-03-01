@if(count($testimonials) > 0)
  <div class="@if($white ?? false) bg-white @endif testimonials mt-5 px-3 pt-5 pb-4 pt-md-4">
    <div class="container">
      <x-header subtitle="{{ $subtitle }}" sm="true" class="text-center">{{ $title }}</x-header>
      <div class="testimonials-swiper swiper mt-4">
        <div class="swiper-wrapper">
          @foreach($testimonials as $testimonial)
            <div class="testimonials__item swiper-slide h-auto"
                 @if(!($white ?? false)) style="background-color: #FFF;" @endif>
              <img class="rounded-circle lazy" data-src="{{ file_url($testimonial, 'photo', 'small') }}"
                   alt="{{ $testimonial->name }}" width="90px" height="90px">
              <p>{{ $testimonial->review }}</p>
              <h5 class="fw-bold m-0">{{ $testimonial->name }}, {{ $testimonial->city }}</h5>
            </div>
          @endforeach
        </div>
        <div class="swiper-pagination position-relative"></div>
      </div>
    </div>
  </div>
@endif
