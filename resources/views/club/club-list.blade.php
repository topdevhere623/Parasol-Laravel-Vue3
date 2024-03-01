@extends('layouts.main')
@include('partials.theme-variables')
@section('content')

  <section class="page-header page-header-clubs-list">
    <div class="container text-center">
      <ul vocab="http://schema.org/" typeof="BreadcrumbList" class="breadcrumbs mt-3">
        <li property="itemListElement" typeof="ListItem">
          <a class="text-white" property="item" typeof="WebPage" href="{{ route('home') }}">Home</a>
        </li>
        <li class="text-white">Clubs</li>
      </ul>
      <h1 class="text-white text-uppercase fw-bolder my-3">Clubs</h1>
    </div>
  </section>

  <div class="container-lg">
    @if(count($clubs) > 0)
      @include('club.club-list-navigation')

      <div class="row clubs-page">
        @foreach ($clubs as $club)
          <div class="col-12 col-md-6 col-lg-4 club-city {{ $club->city_id }}">
            @include('club.club-list-item', ['club' => $club])
          </div>
        @endforeach
      </div>
    @endif
  </div>

  @include('partials.clubs-request-block')

@endsection
