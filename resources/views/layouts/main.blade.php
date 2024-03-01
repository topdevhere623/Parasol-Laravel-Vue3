@include('layouts.partials.head')

<body data-bs-no-jquery @class($theme->bodyClass)>

@production
  @include('partials.metrics.gtag-noscript')
  @include('partials.metrics.facebook')
@endproduction

@includeWhen($theme->showHeader, 'layouts.partials.header')

@yield('content')

@includeWhen($theme->showPreFooterContacts, 'layouts.partials.contacts')

@includeWhen($theme->showFooter, 'layouts.partials.footer')

<script type='text/javascript' src='{{ mix("assets/js/app.js") }}'></script>

@stack('scripts')

<script src="{{ mix('assets/js/vendor/imask.js') }}"></script>

<script>
  {{--  @formatter:off--}}
  const elements = document.querySelectorAll('input[type="tel"]');
  for (let i = 0; i < elements.length; i++) {
    new IMask(elements[i], {
      mask: /^[+]*\d+$/,
      placeholder: {
        show: 'always',
      },
    })
  }
  {{--  @formatter:on--}}
</script>

@production
  @include('partials.metrics.yandex-metrica')
  @include('partials.metrics.cloudfront')
  @includeWhen($theme->showTawkChat, 'partials.metrics.tawkto')
@endproduction

</body>
</html>
