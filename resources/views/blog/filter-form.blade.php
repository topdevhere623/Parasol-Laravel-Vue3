<div class="w-100 w-sm-auto">
  <div class="
      d-flex
      flex-row-reverse flex-md-row
      justify-content-between justify-content-md-start
      align-items-center
  ">
    <details class="custom-select">
      <summary class="radios">
        <img src="{{ url('/assets/images/blog/arrow-down-up.svg') }}" alt="arrow down up">
        <input type="radio" name="item" id="item1" data-link="{{ route('blog-posts', [
            'query' => $query,
            'sort' => 'recent',
        ]) }}" title="Recent" @if($sort=='recent') checked @endif />
        {{--        <input type="radio" name="item" id="item2" data-link="{{ route('blog-posts', [--}}
        {{--            'query' => $query,--}}
        {{--            'sort' => 'popular',--}}
        {{--        ]) }}" title="Popular" @if($sort=='popular') checked @endif />--}}
        <input type="radio" name="item" id="item3" data-link="{{ route('blog-posts', [
            'query' => $query,
            'sort' => 'alphabetical',
        ]) }}" title="Alphabetical" @if($sort=='alphabetical') checked @endif />
      </summary>

      <ul class="list">
        <li>
          <label for="item1">Recent</label>
        </li>
        {{--        <li>--}}
        {{--          <label for="item2">Popular</label>--}}
        {{--        </li>--}}
        <li>
          <label for="item3">Alphabetical</label>
        </li>
      </ul>
    </details>

    <div class="search-box">
      <input
        data-link="{{ route('blog-posts', $sort === 'recent' ? [] : compact('sort')) }}"
        class="form-control"
        placeholder="Search"
        value="{{ $query ?? '' }}"
      />
      <img src="{{ url('/assets/images/blog/search.svg') }}" alt="search" class="bi bi-search">
    </div>
  </div>
</div>
