<header
  class="navbar navbar-dark navbar-expand-md bg-light container-xl header-menu"
  aria-label="Offcanvas navbar large">
  <div class="container-fluid">
    <a class="navbar-brand d-flex flex-row align-items-center"
       href="{{ $theme->showHeaderMenu ? route('home') : '#' }}">
      <img
        class="d-none d-lg-block"
        height="45px"
        src="{{ $theme->headerLogo }}"
        alt="Advplus"
        title="Advantage Plus Programme  adv+"
      />
      <img
        class="d-block d-lg-none"
        height="32px"
        src="{{ $theme->mobileHeaderLogo }}"
        alt="Advplus"
        title="Advantage Plus Programme  adv+"
      />
    </a>
    @if($theme->showHeaderMenu)
      <button
        class="navbar-toggler border-0 shadow-none px-0"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasHeaderNavbar"
        aria-controls="offcanvasHeaderNavbar"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
    @endif
    <div class="offcanvas offcanvas-end border-0 w-100" tabindex="-1" id="offcanvasHeaderNavbar"
         aria-labelledby="offcanvasHeaderNavbarLabel">
      @if($theme->showHeaderMenu)
        <div class="offcanvas-header border-bottom border-info border-opacity-25">
          <h5 class="offcanvas-title text-uppercase text-center w-100 text-muted fs-6 fw-bold"
              id="offcanvasHeaderNavbarLabel">Main menu</h5>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"
                  aria-label="Close"></button>
        </div>
      @endif
      <div class="offcanvas-body">
        <ul class="navbar-nav">
          @if($theme->showHeaderMenu)
            <li class="nav-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="nav-item"><a href="{{ route('website.clubs.index') }}">Clubs</a></li>
            <li class="nav-item"><a href="{{ route('home') }}/#join">Pricing</a></li>
            <li class="nav-item"><a href="{{ route('blog-posts') }}">Blog</a></li>
          @endif
          @if($theme->showHeaderMemberPortalLink)
            <li class="nav-item">
              <a href="{{ $theme->memberPortalUrl  }}"
                 class="member-login-btn btn btn-outline border border-light border-2 mt-6 mt-md-0 mx-md-3 border-opacity-50 rounded-pill">
                Member login
              </a>
            </li>
          @endif
          @if($theme->showHeaderMenu)
            <li class="nav-item">
              <a
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasHeaderNavbar"
                aria-controls="offcanvasHeaderNavbar"
                href="{{ $theme->joinLink }}"
                class="navbar-toggler shadow-none join-today join-today-mobile btn btn-warning rounded rounded-pill text-white text-align-center d-md-none d-block mt-2 mt-md-0 ms-md-3 scrollToBottom w-100"
              >
                Join today
              </a>
              <a
                href="{{ $theme->joinLink }}"
                class="join-today join-today-desktop btn btn-warning rounded rounded-pill text-white text-align-center d-md-block d-none mt-2 mt-md-0 ms-md-3 scrollToBottom"
              >
                Join today
              </a>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</header>
