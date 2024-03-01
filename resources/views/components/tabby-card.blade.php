<div id="{{ $name }}_payment_block" style="display: none" data-is-available="{{ var_export($isAvailable, true) }}">
  <div class="d-flex flex-column align-items-center">
    @if($isAvailable)
      <div class="tabby">
        <div class="tabby-head">
          <h4>Payment schedule:</h4>
        </div>
        <div class="tabby-body size-{{ $paymentsCount }}">
          @for ($i = 1; $i <= $paymentsCount; $i++)
            <div class="tabby-step">
              <div class="price">
                <div class="amount">
                  AED {{ money_formatter(($totalPrice / $paymentsCount)) }}
                </div>
                <span class="line"></span></div>
              <div class="title">
                <div @class(['d-none d-md-block' => $paymentsCount == 6])>{{ $i == 1 ? 'Today' : $formatter->format($i) . ' payment' }}</div>
                @if($paymentsCount == 6)
                  <div class="d-md-none">{{ $i }}</div>
                @endif
              </div>
            </div>
          @endfor
        </div>
        <div class="tabby-footer">
          <h4 class=" text-uppercase">Total: AED {{ money_formatter($totalPrice) }}</h4>
          <div class="desc">Interest free (0%)</div>
        </div>
      </div>

      <div class="tabby__how-to">
        <p class="mt-3 mb-2 ms-4 ps-1 fw-bold">
          PS: if you do not already have tabby account please
        </p>
        <ol class="ms-3 text-black">
          <li>Sign Up</li>
          <li>Verify your email</li>
          <li>Upload Emirates ID before proceeding to payment</li>
        </ol>
      </div>
    @else
      <div class="tabby tabby__error text-dark">
        <div class="tabby-head">
          <h4>Attention!</h4>
        </div>
        <div class="tabby-body desc">
          <p>Your Tabby authentication was unsuccessful. Please return to the previous step and try again with Tabby's
            registered email ID.
            <br>
            For further troubleshooting, please check Tabby's <a target="_blank"
                                                                 href="https://helpcenter.tabby.ai/hc/en-ae">website</a>
            or contact Tabby customer service on <a href="tel:+80082229">800 82229</a> or <a
              href="mailto:help@tabby.ai">help@tabby.ai</a>.
          </p>
        </div>
      </div>
    @endif
  </div>
</div>
