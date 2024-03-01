@extends('layouts.booking.booking')

@section('content')
  <section class="booking-page complete pb-0 min-vh-75">
    <div class="container-lg py-5 w-md-50">
      <div class="hsbc-complete-bg p-4 rounded-5 ql-content">
        @if($completeMessage)
          {!! $completeMessage !!}
        @elseif($membershipRenewal)
          <h2 class="text-center">Success</h2>
          <div class="text-center">
            <p>
              You have completed <strong>{{ $programName }}</strong> membership renewal. Your card will be
              updated upon
              your current membership expiry.
              If your membership has expired, you can refresh/re-download your membership card from the
              member portal.
            </p>
            <p>Thank you!</p>
          </div>
        @else
          <h2 class="text-center">Success</h2>
          <div class="text-center">
            <p>You have completed <strong>{{ $programName }}</strong> membership purchase.
              You can download your digital card once you set up the password to your member portal
              (family
              membership
              has individual access for each adult).
            </p>
            <p>Our team may call you to verify all your details.
              You can go back to <a class="text-decoration-underline" href="{{ url('/') }}">Home Page</a>
              or send us a
              <a class="text-decoration-underline" target="_blank"
                 href="{{ $theme->whatsAppUrl }}">WhatsApp</a>
            </p>
            <p>Thank you!</p>
          </div>
        @endif
      </div>

    </div>
  </section>


  @production
    @push('scripts')
      <script>
        setTimeout(function () {
          gtag('event', 'purchase', {
            'transaction_id': "{{$gtagData['reference_id']}}",
            'affilation': 'advplus.ae',
            'value': {{$gtagData['total_price']}},
            'currency': "{{$gtagData['currency']}}",
            'tax': {{$gtagData['tax']}},
            'shipping': 0,
            'items': [
              {
                'id': "{{$gtagData['reference_id']}}",
                'name': "{{$gtagData['plan']}}",
                'quantity': 1,
                'price': {{$gtagData['total_price']}},
                'variant': "{{$gtagData['clubs']}}",
              }],
            'coupon': "{{$gtagData['coupon']}}",
          })
        }, 3000)
      </script>
    @endpush
  @endproduction
@endsection
