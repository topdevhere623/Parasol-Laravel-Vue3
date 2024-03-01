@extends('layouts.empty')

@section('content')

  @php $bodyClass = 'links' @endphp

  <div class="d-flex flex-column align-items-center mt-5">
    <img
      class="logo d-flex mt-5"
      src="{{ $theme->mobileHeaderLogo }}"
      alt="Advplus"
      title="Advantage Plus Programme adv+"
    />
    <p class="title text-white mt-2">
      ADVPLUS.ae
    </p>
    <div class="link-items d-flex flex-column align-items-center mt-3">
      @foreach($links as $link)
        <a href="{{ $link['url'] }}" class="link-item d-block bg-white py-3 mt-2 w-100">
          {{ $link['title'] }}
        </a>
      @endforeach
    </div>
  </div>

@endsection
