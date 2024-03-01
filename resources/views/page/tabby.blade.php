@extends('layouts.main')
@include('partials.theme-variables')

@section('title')
  Tabby Payment Method for ADVPLUS Programme
@endsection

@section('description')
  Enjoy the best value lifestyle membership now - pay later with the help of Tabby payment method: pay a partial amount today and the rest over equal instalments
@endsection

@section('content')
  <div class="container tabby-page">
    <div class="tabby-page__block-1">
      <div>
        <img src="{{ asset('assets/images/tabby/girl-in-pool.png') }}" alt="Girl in pool">
      </div>
      <a href="{{ route('home') }}/#join" class="d-block">
        <img src="{{ asset('assets/images/tabby/tb.png') }}" alt="tabby" title="tabby">
      </a>
    </div>

    <div class="tabby-page__block-2 what-is">
      <div class="block green">
        <div class="title">
          WHAT IS <img class="logo" src="{{ asset('assets/images/tabby/logo-white.png') }}" alt="tabby logo"/>
        </div>
        <p style="font-size: 34px; font-weight: 420; line-height: 40px;">
          Get more time to pay. With Tabby, you can split your purchases into 3 or 6 interest-free payments.
        </p>
      </div>
      <div class="block">
        <div class="title" style="color: black;">How it works</div>
        <ul>
          <li>Select a membership plan that suits you the most.</li>
          <li>Choose Tabby as your payment method at checkout.</li>
          <li>Sign up with just your email and mobile number (approval is instant!)</li>
          <li>You will receive access to. Member Portal and membership card via email.</li>
          <li>Pay the first installment today and the rest over initial 3 or 6 months, via automatic
            installments.
          </li>
        </ul>
        <div>
          <a
            class="tabby-page__btn btn btn-warning"
            href="https://tabby.ai/en-AE/pay-later"
            target="_blank"
            rel="noopener"
          >
            Learn More
          </a>
        </div>
      </div>
    </div>

    <div class="tabby-page__block-2">
      <div class="block narrow">
        <div class="title">AM I ELIGIBLE?</div>
        <p class="subtitle"><strong>Yes, if you…</strong></p>
        are 18+ years old
        have a valid debit or credit card
        are resident in the United Arab Emirates
        and just FYI…
        <br/>
        Tabby is valid for purchases above AED 200
        Tabby will handle all payments. Your payment installments are automatic, although a small late fee
        applies if you fail to make a payment. The 6 payments option is only available for credit card
        purchases.
        Membership Terms & Conditions apply.
      </div>
      <div class="block dark">
        <div class="title">EXAMPLE...</div>
        <div class="tabby-page__package-info">
          <img style="border-radius: 15px; margin-bottom: 18px"
               alt="Girl with hat"
               src="{{ asset('assets/images/tabby/girl-with-hat.png') }}">
          <div class="tabby-page__package-info__text">
            <h2>Single Annual Membership AED 2,899</h2>
            <p class="small">
              With 6 installments: pay <strong>AED 483.20</strong> today and the rest over 5 equal
              installments.
            </p>
          </div>
        </div>
        <p>
          <img class="logo" alt="tabby logo" src="{{ asset('assets/images/tabby/logo-white.png') }}"/>
          <span class="pay-us">Pay us monthly. no fees. Use any card.</span>
        </p>
      </div>
    </div>

    <div class="tabby-page__block-1 last-of-type">
      <div class="join-today"
           style="background-image: url({{ asset('assets/images/tabby/girl-with-phone.png') }});">
        <div>Digital card in your phone wallet - no app required</div>
        <a
          class="tabby-page__btn btn btn-warning"
          href="{{ route('home') }}/#join"
          target="_blank"
          rel="noopener"
        >Join Today</a>
      </div>
      <div class="clubs">
        <div>Real time club availability at your fingertips</div>
        <div class="clubs-images">
          <img class="club-1" src="{{ asset('assets/images/tabby/club-1.png') }}" alt="club-1" title="club-1">
          <img class="club-2" src="{{ asset('assets/images/tabby/club-2.png') }}" alt="club-2" title="club-2">
          <img class="club-3" src="{{ asset('assets/images/tabby/club-3.png') }}" alt="club-3" title="club-3">
        </div>
        <a
          class="tabby-page__btn btn btn-warning"
          href="{{ route('website.clubs.index') }}"
          target="_blank"
          rel="noopener"
        >View All Clubs</a>
      </div>
    </div>
  </div>
@endsection
