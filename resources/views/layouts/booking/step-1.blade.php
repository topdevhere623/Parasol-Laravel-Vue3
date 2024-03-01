@extends('layouts.booking.booking')
@section('content')

  <section class="booking-page first-step">
    <div class="container-lg">
      <form action="{{ route('booking.step-1-store') }}" method="post" id="bookingStepOneForm">
        @if($package)
          @csrf
          @if($gems_uuid)
            <input type="hidden" name="gems_uuid" value="{{ $gems_uuid }}">
          @endif
          @if($renewal_token)
            <input type="hidden" name="renewal_token" value="{{ $renewal_token }}">
          @endif
          <input type="hidden" id="packageId" name="package_id" value="{{ $package->id }}">

          <div class="first-step__banner d-md-flex align-items-md-stretch mx-auto">
            <div class="first-step__banner__img d-none d-md-block"
                 style="background-image: url({{ file_url($package, 'image', 'large') }});"></div>
            @if($package->mobile_image)
              <img class="first-step__banner__img mobile d-md-none mt-1" alt="package"
                   src="{{ file_url($package, 'mobile_image', 'large') }}">
            @endif
            <div class="first-step__banner__info mt-2 mt-md-5 px-md-5">
              <h6 class="font-mr">Package</h6>
              <h5 class="text-uppercase">{{ $package->title }}</h5>

              @if($package->price_description)
                <p>Starts from </p>
                <h3>{{ $package->price_description }}</h3>
              @endif
              @if($theme->booking->showWithPackageYouGet)
                <h4>With the {{ $package->title }} you get:</h4>
              @endif
              {!! $package->description !!}
            </div>
          </div>
        @endif

        <div class="row user-info-form text-white mb-3">
          <div
            class="col-12 col-md-3 pe-0 d-flex flex-column justify-content-center mb-2 mb-md-0 text-center text-md-start">
            <h6>LET’S GET ACQUAINTED </h6>
            {{ $theme->booking->userInfoText}}
          </div>
          <div class="col-12 col-md-3 d-flex flex-column mb-3 mb-md-0">
            <label class="fw-bold" for="bookingName">Name<sup class="text-danger">*</sup> </label>
            <input type="text" name="name" placeholder="Enter your name" required id="bookingName"
                   value="{{ $bookingUserDetails['name'] }}" @readonly($bookingUserDetails['name']) >
          </div>
          <div class="col-12 col-md-3 d-flex flex-column mb-3 mb-md-0">
            <label class="fw-bold" for="bookingEmail">Email address<sup class="text-danger">*</sup></label>
            <input type="email" name="email" value="{{ $bookingUserDetails['email'] }}" placeholder="Enter your email"
                   id="bookingEmail"
                   required>
          </div>
          <div class="col-12 col-md-3 d-flex flex-column mb-3 mb-md-0">
            <label class="fw-bold" for="bookingPhone">Mobile number<sup class="text-danger">*</sup></label>
            <input class="tel-input" type="{{ $package->is_booking_uae_phone ? 'text' : 'tel' }}" name="phone"
                   placeholder="Enter mobile number"
                   id="bookingPhone" required
                   value="{{ $bookingUserDetails['phone'] }}" @readonly($bookingUserDetails['phone']) >
          </div>
        </div>

        @if($errors->any())
          <ul class="alert alert-danger">
            @foreach($errors->all() as $error)
              <li> {{ $error }} </li>
            @endforeach
          </ul>
        @endif
        @includeWhen($theme->booking->showStepsProgress, 'layouts.booking.partials.step-progress', ['step' => 1])
        <!-- first_step -->
        @if(count($plans) > 0 && $selectedPlan)

          <div class="plan-cfg">
            <div class="row pt-4 py-3 rounded-lg-30 justify-content-center">
              <div @class(['col-12 ps-md-3 pe-md-0', 'col-md-6' => $theme->booking->showClubs, 'col-md-8' => !$theme->booking->showClubs])>
                <h6 class="plan-cfg__title mb-3">SELECT A MEMBERSHIP PLAN</h6>
                <div class="plan-cfg__options">
                  <div class="plan-cfg__options__item">
                    <h5>MEMBERSHIP TYPE</h5>
                    @foreach($plans as $plan)
                      <div class="position-relative plan-cfg__options__item__plan d-flex">
                        <div class="form-check">

                          <input
                            class="form-check-input"
                            type="radio"
                            name="plan_id"
                            value="{{ $plan->id }}"
                            id="plan_{{ $plan->id }}"
                            @if($selectedPlan->id == $plan->id)checked @endif
                            data-number-of-allowed-children="{{ $plan->number_of_allowed_children }}"
                            data-number-of-allowed-juniors="{{ $plan->number_of_allowed_juniors }}"
                            data-show-children-block="{{ var_export($plan->show_children_block, true) }}"
                            data-coupon-required="{{ var_export($plan->is_coupon_conditional_purchase, true) }}"
                            data-number-clubs="{{ $plan->booking_clubs_details->number_of_clubs }}"
                            data-fixed-clubs="{{ $plan->booking_clubs_details->fixed_clubs->toJson() }}"
                            data-include-clubs="{{ $plan->booking_clubs_details->available_clubs->toJson() }}"
                            data-tabby-payments-count="@json($plan->tabbyPaymentsCount)"
                          >
                          <label class="form-check-label"
                                 for="plan_{{ $plan->id }}">{{ $plan->title }}</label>
                        </div>
                        @if($plan->question_mark_description)
                          <img src="{{ asset('assets/images/question.svg') }}"
                               width="18"
                               height="18"
                               data-bs-toggle="tooltip"
                               data-bs-title="{{ $plan->question_mark_description }}"
                               alt="tooltip"
                          />
                        @endif
                      </div>
                    @endforeach
                  </div>

                  <!-- plan-cfg__options__item -->
                  <div class="plan-cfg__options__item" id="childrenBlock"
                       @if(!$selectedPlan->show_children_block) style="display:  none ;"@endif>
                    <h5>How many children are you including in the membership?</h5>
                    <p class="text-muted my-3">
                      Select the number of children age 5-15. Children between 16-20 years old are “junior members”.
                    </p>
                    <div class="d-md-flex justify-content-between align-items-center">
                      <div class="form-select-wrap">
                        <select class="form-select"
                                name="number_of_children"
                                data-value="{{ $kids_count }}"
                                id="numberOfChildren">
                          <option value="0">No child</option>
                        </select>
                      </div>
                      <div class="form-select-wrap mt-3 mt-md-0">
                        <select class="form-select"
                                name="number_of_juniors"
                                data-value="{{ $juniors_count }}"
                                id="numberOfJuniors">
                          <option value="0">No junior</option>
                        </select>
                      </div>
                    </div>

                    <div class="form-check mt-4" id="ageConfirmation" style="display: none">
                      <input type="checkbox" name="age_confirmation" id="rule" class="form-check-input">
                      <label class="form-check-label" for="rule">
                        I confirm, that my children are 15 years old or younger and juniors are
                        between 16-20 years old.
                      </label>
                    </div>
                  </div>

                  <!-- Area of residence -->
                  <div class="plan-cfg__options__item" id="areaBlock">
                    <h5>Area you reside in:</h5>
                    <div class="d-md-flex justify-content-between align-items-center">
                      <div class="form-select-wrap">
                        <select name="city" id="city" class="form-select" required>
                          <option value="">Select emirate</option>
                          @foreach($cities as $city)
                            <option @selected($city->id == $city_id) value="{{ $city->id }}">{{ $city->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="form-select-wrap mt-3 mt-md-0">
                        <select name="area_id" id="area" class="form-select" data-selected="{{ $area_id }}" required>
                          <option value="">Select area</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  @if($membershipSources)
                    <!-- plan-cfg__options__item -->
                    <div class="plan-cfg__options__item">
                      <h5>How did you hear about us?</h5>
                      <div class="d-flex justify-content-start align-items-center flex-column flex-md-row">
                        <select class="form-select w-md-100" name="membership_source_id"
                                id="membershipSourceId" required>
                          <option value="" selected>Please choose a option</option>
                          @foreach($membershipSources as $membershipSource)
                            <option
                              value="{{ $membershipSource->id }}">{{ $membershipSource->title }}</option>
                          @endforeach
                          <option data-value="Other" value="">Other</option>
                        </select>
                        <input class="form-control ms-md-2 w-md-50 mt-3 mt-md-0" type="text" style="display: none"
                               name="membership_source_other"
                               data-value="other"
                               placeholder="How did you hear about us?"
                               id="membershipSourceOther">
                      </div>
                    </div>
                  @endif
                  @if($package->activeGiftCard)
                    <input type="hidden" name="gift_card_number" value="" id="giftCardNumber">
                    <input type="hidden" name="gift_card_amount" value="0" id="giftCardAmount">
                    @includeIf('layouts/booking/gift-card/'.$package->activeGiftCard->code)
                  @endif
                  <div @class(["plan-cfg__options__item", 'd-none' => !$theme->booking->showCoupons])>
                    <h5>Have you got a coupon?</h5>
                    <div class="coupon-input">
                      <input type="text" placeholder="Enter a coupon code" id="couponCode" value="{{ $coupon }}">
                      <input type="hidden" name="coupon_code" id="bookingCouponCode">
                      <button class="btn text-uppercase" type="button" id="applyCouponBtn">
                        Redeem
                        <x-loading-svg/>
                      </button>
                    </div>
                  </div>

                  <div class="plan-cfg__options__item plan-cfg__summary_block loading" id="summaryBlock">
                    <x-loading-svg width="40px" height="40px" color="#ccc"/>
                    <div class="plan-cfg__summary_block__item">
                      <span>Package total </span>
                      <span>AED <em id="summaryPackage">0</em> </span>
                    </div>
                    <div class="plan-cfg__summary_block__item" id="summaryChildrenBlock" style="display:none;">
                      <span>Extra child </span>
                      <span>AED <em id="summaryChildren">0.00</em> </span>
                    </div>
                    <div class="plan-cfg__summary_block__item" id="summaryJuniorBlock" style="display:none;">
                      <span>Extra junior </span>
                      <span>AED <em id="summaryJunior">0.00</em> </span>
                    </div>
                    @if($package->activeGiftCard)
                      <div class="plan-cfg__summary_block__item" id="gift_card_summary_block"
                           style="display:none;">
                        <span>{{ $package->activeGiftCard->invoice_title }}</span>
                        <span>
                          AED <em id="gift_card_summary_amount">N/A</em>
                        </span>
                      </div>
                    @endif

                    <div class="plan-cfg__summary_block__item">
                      <span>Discount</span>
                      <span id="summaryCoupon">N/A</span>
                    </div>
                    <div class="plan-cfg__summary_block__item">
                      <span>VAT</span>
                      <span>AED <em id="summaryVat">0</em> </span>
                    </div>
                    <div class="plan-cfg__summary_block__item total">
                      <span>Total</span>
                      <span>AED <em id="summaryTotal">0</em></span>
                    </div>
                    @if($hasTabbyPayment)
                      <div class="mt-4" id="tabby_widget"></div>
                    @endif
                  </div>
                </div>
              </div>
              <div @class(['d-none' => !$theme->booking->showClubs, 'col-12 col-md-6 position-relative mt-4 mt-md-0'])>
                <h6 class="plan-cfg__title mb-3">SELECT THE CLUBS <span> <em
                      id="selectedClubsCount">3</em>/<em
                      id="allowedClubsCount">3</em> clubs are selected</span></h6>

                <div class="plan-cfg__clubs-block">

                  <div class="club-filter sm text-center">
                    <button class="filter-btn active" data-city="all">All</button>
                    @foreach($clubCities as $key => $item)
                      <button class="filter-btn" data-city="{{ $key }}">{{ $item }}</button>
                    @endforeach
                  </div>
                  <div class="plan-cfg__clubs">
                    @foreach($clubs as $club)
                      <div class="plan-cfg__clubs__item club-city {{ $club->city_id }}"
                           id="clubItem{{ $club->id }}"
                           data-id="{{ $club->id }}">
                        <div class="head">
                          <button class="club-select" type="button"
                                  data-id="{{ $club->id }}"></button>
                          @if($club->logo)
                            <span class="mobile-logo"
                                  style="background-image:url({{ file_url($club, 'logo', 'small') }})"></span>
                          @endif
                          <div class="img">
                            @if($club->logo)
                              <span class="logo"
                                    style="background-image:url({{ file_url($club, 'logo', 'small') }})"></span>
                            @endif
                            <img src="{{ file_url($club, 'checkout_photo', 'medium') }}"
                                 alt="{{ $club->title }}">
                          </div>
                          <div class="title">{{ $club->title }}
                            @if($club->gmap_link)
                              <a href="{{ $club->gmap_link }}" target="_blank" rel="noopener">
                                <img src="{{ asset('assets/images/pin.png') }}" width="12" alt="pin"
                                     height="16"/> {{ $club->address }}</a>
                            @endif
                          </div>
                          <a data-parent="accordion_club_{{ $club->id }}" role="button"
                             class="acc-tgg-button">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                 width="24px" height="24px" viewBox="0 0 451.847 451.847"
                                 style="enable-background:new 0 0 451.847 451.847;"
                                 xml:space="preserve">
														<path d="M225.923,354.706c-8.098,0-16.195-3.092-22.369-9.263L9.27,151.157c-12.359-12.359-12.359-32.397,0-44.751
															c12.354-12.354,32.388-12.354,44.748,0l171.905,171.915l171.906-171.909c12.359-12.354,32.391-12.354,44.744,0
															c12.365,12.354,12.365,32.392,0,44.751L248.292,345.449C242.115,351.621,234.018,354.706,225.923,354.706z"/>
													</svg>
                          </a>
                        </div>
                        <div id="accordion_club_{{ $club->id }}" class="club-content">
                          <div class="mob_club_content">
                            <img src="{{ file_url($club, 'checkout_photo', 'medium') }}"
                                 class="img-responsive" alt="{{ $club->title }}">
                            @if($club->gmap_link)
                              <a href="{{ $club->gmap_link }}" target="_blank" rel="noopener"><img
                                  src="{{ asset('assets/images/pin.png') }}" width="12" alt="pin"
                                  height="16"/> {{ $club->address }}</a>
                            @endif
                          </div>
                          <div class="desc">{!!$club->description!!}</div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                  <!-- club IDS -->
                  <input type="hidden" name="clubs" id="selectedClubs" value="">
                </div>
              </div>
            </div>
            <div class="plan-cfg__result row rounded-lg-30 pt-4 py-md-3">
              <div class="col-12 col-md-6 d-flex justify-content-between align-items-center mb-3 mb-md-0">
                <h6>TOTAL PRICE</h6>
                <h5>AED <em id="totalPrice">0.00</em></h5>
              </div>
              <div class="col-12 col-md-6 d-flex align-items-center position-relative">
                <div class="alert alert-danger alertErrorMsg" style="display:none" role="alert"></div>
                <button type="submit" id="bookingStepOneSubmit" class="btn btn-apply fs-5 w-100 text-uppercase">
                  Next
                  <x-loading-svg class="mt-1"/>
                </button>
              </div>
            </div>
          </div>

        @endif
        @if(count($plans) == 0)
          <h2 style="padding-top:30px;text-align:center;font-size: 40px;">Plans not found</h2>
        @endif
      </form>
    </div>
  </section>
  {{--  @push('scripts')--}}
  {{--    <script src="{{ mix('assets/js/vendor/imask.js') }}"></script>--}}
  {{--  @endpush--}}
  @pushif($hasTabbyPayment, 'scripts')
    <script src="https://checkout.tabby.ai/tabby-promo.js"></script>
  @endpushif
  @pushif($package->is_booking_uae_phone, 'scripts')
    <script type="text/javascript">
      $(function () {
        IMask(document.getElementById('bookingPhone'), {
          mask: '+{971} 00 000 00 00',
          placeholder: {
            show: 'always',
          },
        })
      })
    </script>
  @endpushif
  @push('scripts')
    <script type="text/javascript">
      new BookingStepOne()
    </script>
  @endpush

@endsection
