@extends('layouts.main')

@section('content')
  <div class="hsbc-landing">
    <section class="slogan hsbc pb-0">
      <span class="overlay-bg"></span>
      <div class="container position-relative">
        <a href="{{ \URL::member()  }}" class="btn btn-warning btn-member-portal">Member Login</a>
        <img class="slogan__logo mt-4 mb-5" src="{{ asset('assets/images/hsbc/HSBCxSoleil_NEG.svg') }}"
             alt="Soleil" title="Soleil"/>
        <h1 class="fw-bold text-uppercase my-5">The life you love.<br>Live it.</h1>
        <div class="my-5 text-transform-none fs-4 fw-normal">Enjoy an unlimited 5-star lifestyle at some of the finest
          places across the city
        </div>
        <a href="#" class="btn btn-warning my-4 join_today" data-fancybox
           data-src="#hsbcModal">Join Today</a>
      </div>
    </section>
    <div class="container">

      <x-header tag="h2" class="text-hsbc text-center">Join Entertainer soleil and enjoy <span
          class="font-mr text-capitalize">Unlimited</span> access to:
      </x-header>

      <div class="membership row text-center">
        <div class="membership__item col-12 py-3">
          <div class="membership__item__img">
            <img src="{{ asset('assets/images/hsbc/img1.jpg') }}"
                 alt="5-star beach clubs"/>
          </div>
          <h3 class="fw-normal fs-3 mt-3 mb-1">5-star beach clubs</h3>
          <p class="text-muted mb-0">
            Treat your family to exclusive offers in hotel beaches and pools
          </p>
        </div>
        <div class="membership__item col-12 py-3">
          <div class="membership__item__img">
            <img src="{{ asset('assets/images/hsbc/img2.jpg') }}"
                 alt="5-star beach clubs"/>
          </div>
          <h3 class="fw-normal fs-3 mt-3 mb-1">Premium fitness venue</h3>
          <p class="text-muted mb-0">
            Get healthier with an array of state-of-the-art facilities
          </p>
        </div>
        <div class="membership__item col-12 py-3">
          <div class="membership__item__img">
            <img src="{{ asset('assets/images/hsbc/img3.jpg') }}"
                 alt="5-star beach clubs"/>
          </div>
          <h3 class="fw-normal fs-3 mt-3 mb-1">Dining</h3>
          <p class="text-muted mb-0">
            Remarkable two-for-one gastronomical experiences
          </p>
        </div>
        <div class="membership__item col-12 py-3">
          <div class="membership__item__img">
            <img src="{{ asset('assets/images/hsbc/img4.jpg') }}"
                 alt="5-star beach clubs"/>
          </div>
          <h3 class="fw-normal fs-3 mt-3 mb-1">SPA</h3>
          <p class="text-muted mb-0">
            Refresh, rejuvenate and renew in a haven of serenity
          </p>
        </div>
      </div>
    </div>

    <section
      class="bg-warning text-center text-white mt-4 fs-4 px-md-3 py-md-4 m-md-4 p-2 py-4 rounded-md-30 d-flex-center flex-column flex-md-row">
      Great ENTERTAINER soleil deals at the best locations
      <a href="#" class="btn btn-join bg-white text-warning ms-md-3 mt-3 mt-md-0 w-md-auto w-75 join_today"
         data-fancybox
         data-src="#hsbcModal">Join today</a>
    </section>

    <x-header tag="h2" class="text-hsbc text-center px-5 mt-5 mb-4">Clubs that will be available to you</x-header>
    <div class="container px-2">
      <!-- clubs content -->
      @include('partials.lazy-clubs-list')

      <div class="d-flex-center mt-4 mb-2">
        <a href="#" class="btn btn-warning w-100 join_today" data-fancybox
           data-src="#hsbcModal">Join today</a>
      </div>

    </div>
    <footer class="hsbc-footer">
      <div class="row">
        <div class="col-12 mb-5">
          <img class="logo" src="{{ asset('assets/images/hsbc/logos.svg') }}" alt="Logos"/>
        </div>
        <div class="col-6 text-white d-flex flex-column">
          <h5 class="text-uppercase fs-6 mb-3">How it works</h5>
          <a href="https://www.theentertainerme.com/rules-of-use">Rules of Use</a>
          <a href="{{ route('page.hsbc-faq') }}">FAQs</a>
        </div>
        <div class="col-6 text-white d-flex flex-column">
          <h5 class="text-uppercase fs-6 mb-3">Legal</h5>
          <a href="https://www.theentertainerme.com/end-user-license-agreement?v=full">End user license</a>
          <a href="{{ route('page.show', ['slug' => 'hsbc-soleil-terms-and-conditions']) }}">Terms of use</a>
          <a href="https://www.theentertainerme.com/new-terms-of-sale?v=full">Terms of sale</a>
          <a href="https://www.theentertainerme.com/Privacy-Policy?v=full">Privacy policy</a>
        </div>
        <div class="col-12 text-white text-center mt-5 mb-2">
          Copyright &copy; {{date('Y')}} The ENTERTAINER.
        </div>
      </div>
    </footer>

  </div>

  <div class="modal-message" id="hsbcModal" style="display: none">
    <div class="modal-content form">
      <h4 class="text-transform-none mb-4">
        Find out great offers you qualify <br> for with HSBC ENTERTAINER soleil
      </h4>
      <p>Please enter the first 6 digits of your HSBC card</p>

      <form action="{{ url('/api/hsbc-bin/check') }}" method="post" id="check_hsbc_bin">
        <div class="form-group">
          <input id="hsbc_bin_number" placeholder="0000 00" type="text" class="form-control" name="bin"
                 required>
        </div>
        <input type="checkbox" id="is_supplementary" value="true" name="is_supplementary" class="input-checkbox">

        <label class="terms-and-conditions" for="is_supplementary">
          <div class="w-100">Are you a supplementary<br>card holder?</div>
        </label>

        <button type="submit" class="btn bg-white w-100 text-dark mt-0">
          Submit
          <x-loading-svg color="#000"></x-loading-svg>
        </button>
      </form>
    </div>
    <div class="modal-content success success-red" style="display: none">
      <img class="mb-3" src="{{ asset('assets/images/salut.svg') }}" alt="Congrats">
      <h4>CONGRATULATIONS!</h4>
      <p>You qualify for complimentary access to HSBC ENTERTAINER Soleil!</p>
      <p>Continue below to sign-up.</p>
      <a href="#" class="btn bg-white w-100 package-link">Join today</a>
    </div>
    <div class="modal-content success success-green" style="display: none">
      <img class="mb-3" src="{{ asset('assets/images/smile.svg') }}" alt="Smile">
      <p>Good news! You can enjoy 5% off on your annual soleil membership with your HSBC Debit or Credit Card!</p>
      <p>Claim your discount & sign-up for your membership.</p>
      <a href="#" class="btn bg-white w-100 package-link">Join today</a>
    </div>
    <div class="modal-content error" style="display: none">
      <img class="mb-3" src="{{ asset('assets/images/sad.svg') }}" alt="Card failed">
      <h4>Ooops!</h4>
      <p>It seems that you haven’t used your HSBC card to check out.</p>
      <button type="button" class="btn bg-white w-100 join_today">Please, Try again</button>
      <p>&nbsp;</p>
      <p>Need help?<br>Please contact us on the number on the back of your card.</p>
    </div>
  </div>
  </div>

  @push('styles')
    <link rel='stylesheet' href='{{ mix("assets/css/hsbc.css") }}' type='text/css' media='all'/>
  @endpush

  @push('scripts')
    <script src="{{ mix('assets/js/vendor/imask.js') }}"></script>
    <script src="{{ mix('assets/js/hsbc.js') }}"></script>
    <script src="{{ mix('assets/js/lazy-clubs-list.js') }}"></script>
  @endpush
@endsection
