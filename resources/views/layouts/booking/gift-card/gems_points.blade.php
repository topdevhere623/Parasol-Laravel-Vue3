@if($gemsLoyalId)
  <div class="plan-cfg__options__item"
       id="gemsPointsGiftCardBlock"
       data-card-type="{{ $package->activeGiftCard->uuid }}"
       data-card-number="{{ $gemsLoyalId }}"
  >
    <h5>Gems rewards points</h5>
    <div class="mb-2">Points balance: <span id="gemsPointsGiftCardBalance">--</span></div>
    <div class="coupon-input">
      <input type="text" placeholder="How many points do you want to redeem?" id="gemsPointsGiftCardAmountInput"
             value="">
      <button class="btn green text-uppercase" type="button" id="gemsPointsGiftCardApply" disabled>
        Ok
        <x-loading-svg/>
      </button>
    </div>
  </div>

  @push('scripts')
    <script src="{{ mix('assets/js/gems-points.js') }}"></script>
  @endpush
@endif
