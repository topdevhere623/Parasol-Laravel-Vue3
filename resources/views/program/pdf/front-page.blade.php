@extends('layouts.pdf')

@section('content')

  <div class="main-container">
    <div class="header">
      @include('program.pdf.program-logo')
    </div>

    <div class="main">
      <div class="row-1">
        <div class="text-size-huge text-uppercase text-center text-blue" style="padding: {{ 4 * $ratio }}px 0">
          <span class="text-bold">A WEALTH OF REASONS TO</span>&nbsp;
          <span class="script">Become</span>&nbsp;
          <span class="text-bold">A MEMBER</span>
        </div>
        <div class="reasons">
          <div class="float-left">
            <img src="{{ asset('assets/images/program/front_page_img_1.png') }}"
                 alt="Hotel Beaches & Pools"/>
            <h3 class="text-center">Hotel Beaches & Pools</h3>
            <p class="text-middle-grey">Treat your family to exclusive offers in Hotel beaches and pools</p>
          </div>
          <div class="float-left" style="margin-left: {{ 16 * $ratio }}px">
            <img
              src="{{ asset('assets/images/program/front_page_img_2.png') }}"
              alt="Premium Fitness Venues"
            />
            <h3 class="text-center">Premium Fitness Venues</h3>
            <p class="text-middle-grey">Get healthier with an array of state-of-the-art facilities</p>
          </div>
          <div class="float-left" style="margin-left: {{ 16 * $ratio }}px">
            <img src="{{ asset('assets/images/program/front_page_img_3.png') }}"
                 alt="Get healthier with an array of state-of-the-art facilities"/>
            <h3 class="text-center">Dining & SPA</h3>
            <p class="text-middle-grey">
              Enjoy up to 40% discount across restaurants, spas and
              experiences
            </p>
          </div>
        </div>
        <div class="clear"></div>
        @include('program.pdf.join-today')
      </div>
      <div class="clear"></div>

      <div class="row-2">
        <h1 class="text-size-huge text-uppercase text-blue text-center" style="margin-top: {{ 5.5 * $ratio }}px">
          Membership information and plans available to you
        </h1>
        <div>
          <div class="col-1 float-left">
            <p class="package script text-light-grey">Package</p>
            <h2 style="margin-top: {{ 3 * $ratio }}px" class="text-uppercase text-blue">
              {{ $package->title }}
            </h2>
            <p style="margin-top: {{ 8 * $ratio }}px" class="text-light-grey text-size-tiny">
              Starts from
            </p>
            <p style="margin-bottom: {{ 5.5 * $ratio }}px"
               class="text-size-middle">{!! $package->price_description !!}</p>
            <p style="margin-bottom: {{ 8 * $ratio }}px" class="text-size-middle line-height-normal">
              With the Exclusive {{ $programName }} membership plans you get:
            </p>
            <div class="text-size-tiny line-height-tight">
              {!! $package->description !!}
            </div>
          </div>
          <div class="col-2 float-left">
            <h4 class="text-blue text-uppercase">Membership type</h4>
            <div>
              <div class="float-left back-orange circle">
                <img style="width: {{ 8 * $ratio }}px"
                     src="{{ asset('assets/images/program/mark.png') }}"/>
              </div>
              <ul class="float-right text-size-middle line-height-normal" style="margin-top: {{ $ratio / 2 }}px">
                @foreach($plans as $plan)
                  <li>{{ $plan->title }}</li>
                @endforeach
              </ul>
            </div>
          </div>
          <div class="clear"></div>
        </div>
        @include('program.pdf.join-today')
      </div>
      <div class="clear"></div>
    </div>

    <div class="footer clear">
      <div class="text-uppercase text-size-big text-center text-white">
        View club selection &nbsp;<img src="{{ asset('assets/images/program/v.png') }}"/>
      </div>
    </div>
  </div>

@endsection

@include('program.pdf.styles')

<style>
  .main-container {
    background: url("{{ asset("assets/images/program/header_back.png") }}") no-repeat;
    background-size: contain;
  }

  .header {
    width: 100%;
    height: {{ 212 * $ratio }}px;
  }

  h2 {
    font-size: {{ 11 * $ratio }}px;
    line-height: {{ 7 * $ratio }}px;
  }

  h3 {
    font-size: {{ 12 * $ratio }}px;
    color: #1E262E;
    font-weight: 390;
  }

  h4 {
    font-size: {{ 7 * $ratio }}px;
    letter-spacing: {{ 1 * $ratio }}px;
  }

  ul li {
    padding-bottom: {{ 10 * $ratio }}px;
  }

  .row-1 {
    padding-top: {{ 5 * $ratio }}px;
  }

  .row-1 .reasons {
    padding-top: {{ 4 * $ratio }}px;
  }

  .row-1 .reasons div {
    width: {{ 185 * $ratio }}px;
  }

  .row-1 .reasons p {
    font-size: {{ 10 * $ratio }}px;
    line-height: {{ 8 * $ratio }}px;
    width: {{ 130 * $ratio }}px;
    margin: 0 auto;
    text-align: center;
  }

  .row-1 img {
    border-radius: {{ 15 * $ratio }}px;
    width: {{ 172 * $ratio }}px;
    margin-bottom: {{ 8 * $ratio }}px;
  }

  .row-2 {
    margin-bottom: {{ 11 * $ratio }}px;
  }

  .row-2 > div {
    margin-top: {{ 5 * $ratio }}px
  }

  .row-2 .col-1 {
    width: {{ 225 * $ratio }}px;
    margin-left: {{ 24 * $ratio }}px;
  }

  .row-2 .col-1 ol {
    line-height: {{ 6 * $ratio }}px;
    padding-left: {{ 10 * $ratio }}px;
  }

  .row-2 .col-2 {
    width: {{ 264 * $ratio }}px;
    margin-left: {{ 53 * $ratio }}px;
    margin-top: {{ 13 * $ratio }}px
  }

  .row-2 .col-2 > div {
    margin-top: {{ 6 * $ratio }}px
  }

  .row-2 .circle {
    width: {{ 7 * $ratio }}px;
    height: {{ 7 * $ratio }}px;
    padding: {{ 4 * $ratio }}px {{ 5 * $ratio }}px {{ 4 * $ratio }}px {{ 4 * $ratio }}px;
    border-radius: 50%;
  }

  .row-2 ul {
    list-style: none;
    margin-top: -{{ 3 * $ratio }}px;
    width: 90%;
  }

  .row-1 .join,
  .row-2 .join {
    margin: {{ 10 * $ratio }}px auto;
  }

  .package {
    font-size: {{ 12 * $ratio }}px;
    line-height: {{ 13 * $ratio }}px;
  }

  .script {
    font-family: 'Mighty River', serif;
  }

  .footer img {
    width: {{ 10 * $ratio }}px;
  }
</style>


