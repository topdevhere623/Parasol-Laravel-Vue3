@extends('layouts.pdf')

@section('content')

  <div class="main-container">
    <div class="header">
      <img class="advplus-logo text-right float-left"
           src="{{ asset('assets/images/ADVPlus_logo_RGBgold-2.png') }}"
           alt="logo"/>
      <div class="small-text float-right text-right">
        <p>{{ $companyDetails->title }}</p>
        <p>{!! $companyDetails->address !!}</p>
        <p>{{ $companyDetails->phone }}</p>
        <p>{{ $companyDetails->email }}</p>
      </div>
    </div>
    <div class="clear"></div>
    <h1>Dear {{ $salesQuote->corporate_contact_name }}</h1>
    <p class="subject">We have the pleasure of submitting the following quotation for you:</p>

    @include('sales-quote.table', compact('salesQuote', 'calculated'))

    <div class="footnote">
      <p>*Quotation housekeeping:</p>
      <ul>
        <li>The quote is an estimate based on the package and membership requirements provided by a Company/
          Customer.
        </li>
        <li>The price is indicative and may change should the number of memberships or the package be altered.
        </li>
        <li>Following on the quote approval, we will issue an official invoice.</li>
        <li>To gain access to the memberships, we will require full payment upfront (meaning that no membership
          can
          start without being fully paid in advance).
        </li>
        <li>This proposal is valid for a period of 14 days. The price is not guaranteed. We will issue a new
          quote
          after the expiration term.
        </li>
      </ul>
    </div>
    <div class="footer small-text">
      <p>To accept this offer, please send us a confirmation email and/or Purchase Order.</p>
      <p>Please let us know if you have any questions, and we look forward to hearing from you soon.</p>
      <p>Kind regards,</p>
      <p>adv+ team</p>
    </div>
  </div>

@endsection

<style>
  .main-container {
    width: 496px;
    padding: 42px 55px 40px 56px;
    font-family: 'Brandon Grotesque', sans-serif;
  }

  .header {
    width: 100%;
  }

  .advplus-logo {
    height: 84px;
    float: left;
    margin-left: -8px;
    margin-top: -8px;
  }

  h1 {
    font-size: 20px;
    margin-top: 20px;
  }

  .subject {
    font-style: normal;
    font-weight: 390;
    font-size: 12px;
    line-height: 16px;
  }

  .footnote {
    margin-top: 16px;
    font-size: 7px
  }

  .footnote ul {
    margin-left: 16px;
  }

  .footnote ul li {
    line-height: 6px;
  }

  .footer {
    margin-top: 16px;
  }

  .uppercase {
    text-transform: uppercase;
  }

  .monthly-value {
    margin-top: 15px;
  }

  .membership-package-quotation {
    margin-top: 13px;
  }

  ul {
    margin-left: -24px;
  }

  td {
    font-size: 9px;
    padding: 4px 7px;
    line-height: 10px;
  }

  tr.header td {
    font-size: 12px;
  }

  .float-left {
    float: left;
  }

  .float-right {
    float: right;
  }

  .text-right {
    text-align: right;
  }

  .clear {
    clear: both;
  }

  .small-text {
    font-size: 10px;
    line-height: 10px;
  }

</style>
