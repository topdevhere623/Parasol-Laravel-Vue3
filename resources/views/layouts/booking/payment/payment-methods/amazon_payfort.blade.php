<div id="amazon_payfort_payment_block" class="amazonBlock" data-booking="{{ $booking->uuid }}">
  <div class="d-flex-center">
    <div class="amazon-payfort" id="amazonPayfortSuccess" style="display:none;">
      <h3>
        <img class="text-uppercase" height="44" width="auto" alt="salut"
             src="{{ asset('assets/images/salut_blue.svg') }}">
        Congratulations!</h3>
      <p>Your payment is processed.</p>
      <p>
        Please read & agree to the adv+ T&Cs and <strong>CHECKOUT</strong> below to complete the payment and purchase.
      </p>
      <img width="50px" height="50px" class="download" src="{{ asset('assets/images/congratulation-icon.svg') }}">
    </div>
  </div>
</div>
