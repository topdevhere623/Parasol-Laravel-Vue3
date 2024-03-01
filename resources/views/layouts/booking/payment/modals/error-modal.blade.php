<div class="modal-message" id="errorModal" style="display: none"
     data-show="{{ session('booking_payment_error') ? 'true' : 'false' }}">
  <div class="modal-content error">
    <img src="{{ asset('assets/images/sad.svg') }}" alt="Card failed">
    <h4>Ooops!</h4>
    <div id="errorModalText">
      @if(session('booking_payment_error'))
        @includeFirst([
            'layouts.booking.payment.modals.error-code-'.session('booking_payment_error'),
            'layouts.booking.payment.modals.error-default',
        ])
      @endif
    </div>
    <p>&nbsp;</p>
    <p>Need help?<br>Please contact us on the number on the back of your card.</p>
  </div>
</div>
