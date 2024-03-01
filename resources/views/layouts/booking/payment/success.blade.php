@extends('layouts.booking.booking')

@section('content')
  <section class="booking-page second-step bg-white">
    <div class="container-lg pt-2 pt-md-5">

      @includeWhen($theme->booking->showStepsProgress, 'layouts.booking.partials.step-progress', ['step' => 2])

      <div class="d-flex-center flex-column text-center my-5 pt-3">
        <img src="{{ asset('assets/images/check-mark.svg') }}" width="100" height="100"/>
        <h2 class="fs-1 my-3">Payment successfully completed!</h2>
        <p class="fs-4 mb-5">Please wait, after 3 seconds we will redirect you to the next information
          page</p>
        <img src="{{ asset('assets/images/spinner.svg') }}" width="50" height="50"/>
      </div>
    </div>
  </section>

  <script>
    window.setTimeout(function () {
      window.location.href = '{{ $url }}'
    }, 3000)
  </script>
@endsection
