<div class="club-filter my-4 d-flex flex-column flex-md-row justify-content-between">
  <div class="city-filter container text-center text-md-start">
    <button class="filter-btn active" data-city="all">All <span>{{ $clubs->count() }}</span></button>
    @foreach($clubCities as $key => $item)
      <button class="filter-btn" data-city="{{ $key }}">
        {{ $item['name'] }} <span>{{ $item['count'] }}</span>
      </button>
    @endforeach
  </div>
  <div class="tag-filter container text-end w-100 w-md-25 m-0 mt-3 mt-md-0">
    <div class="d-md-flex justify-content-between align-items-center">
      <div class="form-select-wrap">
        <select name="tags" id="tags" class="form-select" onchange="location = this.value;">
          <option value="{{ route('website.clubs.index') }}">Select Tag</option>
          @foreach($tags as $item)
            <option
              @selected($item->slug == $tag)
              value="{{ route('website.clubs.index', ['tag' => $item->slug]) }}"
            >{{ $item->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>
</div>
