<section class="slogan">

  <span class="overlay-bg"></span>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-sm-12">
        @if($showAdv ?? false)
          <p class="mt-3 mb-4 fw-bold text-uppercase">adv+ membership</p>
        @endif
        <h1 class="fw-bold">{{ $slogan }}</h1>
        <div class="my-4 fs-3 fw-normal">{!! $smallSlogan !!}</div>
        <a
          href="{{ $theme->joinLink }}"
          class="btn btn-warning rounded rounded-pill text-white text-align-center w-100 pt-3 pb-0 scrollToBottom"
        >
          {{ $buttonText ?? 'Join Today' }}
        </a>
      </div>

      @if($showBanner ?? false)
        <div class="col-md-6 col-sm-12 d-flex align-items-center justify-content-center">
          <a href="https://whatson.ae/2022/09/membership-programs-in-the-uae-you-need-to-be-signed-up-for/"
             class="text-decoration-none w-100" target="_blank">
            <div class="whats-on-banner p-4 w-100 overflow-hidden position-relative whatson-col">
              <div class="banner-overlay w-100 h-100 position-absolute"></div>
              <div class="banner-content d-flex justify-content-between flex-column w-100 h-100 position-relative">
                <div>
                  <div class="card-text">
                    One of the best membership <br />programs according to
                  </div>
                  <img src="{{asset('assets/images/wo-dubai.png')}}" class="whatson-logo img-fluid mt-3"
                       alt="whats on"/>
                </div>
                <div class="mt-5">
                  <svg
                    width="28"
                    height="28"
                    viewBox="0 0 28 28"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M12 9L17 14L12 19"
                      stroke="white"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <circle
                      cx="14"
                      cy="14"
                      r="13"
                      stroke="white"
                      stroke-width="2"
                    />
                  </svg>
                </div>
              </div>
            </div>
          </a>
        </div>
      @endif
    </div>
  </div>
</section>
