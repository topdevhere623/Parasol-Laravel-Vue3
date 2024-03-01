@extends('layouts.main')

@section('content')

  @include('partials.slogan', [
      'showAdv' => true,
      'slogan' => 'THE LIFESTYLE MEMBERSHIP YOU DESERVE',
      'smallSlogan' => '— at a price you can afford',
      'showBanner' => true,
  ])

  <div class="container">
    <section class="lifestyle">
      @include('partials.partners')

      <x-header subtitle="with access to select gyms, beaches and leisure clubs" class="px-3 text-center mt-4">
        adv+ is a <span class="font-mr text-capitalize">Lifestyle</span> membership
      </x-header>
      <div class="lifestyle__benefits mt-5">
        <div class="lifestyle__benefits__item">
          <img class="rounded-circle lazy" data-src="{{ asset('assets/images/image_1.jpg') }}" width="105"
               height="105" alt="Lifestyle-1"/>
          <h3 class="fw-normal fs-4 my-3">Only pay for clubs you need</h3>
          <p class="text-muted">Why pay for access to all clubs year-round. Select your favourites: any other
            clubs, pay-as-you-go on the day.</p>
        </div>
        <div class="lifestyle__benefits__item">
          <img class="rounded-circle lazy" data-src="{{ asset('assets/images/image_2.jpg') }}" width="105"
               height="105" alt="Lifestyle-2"/>
          <h3 class="fw-normal fs-3 my-3">Use all discounts & offers</h3>
          <p class="text-muted">As an adv+ member, you get to use any deal at any club—no matter which clubs
            you selected for unlimited access.</p>
        </div>
        <div class="lifestyle__benefits__item">
          <img class="rounded-circle lazy" data-src="{{ asset('assets/images/image_3.jpg') }}" width="105"
               height="105" alt="Lifestyle-3"/>
          <h3 class="fw-normal fs-3 my-3">Referral reward</h3>
          <p class="text-muted">Loving your adv+ membership? Refer your friend to join and receive a reward of your
            choice from our selection.</p>
        </div>
      </div>
    </section>
    <x-header subtitle="Could a range of exclusive adv+ offers on leisure, lifestyle and wellness help you save cash?"
              class="text-center">
      Stop spending <span class="font-mr text-capitalize">More</span> than you need to
    </x-header>

    <div class="membership row text-center">
      <div class="membership__item col-12 col-md-4 py-3">
        <div class="membership__item__img">
          <img class="lazy" data-src="{{ asset('assets/images/for_site_1.jpg') }}" alt="30+ clubs">
          <a class="playVideo" data-fancybox data-width="640" data-height="360"
             href="https://www.youtube.com/watch?v=rBTEkJfep2I"></a>
        </div>
        <h3 class="fw-normal fs-3 my-3">45+ clubs</h3>
        <p class="text-muted">
          You deserve to have quality downtime, whether that’s a workout or chilling at a pool, or having a
          relaxing day out with family—your choice.
        </p>
        <a href="{{ route('website.clubs.index') }}" class="link-arrow">View all clubs</a>
      </div>
      <div class="membership__item col-12 col-md-4 py-3">
        <div class="membership__item__img">
          <img class="lazy" data-src="{{ asset('assets/images/for_site_2.jpg') }}" alt="More than 130 offers">
          <a class="playVideo" data-fancybox data-width="640" data-height="360"
             href="https://www.youtube.com/watch?v=TVkjMXwF5XY"></a>
        </div>
        <h3 class="fw-normal fs-3 my-3">More than 130 offers</h3>
        <p class="text-muted">
          Offers included with your membership range from wellness, F&B, party planning to dental care and more.
          It’s all about lifestyle.
        </p>
        <a href="{{ route('website.clubs.index') }}" class="link-arrow">View all offers</a>
      </div>
      <div class="membership__item col-12 col-md-4 py-3">
        <div class="membership__item__img">
          <img class="lazy" data-src="{{ asset('assets/images/for_site_3.jpg') }}" alt="Corporate wellness">
        </div>
        <h3 class="fw-normal fs-3 my-3">Corporate wellness</h3>
        <p class="text-muted">
          Speak to us about setting up a wellness program to bring your offering on par with
          the largest corporates.
        </p>
        <a data-fancybox data-src="#requestCorporatePricingModal" href="javascript:" class="link-arrow">
          Request corporate pricing
        </a>
      </div>
    </div>
  </div>

  @include('partials.join-today-block', [
      'text' => 'Would you like access to a choice of Gyms, Beach clubs and Leisure venues?',
      'buttonText' => 'Join today',
  ])

  <div class="container clubs-slider mt-5">
    <x-header subtitle="Within 20 minutes drive from home" class="text-center">
      Venues <span class="font-mr text-capitalize">Conveniently</span> located
    </x-header>
    @include('partials.clubs-slider')
  </div>

  @include('partials.clubs-request-block')

  @include('partials.corporate-pricing-row')

  @include('partials.easy-steps-block')

  @include('partials.testimonials', [
      'title' => 'TESTIMONIALS',
      'subtitle' => 'What members say about our adv+ membership',
      'white' => true,
  ])

  @if(count($instagramPhotos) > 0)
    <div class="container p-md-0">
      <div class="instagram-head">
        <a class="title" href="https://www.instagram.com/advplusae/" target="_blank">
          <img class="lazy" data-src="{{ asset('assets/images/instagram.png') }}" alt="instagram"
               width="32" height="32">&nbsp;&nbsp;
          advplusae
        </a>
        <a class="sbi_follow_btn" href="https://www.instagram.com/advplusae/" target="_blank"
           rel="noopener nofollow">
          <svg class="svg-inline--fa" aria-hidden="true" viewBox="0 0 448 512">
            <path fill="currentColor"
                  d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path>
          </svg>
          For more posts take me to Instagram</a>
      </div>
      <div class="instagram-feed swiper">
        <div class="swiper-wrapper">
          @foreach ($instagramPhotos as $photo)
            <a class="swiper-slide" href="{{$photo['url']}}" target="_blank" rel="noopener nofollow">
              <img data-src="{{$photo['thumbnailUrl']}}"
                   class="swiper-lazy"/>
              <span class="swiper-lazy-preloader swiper-lazy-preloader-black"></span>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  @endif

  @php
    $showNewsletterModal = true
  @endphp

  @include('components.newsletter-modal')

@endsection
