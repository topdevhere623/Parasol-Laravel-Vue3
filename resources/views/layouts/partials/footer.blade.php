<?php
/** @var \App\Services\WebsiteThemeService $theme */

?>
<footer @class(['footer text-white', 'pt-5 mt-5' => !$theme->showPreFooterContacts])>

  <div class="container-lg px-md-4">

    @includeWhen($theme->showFooterContacts, 'layouts.partials.entertainer-contacts')

    <div class="footer-info row justify-content-md-around">
      @if($theme->showFooterDescription)
        <div class="footer-description col-12 col-md-5">
          <h4 class="mb-3">adv+ - Your Membership, Your Way!</h4>
          <p class="pe-md-5 me-md-5">We provide unlimited access to 5-star beach clubs, exclusive hotels, pools and
            fitness venues through our lifestyle memberships. Our members enjoy a deservedly luxurious lifestyle at a
            surprisingly affordable price.
          </p>
        </div>
      @endif

      @if($theme->showFooterNavigation)
        <div class="col-6 col-md-2 d-flex flex-column ">
          <div class="text-uppercase fw-bold mb-3 fs-6">Navigation</div>
          <a href="{{ route('home') }}">Home</a>
          <a href="{{ route('website.clubs.index') }}">Clubs</a>
          <a href="{{ route('home') }}/#join">Pricing</a>
          <a href="{{ \URL::member()  }}">Member login</a>
          <a href="{{ $theme->joinLink }}">Join today</a>
          @if ($showNewsletterModal ?? false)
            <a href="#" id="subscribe-newsletter">Subscribe to a Newsletter</a>
          @endif
        </div>
      @endif

      <div class="useful-information col-6 col-md-3 d-flex flex-column">
        <div class="text-uppercase fw-bold mb-3 fs-6">Useful information</div>
        <a href="{{ $theme->getTermsAndConditionsUrl() }}">Terms and Conditions</a>
        <a href="{{ $theme->getPrivacyPolicyUrl() }}">Privacy Policy</a>
        <a href="{{ $theme->getExclusionPolicyUrl() }}">Exclusion Policy</a>
        <a href="{{ $theme->getFaqUrl() }}">FAQ</a>
        @if($theme->showFooterNavigation && $theme->getAboutUsUrl())
          <a href="{{ $theme->getAboutUsUrl() }}">About Us</a>
        @endif
        @if(!empty($theme->rulesOfUseLink))
          <a href="{{ $theme->rulesOfUseLink }}">Rules of Use</a>
        @endif
      </div>

      <div class="col-12 col-md-2">
        <div class="payments d-flex flex-column mb-3">
          <div class="text-uppercase fw-bold mb-2 fs-6 mt-3 mt-md-0">Payments</div>
          <div class="mb-3">
            <img src="{{ asset('assets/images/mastercard.png') }}"
                 alt="mastercard"
                 width="54"
                 height="32"
                 style="margin-right:10px;"
            />
            <img src="{{ asset('assets/images/visa.png') }}"
                 alt="visa"
                 width="70"
                 height="31"
            />
          </div>
        </div>

        @if($theme->showFooterSocials)
          <div class="social">
            <div class="title">Social links</div>
            <div class="socials d-flex">
              <a class="me-3" href="https://www.facebook.com/advplusae/" target="_blank">
                <img src="{{ asset('assets/images/facebook.svg') }}" alt="facebook">
              </a>
              <a class="me-3" href="https://www.linkedin.com/company/advplusae" target="_blank">
                <img src="{{ asset('assets/images/linkedin.svg') }}" alt="linkedin">
              </a>
              <a href="https://www.instagram.com/advplusae/" target="_blank">
                <img src="{{ asset('assets/images/instagram.svg') }}" alt="instagram">
              </a>
            </div>
          </div>
        @endif

      </div>
    </div>

    <div class="footer__copy">
      {!! $theme->footerCopy ?? '&copy; ' . date('Y') . ' adv+ | All rights reserved' !!}
    </div>
  </div>
</footer>

<a class="wa_btn"
   href="{{ $theme->whatsAppUrl }}"
   target="_blank">
  <img src="{{ asset('assets/images/wa_icon.png') }}" alt="whatsapp" width="80" height="75"/>
</a>
