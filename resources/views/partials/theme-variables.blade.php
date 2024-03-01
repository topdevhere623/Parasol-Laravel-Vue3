@push('styles')
  <style>
    :root {
    {{
      css_prop([
        '--confirm-btn-color' => $theme->booking->confirmButtonColor,
        '--confirm-btn-color-hover' => $theme->booking->confirmButtonColor ? adjust_brightness($theme->booking->confirmButtonColor, -0.2) : null,
        '--header-bg-first' => $theme->headerBgFirst ?? $theme->booking->firstMainColor,
        '--header-bg-second' => $theme->headerBgSecond ?? $theme->booking->secondMainColor,
        '--footer-bg-first' => $theme->footerBgFirst ?? $theme->booking->secondMainColor,
        '--footer-bg-second' => $theme->footerBgSecond ?? $theme->booking->firstMainColor,
        '--slogan-background-image' => $theme->sloganBackgroundImage ? 'url(' . $theme->sloganBackgroundImage . ')' : null,
        '--slogan-background-image-mobile' => $theme->sloganBackgroundImageMobile ? 'url(' . $theme->sloganBackgroundImageMobile . ')' : null,
        '--warning-color' => $theme->warningColor,
        '--user-info-bg-first' => $theme->booking->firstMainColor,
        '--user-info-bg-second' => $theme->booking->secondMainColor,
        '--checkbox-bg' => $theme->booking->secondMainColor,
        '--coupon-btn-color' => $theme->booking->couponButtonColor,
        '--headers-color' => $theme->booking->headersColor,
        '--headers-color-second' => $theme->booking->secondHeadersColor,
        '--club-filter-city-color' => $theme->booking->secondHeadersColor,
        '--club-filter-city-color-hover' => $theme->booking->secondHeadersColor ? adjust_brightness($theme->booking->secondHeadersColor, 0.90) : null,
        '--total-price-color' => $theme->booking->totalColor,
        '--light-background' => $theme->booking->secondHeadersColor ? adjust_brightness($theme->booking->secondHeadersColor, 0.90) : null,
        '--club-select-color' => $theme->booking->clubSelectColor,
        '--booking-buttons-color' => $theme->booking->buttonTextColor,
        '--swiper-theme-color' => $theme->headerBgFirst,
      ])
    }}







    }
  </style>
@endpush
