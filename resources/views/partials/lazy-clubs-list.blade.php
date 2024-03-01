<div class="lazy-clubs-list">
  @foreach($clubs as $club)
    <div class="lazy-clubs-list__item" style="display: none">
            <span class="club_photo"
                  style="background-image:url({{ file_url($club, 'checkout_photo', 'medium') }})"></span>
      <div class="head">
        @if($club->logo)
          <span class="mobile-logo"
                style="background-image:url({{ file_url($club, 'logo', 'small') }})"></span>
        @endif
        <div class="title">{{ $club->title }}</div>
        <a data-parent="accordion_club_{{ $club->id }}" role="button" class="acc-tgg-button">
          <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
               x="0px" y="0px" width="24px" height="24px"
               viewBox="0 0 451.847 451.847" style="enable-background:new 0 0 451.847 451.847;"
               xml:space="preserve">
            <path
              d="M225.923,354.706c-8.098,0-16.195-3.092-22.369-9.263L9.27,151.157c-12.359-12.359-12.359-32.397,0-44.751 c12.354-12.354,32.388-12.354,44.748,0l171.905,171.915l171.906-171.909c12.359-12.354,32.391-12.354,44.744,0 c12.365,12.354,12.365,32.392,0,44.751L248.292,345.449C242.115,351.621,234.018,354.706,225.923,354.706z"/>
          </svg>
        </a>
      </div>
      <div id="accordion_club_{{ $club->id }}" class="club-content">
        <div class="mob_club_content">
          @if($club->gmap_link)
            <a href="{{ $club->gmap_link }}" target="_blank" rel="noopener">
              <img
                src="{{ asset('assets/images/pin.png') }}"
                width="12"
                height="16"
              />
              {{ $club->address }}
            </a>
          @endif
        </div>
        <div class="desc">{!!$club->description!!}</div>
      </div>
    </div>
  @endforeach
  <a href="#"
     id="loadMore"
     class="load-btn"
  >show more clubs</a>
  <a href="#"
     id="loadLess"
     class="load-btn load-less"
  >less clubs</a>
</div>