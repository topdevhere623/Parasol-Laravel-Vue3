@if ($paginator->hasPages())
  <nav class="d-flex justify-items-center justify-content-center">

    <ul class="pagination">
      {{-- Previous Page Link --}}
      @if (!$paginator->onFirstPage())
        <li class="page-item">
          <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
             aria-label="@lang('pagination.previous')">
            <img class="arrow" src="{{ asset('assets/images/blog/left-arrow.svg') }}" alt="right arrow">
          </a>
        </li>
      @endif

      {{-- Pagination Elements --}}
      @foreach ($elements as $element)
        {{-- "Three Dots" Separator --}}
        @if (is_string($element))
          <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
            @else
              <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Next Page Link --}}
      @if ($paginator->hasMorePages())
        <li class="page-item">
          <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"
             aria-label="@lang('pagination.next')">
            <img class="arrow" src="{{ asset('assets/images/blog/right-arrow.svg') }}" alt="right arrow"></a>
        </li>
      @endif
    </ul>
  </nav>
@endif
