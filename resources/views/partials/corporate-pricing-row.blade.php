@if(count($packages) > 0)
  <div class="container-lg packages mt-5" id="join">
    <x-header subtitle="Choose a plan and join us today in 3 easy steps" class="text-center">
      Choose the <span class="font-mr text-capitalize">Right</span> plan for you
    </x-header>
    <div class="plans row px-3 mt-2 mt-md-5">
      @foreach ($packages as $package)
        <div @class([
              'col-md-4 p-2 mb-5',
              'popular' => $package->is_popular
          ])>
          <div class="plans__item text-center bg-white h-100 position-relative">
            @if($package->isCorporateOffer())
              <a
                href="javascript:"
                data-fancybox
                class="plans__item__img lazy"
                data-src="#requestCorporatePricingModal"
                data-bg="{{ file_url($package, 'image', 'medium') }}"
              ></a>
            @else
              <a
                href="{{ $package->getUrl() }}"
                class="plans__item__img lazy"
                data-bg="{{ file_url($package, 'image', 'medium') }}"
              ></a>
            @endif

            <div class="text-blue fs-2 fw-bold text-uppercase lh-sm mt-4 px-lg-5">
              {{$package->title}}
            </div>
            <div class="text-muted fs-5 my-3">
              {{ $package->subtitle }}
            </div>
            <hr class="border-dark border-opacity-50 mt-4"/>

            <div class="text-muted text-uppercase fs-6 fw-bold">
              @if($package->isCorporateOffer())
                Package price
              @else
                Starting from
              @endif
            </div>
            {!! $package->description !!}

            <div class="position-absolute bottom-0 start-50 translate-middle-x w-100">
              <hr class="border-dark border-opacity-50 mb-0"/>
              @if($package->isCorporateOffer())
                <a
                  href="javascript:"
                  data-fancybox
                  data-src="#requestCorporatePricingModal"
                  class="link-arrow text-info py-3"
                >
                  Request this plan
                </a>
              @else
                <a
                  href="{{ $package->getUrl() }}"
                  class="link-arrow text-info py-3"
                  target="_self"
                >
                  Choose this plan
                </a>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <form class="request-corporate-pricing request-form" style="display: none;" id="requestCorporatePricingModal"
        data-request-url="/web-form-request/corporate-pricing">
    <x-header sm="true" class="mt-3 mb-4 text-center">
      Request corporate pricing
    </x-header>

    <div class="request-form__success" style="display:none;">
      <div class="alert alert-success text-center">Awesome! the corporate pricing is on its way
        <img width="16px" class="emoji" alt="💪" src="https://s.w.org/images/core/emoji/13.0.0/svg/1f4aa.svg">
      </div>
    </div>

    <div class="request-form__inputs">
      <div class="form-group">
        <input placeholder="Name" type="text" class=" form-control" name="name" required>
      </div>
      <div class="form-group">
        <input placeholder="Email" type="email" class="form-control" name="email" required>
      </div>
      <div class="form-group">
        <input placeholder="Phone number" data-inputmask="'mask':'(999)999-9999'" type="phone"
               class="form-control" name="phone" required>
      </div>
      <div class="form-group">
        <input placeholder="How many memberships are you looking for?" type="number" class="form-control"
               name="memberships" min="1">
      </div>
      <div class="form-group mb-0">
        <button class="btn btn-warning w-100 mt-3 request-form__inputs__submit" type="submit">
          Complete
          <x-loading-svg/>
        </button>
      </div>
    </div>
  </form>
@endif