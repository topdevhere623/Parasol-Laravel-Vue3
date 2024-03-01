@extends('layouts.pdf')

@section('content')
  <div class="main-container">
    @include('program.pdf.program-logo')

    <table
      class="table-header max-width radius-16 text-size-small text-uppercase text-white back-orange">
      <tr class="align-top">
        <td class="col-1"><b>clubs</b></td>
        <td class="col-2"><b>club overview</b></td>
        <td class="col-3"><b>Member access & privileges</b></td>
        <td class="col-4"><b>opening hours</b></td>
        <td class="col-5"><b>guest fee</b></td>
        <td class="col-6" style="width: {{ 23 * $ratio }}px; white-space: nowrap;"><b>check-in</b></td>
      </tr>
    </table>

    <table class="clubs radius-31 back-white text-size-tiny line-height-extra-tight">
      @foreach ($clubs as $key => $club)
        <tr class="club align-top">
          <td class="col-1">
            <img class="logo" src="{{ asset(file_url($club, 'logo', 'large')) }}" alt="club logo"/>
            <div class="text-size-middle text-uppercase">
              <b>{{ $club->title }}</b>
            </div>
            <div class="address">
              {{ $club->address }}
            </div>
          </td>
          <td class="col-2 description">
            {!! fix_html($club->club_overview) !!}
          </td>
          <td class="col-3">
            {!! fix_html($club->description) !!}
          </td>
          <td class="col-4">
            {!! fix_html($club->opening_hours_notes) !!}
          </td>
          <td class="col-5">
            {!! fix_html($club->guest_fees) !!}
          </td>
          <td class="col-6">
            {!! fix_html($club->check_in_area) !!}
          </td>
        </tr>
        @if($key < count($clubs) - 1)
          <tr>
            <td colspan="6">
              <img class="line clear" src="{{ asset("assets/images/program/line.png") }}"/>
            </td>
          </tr>
        @endif
      @endforeach
    </table>
  </div>

  <div class="footer text-white">
    <div class="address float-left">WhatsApp +971 52 129 4354 | +971 4 568 2083 | {{ $email }}
      @if($website)
        | {{ $website }}
      @endif
    </div>
    @include('program.pdf.join-today')
  </div>
@endsection

@include('program.pdf.styles')

<style>
  .main-container {
    background: url("{{ asset("assets/images/program/cover_$backgroundNum.png") }}") no-repeat;
    background-size: contain;
  }

  .table-header {
    padding: {{ 8 * $ratio }}px {{ 9 * $ratio }}px;
    margin-bottom: {{ 6 * $ratio }}px;
    height: {{ 16 * $ratio }}px;
  }

  .table-header td, .club td {
    padding-right: {{ 6 * $ratio }}px;
  }

  .clubs {
    height: {{ 676 * $ratio }}px;
    padding: {{ 6 * $ratio }}px {{ 9 * $ratio }}px;
  }

  .clubs .address {
    margin-top: {{ 9 * $ratio }}px
  }

  .clubs .logo {
    height: {{ 44 * $ratio }}px;
    margin-bottom: {{ 6 * $ratio }}px;
  }

  .line {
    margin: {{ 9 * $ratio }}px 0;
    width: 100%;
  }

  .col-1 {
    width: {{ 82 * $ratio }}px
  }

  .col-2 {
    width: {{ 120 * $ratio }}px
  }

  .col-3 {
    width: {{ 144 * $ratio }}px
  }

  .col-4 {
    width: {{ 72 * $ratio }}px
  }

  .col-5 {
    width: {{ 43 * $ratio }}px
  }

  .col-6 {
    width: {{ 54 * $ratio }}px;
    padding-right: 0;
  }

  .footer {
    margin-top: {{ 2 * $ratio }}px;
  }

</style>


