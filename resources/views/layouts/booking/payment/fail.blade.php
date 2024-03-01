@extends('layouts.booking.booking')

@section('content')
  <section class="booking-page second-step bg-white">
    <div class="container-lg pt-2 pt-md-5">

      @includeWhen($theme->booking->showStepsProgress, 'layouts.booking.partials.step-progress', ['step' => 2])

      <div class="d-flex-center flex-column text-center my-5 pt-3">
        <img src="{{ asset('/assets/images/x-mark.svg') }}" width="100" height="100">
        <h2 class="fs-1 my-3">Payment failed!</h2>
        <p class="fs-4">
          There could be several different reasons you're seeing a decline error message or your payment won't go
          through.
        </p>
        <ol class="fs-4 text-darkred text-start m-0 mb-5">
          <li>Card details are incorrect</li>
          <li>Your card is expired or out of date</li>
          <li>Insufficient funds</li>
          <li>Your card is blocked</li>
          <li>Exceed limit</li>
        </ol>

        <a href="{{ route('booking.step-2', $booking) }}" class="btn btn-info text-uppercase mb-5">Try again</a>
      </div>
    </div>
  </section>
@endsection
