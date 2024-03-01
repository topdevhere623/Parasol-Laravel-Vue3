<div id="monthlyPaymentDescription" class="monthly-payment-description" style="display:none;">
  <div class="row">
    <div class="col-12 col-md-4">
      <h4>Payment schedule</h4>
      <p>1st Payment: AED {{ money_formatter($monthlyPayment->first_charge) }} </p>
      <p>Every payment after :
        AED {{ money_formatter($monthlyPayment->monthly_charge) }}</p>
      <p class="orange">Total: AED {{ money_formatter($booking->total_price) }} </p>
    </div>
    <div class="col-12 col-md-8 info mt-3 mt-md-0">
      The first payment is calculated as a prorated amount until the end of the current
      month plus one whole month. After that, every next payment will be deducted on the
      1st month. <br>
      Should the payment fail for whatsoever reason, you will need to settle the entire
      outstanding amount in one payment to re-activate the access. While we are waiting
      for your payment, you will not be able to access the clubs. Please note we will the
      membership expiry date remains the same.
    </div>
  </div>
</div>
