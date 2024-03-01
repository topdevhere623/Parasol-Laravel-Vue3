@extends('layouts.booking.booking')

@section('content')
  <section class="booking-page third-step bg-white">
    <div class="container-lg pt-2 pt-md-5">
      @includeWhen($theme->booking->showStepsProgress, 'layouts.booking.partials.step-progress', ['step' => 3])

      <form id="bookingStepThreeForm" action="{{ route('booking.step-3-store', $booking) }}"
            method="post">
        @csrf
        <div class="d-flex flex-column">
          <x-header class="fs-4 my-3 booking-header">Membership Details</x-header>
          @if($showIsGiftBlock)
            <div class="row w-md-75 mt-3 ">
              <div class="col-12 col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio"
                         @checked($booking['is_gift'] == 0) name="billing[is_gift]"
                         value="0" id="membership_for_me">
                  <label class="form-check-label" for="membership_for_me">
                    This membership is <b>for me</b>
                  </label>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio"
                         @checked($booking['is_gift'] == 1) name="billing[is_gift]"
                         value="1" id="membership_for_gift">
                  <label class="form-check-label" for="membership_for_gift">
                    This membership is <b>a gift</b>
                  </label>
                </div>
              </div>
            </div>
          @endif
          <div class="row w-md-75 mt-3">
            <div class="col-12 mb-3">
              @include('layouts.booking.partials.step-3.photo-upload', ['name' => 'member', 'title' => 'Please upload your photo', 'defaultAvatar' => $member ? file_url($member, 'avatar', 'medium') : null])
            </div>
            <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
              <div class="input-with-label">
                <label for="memberName">First name</label>
                <input type="text" id="memberName" name="member[first_name]" placeholder="First name"
                       class="input_name" value="{{ $presetData['member']['first_name']  ?? $firstName  }}"
                       @readonly($presetData['member']['first_name'])
                       required>
              </div>
            </div>
            <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
              <div class="input-with-label">
                <label for="memberLastName">Last name</label>
                <input type="text" id="memberLastName" class="input_name"
                       value="{{ $presetData['member']['last_name']  ?? $lastName }}"
                       @readonly($presetData['member']['last_name']) name="member[last_name]"
                       placeholder="Enter last name" required>
              </div>
            </div>
            <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
              <div class="input-with-label">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="member[phone]" placeholder="+971"
                       @readonly($presetData['member']['phone']) value="{{ $presetData['member']['phone'] ?? $booking->phone }}"
                       required>
              </div>
            </div>
            <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
              <div class="input-with-label">
                <label for="memberDob">Date of birth</label>
                <input type="text" id="memberDob" name="member[birthday_format]"
                       value="{{ app_date_format($presetData['member']['dob'] ?? '') }}"
                       data-value="{{ isset($presetData['member']['dob']) ? $presetData['member']['dob']->format('Y-m-d') : ''}}"
                       placeholder="Enter your date of birth" required class="input-date-members">
                <input type="hidden" name="member[birthday]"
                       value="{{ isset($presetData['member']['dob']) ? $presetData['member']['dob']->format('Y-m-d') : '' }}">
              </div>
            </div>
          </div>

          <div
            class="col-12 col-md-8 mt-2 mb-1 mt-md-3 mb-md-2 d-flex justify-content-md-center flex-column-reverse flex-md-row">
            <div class="input-with-label w-100 w-md-75">
              <label for="email">Personal email</label>
              <input type="email" placeholder="Enter your personal email" name="member[email]"
                     value="{{ $presetData['member']['email'] ?? $booking->email }}" id="email" required>
            </div>
            <div class="form-check w-50 ms-md-3 d-flex align-items-center">
              <input class="form-check-input" type="radio" name="member[main_email]" value="personal_email"
                     id="personal" @checked(!$member || $member?->main_email == \App\Models\Member\MemberPrimary::MAIN_EMAIL['personal_email'])>
              <label class="form-check-label" for="personal">
                Select as Primary
              </label>
            </div>
          </div>
          <div
            class="col-12 col-md-8 mt-2 mb-1 mt-md-3 mb-md-2 d-flex justify-content-md-center flex-column-reverse flex-md-row">
            <div class="input-with-label w-100 w-md-75">
              <label for="recovery_email">Recovery email</label>
              <input type="email" placeholder="Enter your personal email" name="member[recovery_email]"
                     value="{{ $member?->recovery_email }}"
                     id="recovery_email" required>
            </div>
            <div class="form-check w-50 ms-md-3 d-flex align-items-center">
              <input class="form-check-input" type="radio" name="member[main_email]" value="recovery_email"
                     @checked($member?->main_email == \App\Models\Member\MemberPrimary::MAIN_EMAIL['recovery_email'])
                     id="recovery">
              <label class="form-check-label" for="recovery">
                Select as Primary
              </label>
            </div>
          </div>

          @if($startDate)
            {{--          @if(true)--}}
            <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
              <x-header class="fs-5 text-center text-md-start mt-4 mb-2 booking-header">Start date of your
                membership
              </x-header>
              <div class="form_control">
                <!-- input-with-label -->
                <div class="input-with-label">
                  <label for="memberStartDate">Start date</label>
                  <input type="text" name="member[start_date_format]"
                         value="{{ app_date_format($startDate) }}"
                         data-value="{{ $startDate->format('Y-m-d') }}" id="memberStartDate"
                         required/>
                  <input type="hidden" name="member[start_date]" value="{{ $startDate->format('Y-m-d') }}"/>
                </div>
              </div>
            </div>
          @endif

          @if($booking->plan->is_partner_available)
            <input type="hidden" name="partner[uuid]" value="{{ $member?->partner?->uuid }}"/>
            <hr class="border-dark border-opacity-50 mt-5 mb-4"/>
            <x-header class="fs-5 text-center text-md-start booking-header">Partner’s information</x-header>
            <div class="row">
              <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
                <div class="input-with-label">
                  <label for="partnerFirstName">First name</label>
                  <input type="text" id="partnerFirstName" name="partner[first_name]"
                         placeholder="Enter partner’s first name"
                         value="{{ $presetData['partner']['first_name'] ?? '' }}"
                         class="input_name" required>
                </div>
              </div>
              <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
                <div class="input-with-label">
                  <label for="partnerLastName">Last name</label>
                  <input type="text" id="partnerLastName" name="partner[last_name]"
                         placeholder="Enter partner’s last name"
                         value="{{ $presetData['partner']['last_name'] ?? '' }}"
                         class="input_name" required>
                </div>
              </div>
              <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
                <div class="input-with-label">
                  <label for="partnerEmail">Email address</label>
                  <input type="email" id="partnerEmail" name="partner[email]" placeholder="Enter partner’s email"
                         value="{{ $presetData['partner']['email'] ?? '' }}"
                         required>
                </div>
              </div>
              <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
                <div class="input-with-label">
                  <label for="partnerPhone">Phone number</label>
                  <input type="tel" id="partnerPhone" name="partner[phone]" placeholder="+971"
                         value="{{ $presetData['partner']['phone'] ?? '' }}"
                         required>
                </div>
              </div>
              <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
                <div class="input-with-label">
                  <label for="partnerDob">Date of birth</label>
                  <input type="text" id="partnerDob" name="partner[birthday_format]"
                         value="{{ app_date_format($presetData['partner']['dob'] ?? '') }}"
                         data-value="{{ isset($presetData['partner']['dob']) ? $presetData['partner']['dob']->format('Y-m-d') : ''}}"
                         placeholder="Enter partner’s a date of birth" required class="input-date-members">
                  <input type="hidden" name="partner[birthday]"
                         value="{{ isset($presetData['partner']['dob']) ? $presetData['partner']['dob']->format('Y-m-d') : '' }}">
                </div>
              </div>
              <div class="col-12 mb-3">
                @include('layouts.booking.partials.step-3.photo-upload', ['name' => 'partner', 'title' => 'Please upload partner’s photo', 'defaultAvatar' => $member?->partner ? file_url($member->partner, 'avatar', 'medium') : null])
              </div>
            </div>
          @endif

          @if($booking->number_of_children > 0)
            <hr class="border-dark border-opacity-50 mt-5 mb-4"/>
            <x-header class="fs-5 text-center text-md-start booking-header">Kids information</x-header>
            <div class="row">
              @for ($i = 0; $i < $booking->number_of_children; $i++)
                @isset($member?->kids[$i]?->uuid)
                  <input type="hidden" name="kids[{{$i}}][uuid]" value="{{ $member?->kids[$i]->uuid }}">
                @endisset
                <div class="col-12 col-md-1 mt-2 mb-1 mt-md-3 mb-md-2">
                  <span class="kids-counter">{{$i + 1}}</span>
                </div>
                <div class="col-12 col-md-4 mt-2 mb-1 mt-md-3 mb-md-2">
                  <div class="input-with-label">
                    <label for="kidsFirstName{{$i}}">First name</label>
                    <input type="text" id="kidsFirstName{{$i}}" name="kids[{{$i}}][first_name]"
                           value="{{ isset($member?->kids[$i]?->first_name) ? $member?->kids[$i]->first_name : '' }}"
                           placeholder="Enter kid’s first name"
                           required>
                  </div>
                </div>
                <div class="col-12 col-md-4 mt-2 mb-1 mt-md-3 mb-md-2">
                  <div class="input-with-label">
                    <label for="kidsLastName{{$i}}">Last name</label>
                    <input type="text" id="kidsLastName{{$i}}" name="kids[{{$i}}][last_name]"
                           value="{{ isset($member?->kids[$i]?->last_name) ? $member?->kids[$i]->last_name : '' }}"
                           placeholder="Enter kid’s last name"
                           required>
                  </div>
                </div>
                <div class="col-12 col-md-3 mt-2 mb-1 mt-md-3 mb-md-2">
                  <div class="input-with-label">
                    <label for="kidsDob{{$i}}">Date of birth</label>
                    <input type="text" id="kidsDob{{$i}}" name="kids[{{$i}}][birthday_format]"
                           value="{{ isset($member?->kids[$i]?->dob) ? app_date_format($member?->kids[$i]->dob) : '' }}"
                           placeholder="Enter kid’s a date of birth" required class="input-date-kids">
                    <input type="hidden" name="kids[{{$i}}][birthday]"
                           value="{{ isset($member?->kids[$i]?->dob) ? $member?->kids[$i]->dob->format('Y-m-d') : '' }}">
                  </div>
                </div>
              @endfor
            </div>
          @endif

          @if($booking->number_of_juniors > 0)
            <hr class="border-dark border-opacity-50 mt-5 mb-4"/>
            <x-header class="fs-5 text-center text-md-start booking-header">Junior member’s information</x-header>
            <div class="row">
              @for ($i = 0; $i < $booking->number_of_juniors; $i++)
                @if($member?->juniors?->isNotEmpty() && $member?->juniors[$i]?->uuid)
                  <input type="hidden" name="juniors[{{$i}}][uuid]" value="{{ $member?->juniors[$i]->uuid }}">
                @endisset
                <div class="col-12 col-md-4 mt-2 mb-1 mt-md-3 mb-md-2">
                  <div class="input-with-label">
                    <label for="juniorsFirstName{{$i}}">First name</label>
                    <input type="text" id="juniorsFirstName{{$i}}" name="junior[{{$i}}][first_name]"
                           value="{{ $member?->juniors?->isNotEmpty() && $member?->juniors[$i]?->first_name ? $member?->juniors[$i]->first_name : '' }}"
                           placeholder="Enter junior’s first name"
                           required>
                  </div>
                </div>
                <div class="col-12 col-md-4 mt-2 mb-1 mt-md-3 mb-md-2">
                  <div class="input-with-label">
                    <label for="juniorsLastName{{$i}}">Last name</label>
                    <input type="text" id="juniorsLastName{{$i}}" name="junior[{{$i}}][last_name]"
                           value="{{ $member?->juniors?->isNotEmpty() && $member?->juniors[$i]?->last_name ? $member?->juniors[$i]->last_name : '' }}"
                           placeholder="Enter junior’s last name"
                           required>
                  </div>
                </div>
                <div class="col-12 col-md-4 mt-2 mb-1 mt-md-3 mb-md-2">
                  <div class="input-with-label">
                    <label for="juniorsDob{{$i}}">Date of birth</label>
                    <input type="text" id="juniorsDob{{$i}}" name="junior[{{$i}}][birthday_format]"
                           value="{{ $member?->juniors?->isNotEmpty() && $member?->juniors[$i]?->dob ? $member?->juniors[$i]->dob->format('Y-m-d') : '' }}"
                           placeholder="Enter junior’s a date of birth" required class="input-date-juniors">
                    <input type="hidden" name="junior[{{$i}}][birthday]"
                           value="{{ $member?->juniors?->isNotEmpty() && $member?->juniors[$i]?->dob ? $member?->juniors[$i]->dob->format('Y-m-d') : '' }}">
                  </div>
                </div>
                <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
                  <div class="input-with-label">
                    <label for="juniorsEmail{{$i}}">Email address</label>
                    <input type="email" id="juniorsEmail{{$i}}" name="junior[{{$i}}][email]"
                           value="{{ $member?->juniors?->isNotEmpty() && $member?->juniors[$i]?->email ? $member?->juniors[$i]->email : '' }}"
                           placeholder="Enter junior’s email"
                           required>
                  </div>
                </div>
                <div class="col-12 col-md-6 mt-2 mb-1 mt-md-3 mb-md-2">
                  <div class="input-with-label">
                    <label for="juniorsPhone{{$i}}">Phone number</label>
                    <input type="tel" id="juniorsPhone{{$i}}" name="junior[{{$i}}][phone]"
                           value="{{ $member?->juniors?->isNotEmpty() && $member?->juniors[$i]?->phone ? $member?->juniors[$i]->phone : '' }}"
                           placeholder="+971" required>
                  </div>
                </div>
                <div class="col-12 mb-5 pb-2">
                  @include('layouts.booking.partials.step-3.photo-upload', [
                                  'name' => "juniorsPhoto$i",
                                  'inputName' => "junior[$i]",
                                  'title' => 'Please upload junior’s photo',
                                  'defaultAvatar' => $member?->juniors?->isNotEmpty() && $member?->juniors[$i]?->avatar ? file_url($member?->juniors[$i], 'avatar', 'medium') : null
                                ])
                </div>
              @endfor
            </div>
          @endif

          <x-header class="fs-4 my-3 booking-header">Billing details</x-header>
          <h6 class="w-100 text-muted fs-4 opacity-75 mb-3 text-center text-md-start">Do you require a billing address
            on your invoice?</h6>
          <div class="row w-md-75 mt-3 ">
            <div class="col-12 col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="radio"
                       @checked($billing['is_needed'] == 1) name="billing[is_needed]"
                       value="1" id="billing_is_needed">
                <label class="form-check-label" for="billing_is_needed">
                  Yes
                </label>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="radio"
                       @checked($billing['is_needed'] == 0) name="billing[is_needed]"
                       value="0" id="billing_is_not_needed">
                <label class="form-check-label" for="billing_is_not_needed">
                  No
                </label>
              </div>
            </div>
          </div>
          <div class="row w-md-75 billing-details">
            <div class="col-12 col-md-6 my-2">
              <!-- input-with-label -->
              <div class="input-with-label">
                <label for="first_name">First name</label>
                <input class="billing-required-input" type="text" placeholder="Enter your first name"
                       value="{{ $billing['first_name'] ?? '' }}"
                       name="billing[first_name]" id="first_name" required>
              </div>
            </div>
            <div class="col-12 col-md-6 my-2">
              <!-- input-with-label -->
              <div class="input-with-label">
                <label for="last_name">Last name</label>
                <input class="billing-required-input" type="text" placeholder="Enter your last name"
                       name="billing[last_name]"
                       value="{{ $billing['last_name'] ?? '' }}" id="last_name" required>
              </div>
            </div>
            <div class="col-12 my-2">
              <!-- input-with-label -->
              <div class="input-with-label autocomplete">
                <label for="company_name">Company name</label>
                <input class="billing-required-input" type="text" placeholder="Enter your company name"
                       name="billing[company_name]"
                       value="{{ $billing['company_name'] ?? '' }}" id="company_name" required autocomplete="of">
              </div>
            </div>
            <div class="col-12 col-md-6 my-2">
              <div class="input-with-label">
                <label for="country">Country </label>
                <select class="billing-required-input" name="billing[country]" id="country" required>
                  <option value="">Choose country</option>
                  @foreach($countries as $country)
                    <option value="{{$country->id}}" @if(($billing['country'] ?? 237) == $country->id) selected @endif
                    >{{$country->country_name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-12 col-md-6 my-2">
              <div class="input-with-label">
                <label for="city">City </label>
                <input class="billing-required-input" type="text" placeholder="Enter your city"
                       value="{{ $billing['city'] ?? '' }}"
                       name="billing[city]"
                       id="city" required>
              </div>
            </div>
            <div class="col-12 col-md-6 my-2">
              <div class="input-with-label">
                <label for="state">State / County</label>
                <input type="text" placeholder="Enter your state/country (optional)"
                       value="{{ $billing['state'] ?? '' }}"
                       name="billing[state]" id="state">
              </div>
            </div>
            <div class="col-12 col-md-6 my-2">
              <div class="input-with-label">
                <label for="street">Street address</label>
                <input class="billing-required-input" type="text" placeholder="Enter your street address"
                       name="billing[street]"
                       value="{{ $billing['street'] ?? '' }}" id="street" required>
              </div>
            </div>
          </div>
          <div class="row w-md-75 justify-content-center">
            <div class="col-12">
              <div class="input-with-label">
              <textarea class="mt-2 mb-4" name="billing[member_instruction]"
                        rows="5"
                        placeholder="Please let us know if you have any special instructions or if you have children under the age of 5.&#10;If you have a toddler, please mention name + surname + DOB."></textarea>
              </div>
            </div>
            <div class="col-12 col-md-6 pt-3 pt-md-0">
              <button id="bookingStepThreeSubmit" class="btn btn-apply w-100">
                Finish
                <x-loading-svg/>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </section>

  @push('scripts')
    <script src="{{ mix('assets/js/vendor/autocomplete.js') }}"></script>

    <script type="text/javascript">
      autocomplete(document.getElementById('company_name'), "{{ route('autocomplete-corporate') }}")
    </script>

    <script src="{{ mix('assets/js/vendor/daterangepicker.js') }}"></script>
    <script>
      new BookingStepThree()
    </script>
  @endpush
@endsection
