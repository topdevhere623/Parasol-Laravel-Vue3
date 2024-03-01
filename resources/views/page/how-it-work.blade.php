@extends('layouts.main')

@section('content')

  <section class="how-it-work bg-white mt-5 pt-5">
    <div class="container">
      <div class="d-flex justify-content-between">
        <div class="content flex-column">
          <h3 class="title text-blue text-uppercase">
            How it works
          </h3>
          <div class="subtitle fw-semibold">
            Shop and make your purchase.
          </div>
          <ul class="fs-5 lh-lg">
            <li>Select the membership plan you want to purchase</li>
            <li>Complete your payment.</li>
            <li>After you have made your payment contact your bank to check eligibility.</li>
            <li>Terms and Conditions will apply.</li>
            <li>Orders over AED 1000 may qualify for 0% credit card installments footnote&nbsp;*</li>
          </ul>
          <div class="block">
            <img class="block-image" src="{{ asset('assets/images/emirates_nbd.png') }}"
                 alt="Emirates NBD"/>
            <div class="block-link">
              <a href="www.emiratesnbd.com/en/help-and-support/installment-payment-plan">
                www.emiratesnbd.com/en/help-and-support/installment-payment-plan
              </a>
            </div>
          </div>
          <div class="small-text">
            Prices are inclusive of VAT (5%) but exclusive of delivery charges unless otherwise indicated. The order
            form shows you the VAT payable on the products you select.
          </div>
        </div>
        <img class="image d-none d-lg-block" src="{{ asset('assets/images/image-card.png') }}"
             alt="image card"/>
      </div>
    </div>
    <div class="added-footer">
      <div class="container d-flex">
        <img class="added-footer-img" src="{{ asset('assets/images/important.png') }}" alt="important">
        <div class="added-footer-text">
          Important: Installment financing is provided directly by participating banks only. 0% rate subject to bank
          availability. After you make your purchase on the Advantage Plus website, contact your bank to see what
          options
          may be available to you. Bank Terms and Conditions apply. (the link loops back to the same page)
        </div>
      </div>
    </div>
  </section>

@endsection
