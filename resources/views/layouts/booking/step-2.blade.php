@extends('layouts.booking.booking')
@section('content')
  <section class="booking-page second-step bg-white">
    <div class="container-lg pt-2 pt-md-5">
      @includeWhen($theme->booking->showStepsProgress, 'layouts.booking.partials.step-progress', ['step' => 2])
      <!-- second_step -->
      <form id="bookingStepTwoForm"
            action="{{ route('booking.step-2-store', $booking) }}"
            method="post"
      >
        @if($booking->plan->package->mobile_image)
          <img class="mobile d-md-none mt-1 mb-2 rounded-30" alt="package" width="100%"
               src="{{ file_url($booking->plan->package, 'mobile_image', 'large') }}">
        @endif
        <input type="hidden" name="payment_data" id="paymentData">
        <div class="invoice">
          <div class="row invoice__header">
            <div class="col-6 col-md-10">
              <h6 class="ps-3 ps-md-5">PRODUCT</h6>
            </div>
            <div class="col-6 col-md-2 text-end text-md-start pe-4">
              <h6>PRICE</h6>
            </div>
          </div>

          <div class="row py-md-5 bg-white border-bottom">
            <div class="col-12 col-md-2 d-none d-md-flex justify-content-md-center align-items-md-center ps-md-4 pe-0">
              <img class="rounded-1" width="100%"
                   src="{{ file_url($booking->plan->package, 'image', 'large') }}"
                   alt="{{ $booking->plan->package->title }}">
            </div>
            <div class="col-12 col-md-8 px-4">
              @if($booking->plan && $booking->plan->package)
                <h5 class="fs-4 fw-bold text-center text-md-start px-3 mt-2">
                  {{ $booking->plan->package->title }} - {{ $booking->plan->title }}
                </h5>
                @if($booking->clubs->count() < 10)
                  <h6 class="text-uppercase text-muted fs-5 opacity-75 my-4">Selected clubs</h6>
                  <ol class="ps-3">
                    @foreach($booking->clubs as $key => $item)
                      <li>{{ $item->title }}</li>
                    @endforeach
                  </ol>
                @endif
              @endif
            </div>
            <div class="col-2 d-none d-md-flex d-flex-center">
              AED {{ money_formatter($booking->plan_amount) }}
            </div>
          </div>

          <div class="invoice__item row px-3 ps-md-4 pe-md-3 fs-5 border-bottom d-md-none">
            <div class="col-6">Package total</div>
            <div class="col-6 text-end">AED {{ money_formatter($booking->plan_amount) }}</div>
          </div>

          @if($booking->extra_child_amount > 0)
            <div class="invoice__item row px-3 ps-md-4 pe-md-3 fs-5 border-bottom">
              <div class="col-6">Extra child</div>
              <div class="col-6 text-end">AED {{ money_formatter($booking->extra_child_amount) }}</div>
            </div>
          @endif

          @if($booking->extra_junior_amount > 0)
            <div class="invoice__item row px-3 ps-md-4 pe-md-3 fs-5 border-bottom">
              <div class="col-6">Extra junior</div>
              <div class="col-6 text-end">AED {{ money_formatter($booking->extra_junior_amount) }}</div>
            </div>
          @endif

          <div class="invoice__item row px-3 ps-md-4 pe-md-3 fs-5 border-bottom invoice__subtotal">
            <div class="col-6 text-uppercase fw-bold">Subtotal</div>
            <div class="col-6 text-end">AED {{ money_formatter($booking->subtotal_amount) }}</div>
          </div>

          @if($booking->gift_card_amount > 0)
            <div class="invoice__item row px-3 ps-md-4 pe-md-3 fs-5 border-bottom">
              <div class="col-6">{{ $booking->giftCard->invoice_title }} ({{ $booking->gift_card_amount }})</div>
              <div class="col-6 text-end">- AED {{ money_formatter($booking->gift_card_discount_amount) }}</div>
            </div>
          @endif

          @if($booking->coupon_amount > 0 && $booking->coupon)
            <div class="invoice__item row px-3 ps-md-4 pe-md-3 fs-5 border-bottom">
              <div class="col-6">Discount ({{ $booking->coupon->code }})</div>
              <div class="col-6 text-end">AED -{{ money_formatter($booking->coupon_amount) }}</div>
            </div>
          @endif

          <div class="invoice__item row px-3 ps-md-4 pe-md-3 fs-5 border-bottom">
            <div class="col-6">VAT</div>
            <div class="col-6 text-end">AED {{ money_formatter($booking->vat_amount) }}</div>
          </div>

          <div class="invoice__total row px-3 ps-md-4 py-3 pb-md-2 pe-md-3 fw-bold">
            <div class="col-5 col-md-6 text-uppercase fs-5 d-flex align-items-center pe-0 pe-md-3">Total price</div>
            <div class="col-7 col-md-6 fs-2 ps-0 ps-md-3 d-flex align-items-center justify-content-end">
              AED {{ money_formatter($booking->total_price) }}
            </div>
          </div>
        </div>

        <div class="row payment-methods">
          <div class="col-12 col-md-5">
            @if($paymentMethods->isNotEmpty() && $booking->total_price > 0)
              <h6>SELECT PAYMENT METHOD</h6>
              <div>
                <div class="payment-methods__select">
                  @foreach($paymentMethods as $item)
                    <input type="radio" id="{{ $item->code }}"
                           name="payment_method_code"
                           @checked($loop->first)
                           class="paymentMethodInput"
                           value="{{ $item->id }}"
                    >
                    <label for="{{ $item->code }}">
                      <img src="{{ asset("/assets/images/payment-methods/{$item->code}.png") }}" width="24px"
                           alt="{{ $item->code }}">
                      {{ $item->website_title }}
                    </label>
                  @endforeach
                </div>
              </div>
            @endif

            @if($paymentMethods->isEmpty() || $booking->total_price == 0)
              <div class="payment-methods__select">
                <input type="radio"
                       id="no_payment"
                       name="payment_method_code"
                       class="paymentMethodInput"
                       value="{{ $focPaymentId }}"
                       checked
                >
                <label for="no_payment">
                  <img src="{{ asset("/assets/images/payment-methods/foc.png") }}" width="24px"
                       alt="foc">
                  NO PAYMENT</label>
              </div>
            @endif

            <p class="mt-4 fw-light">
              Your personal data will be used to process your order, support your experience throughout
              this website, and for other purposes in our Privacy Policy.
            </p>
          </div>
          <div class="col-12 col-md-7">
            @if($paymentMethods->isNotEmpty() || $booking->total_price > 0)
              @foreach($paymentMethods as $key => $item)
                @includeIf('layouts/booking/payment/payment-methods/'.$item->code, ['paymentMethod' => $item])
              @endforeach
              <div class="payment-methods__loading my-5" style="display:none" id="paymentMethodLoading">
                <svg
                  xmlns="http://www.w3.org/2000/svg" width="50px" height="50px"
                  xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                  viewBox="0 0 100 100" xml:space="preserve">
                    <path fill="#ccc"
                          d="M 98.293 50 C 98.293 23.334 76.666 1.707 50 1.707 C 23.334 1.707 1.707 23.334 1.707 50 M 9.896 50 C 9.896 27.953 27.743 9.896 50 9.896 C 72.257 9.896 90.104 27.953 90.104 50">
                      <animateTransform
                        attributeName="transform"
                        attributeType="XML"
                        type="rotate"
                        dur="1s"
                        from="0 50 50"
                        to="360 50 50"
                        repeatCount="indefinite"/>
                    </path>
                </svg>

              </div>

            @endif
          </div>
        </div>

        @if($monthlyPayment)
          @includeWhen($monthlyPayment, 'layouts/booking/payment/monthly-calculation')
        @endif
        <div class="more-details w-100 pt-2 pt-md-5 pb-3">
          <h6 class="d-flex align-items-center text-uppercase">
            <img class="mb-1 me-1" src="{{ asset('assets/images/info-circle.png') }}" alt="info circle">
            Few more details
          </h6>
          <p class="mb-0 fw-light">
            You can download a digital membership card from your member portal upon completion of payment & all booking
            steps. Each adult/junior member will receive an invitation with a unique member portal access and membership
            ID.
          </p>
          <p class="mb-0 fw-light">
            Check the T&Cs for more details on the club access and all policies.
          </p>
          <p class="mb-0 fw-light">
            The exclusion policy applies to individual clubs under all club access plans. Please read the T&Cs before
            committing to the payment.
          </p>

          <div class="terms-and-conditions mt-3">
            <input type="checkbox" id="terms" required>

            <label for="terms">
              @if($theme->booking->terms_and_conditions)
                {!! $theme->booking->terms_and_conditions !!}
              @else
                I have read and agree to the
                <a href="{{  route('page.show', 'terms-and-conditions') }}" target="_blank">
                  Terms and conditions
                </a>
                and
                <a href="{{ route('page.show', 'exclusion-policy') }}" target="_blank">
                  Exclusion Policy
                </a>
              @endif
            </label>
          </div>

          <div class="row">
            <div class="col-12 col-md-6">
              <a class="btn btn-back text-uppercase w-100 border-1"
                 role="button"
                 href="{{ \URL::website('checkout', ['package' => $booking->plan->package->slug]) }}">
                Back
              </a>
            </div>
            <div class="col-12 col-md-6 pt-3 pt-md-0">
              <button id="bookingStepTwoSubmit" class="btn btn-apply w-100">
                Pay now
                <x-loading-svg/>
              </button>

            </div>
          </div>
        </div>
      </form>
    </div>
  </section>

  @include('layouts.booking.payment.modals.error-modal')

  @push('scripts')
    <script type="text/javascript">
      new BookingStepTwo()
    </script>
  @endpush

@endsection
