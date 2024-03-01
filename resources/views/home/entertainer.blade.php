@extends('layouts.main')
@include('partials.theme-variables')

@section('content')

  @include('partials.slogan', [
    'slogan' => 'THE LIFE YOU LOVE. LIVE IT.',
    'smallSlogan' => 'Enjoy an unlimited 5-star lifestyle at some of the finest places across the city',
    'buttonText' => 'Get it now!',
  ])

  <div class="container">

    @include('partials.partners')

    <div class="reasons">
      <x-header class="text-center">
        a wealth of reasons to&nbsp;<span class="font-mr text-capitalize">Become</span>&nbsp;a member
      </x-header>
      <div class="reasons-list d-flex justify-content-center align-items-center">
        <div class="d-flex flex-column justify-content-center align-items-center">
          <img src="{{ asset('assets/images/entertainer/reasons_img_1.png') }}"
               alt="Hotel Beaches & Pools"/>
          <h3 class="text-center">Hotel Beaches & Pools</h3>
          <p class="text-middle-grey">Treat your family to exclusive offers in hotel beaches and pools</p>
        </div>
        <div class="d-flex flex-column justify-content-center align-items-center">
          <img
            src="{{ asset('assets/images/entertainer/reasons_img_2.png') }}"
            alt="Premium Fitness Venues"
          />
          <h3 class="text-center">Premium Fitness Venues</h3>
          <p class="text-middle-grey">Get healthier with an array of state-of-the-art facilities</p>
        </div>
        <div class="d-flex flex-column justify-content-center align-items-center">
          <img src="{{ asset('assets/images/entertainer/reasons_img_3.png') }}"
               alt="Get healthier with an array of state-of-the-art facilities"/>
          <h3 class="text-center">Dining & SPA</h3>
          <p class="text-middle-grey">
            Enjoy up to 40% discount across restaurants, spas and
            experiences
          </p>
        </div>
      </div>
    </div>

  </div>

  @include('partials.join-today-block', [
    'text' => 'Interested in membership?',
    'buttonText' => 'Get instant access',
  ])

  <div class="clubs">
    <div class="small">
      <x-header class="text-center">
        Clubs&nbsp;<span class="font-mr text-capitalize">Available</span> For&nbsp;You
      </x-header>

      @include('partials.lazy-clubs-list')

      <a
        href="{{ $theme->joinLink }}"
        class="btn btn-warning rounded rounded-pill text-white text-align-center d-block mx-auto mt-sm-0 mt-md-5 mb-2 scrollToBottom"
      >
        Join today
      </a>
    </div>

    <div class="large container clubs-slider">
      <x-header class="text-center" subtitle="Clubs are available across Dubai, Abu Dhabi, Al Ain, Sharjah and
        Ajman">
        Clubs&nbsp;<span class="font-mr text-capitalize">Available</span>&nbsp;For You
      </x-header>
      @include('partials.clubs-slider')
    </div>
  </div>

  @include('partials.clubs-request-block')

  @include('partials.corporate-pricing-row')

  @include('partials.easy-steps-block')

  @include('partials.testimonials', [
    'title' => 'suitably impressed',
    'subtitle' => 'ENTERTAINER soleil shines for our discerning members',
  ])

  <div id="sn-5ea9560e-1ab5-4b72-8234-270c3bc4c6f5"></div>

  @push('scripts')
    <script src="{{ mix('assets/js/lazy-clubs-list.js') }}"></script>
  @endpush
@endsection
