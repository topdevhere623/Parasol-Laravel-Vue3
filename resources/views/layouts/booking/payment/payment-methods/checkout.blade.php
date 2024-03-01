@once
  <div id="checkout_payment_block" class="checkout-com" style="display:none;">
    <div>
      <div class="visa-card"
           data-public-key="{{ config('services.checkout.public_key') }}"
      >
        <img src="{{ asset('assets/images/visaLogo.png') }}" alt="">
        <div class="visa-input-groups">
          <div class="visa-input">
            <label for="">card number</label>
            <div class="card-number-frame frame-input"></div>
            <img id="checkoutCardLogo"/>
          </div>
          <div class="visa-input">
            <label for="">EXPIRY</label>
            <div class="expiry-date-frame frame-input"></div>
          </div>
          <div class="visa-input">
            <label for="">Holder’s name</label>
            <input type="text" placeholder="Enter holder’s name" id="checkoutCardFullName">
          </div>
          <div class="visa-input">
            <label for="">cVV</label>
            <div class="cvv-frame frame-input"></div>
          </div>
        </div>
      </div>
      <div class="mt-3 ms-2">
        <p>
          To complete the card payment - fill in the card information above.<br><br>
          Please note, the charge will appear in your statement as "Parasol Loyalty Card Services LLC"
        </p>

        <div class="text-danger error-message error-message__card-number"></div>
        <div class="text-danger error-message error-message__expiry-date"></div>
        <div class="text-danger error-message error-message__cvv"></div>
      </div>
    </div>
  </div>
  @push('scripts')
    <script src="https://cdn.checkout.com/js/framesv2.min.js"></script>
  @endpush
@endonce
