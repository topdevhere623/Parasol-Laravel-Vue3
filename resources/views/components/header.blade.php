<{{ $tag }} {{ $attributes->class(['text-blue fw-bold text-uppercase', 'fs-2' => !$sm, 'fs-4' => $sm]) }}>
{{ $slot }}
@if($subtitle)
  <span class="d-block mt-1 text-transform-none text-black-50 fw-normal {{ $sm ? 'fs-5' : 'fs-4'  }}">
            {!! $subtitle !!}
        </span>
@endif
</{{ $tag }}>
