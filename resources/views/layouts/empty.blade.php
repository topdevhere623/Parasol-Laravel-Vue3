@include('layouts.partials.head')

<body data-bs-no-jquery @class($bodyClass ?? $theme->bodyClass)>

@production
  @include('partials.metrics.gtag-noscript')
  @include('partials.metrics.facebook')
@endproduction

@yield('content')

@production
  @include('partials.metrics.yandex-metrica')
  @include('partials.metrics.cloudfront')
  @includeWhen($theme->showTawkChat, 'partials.metrics.tawkto')
@endproduction

</body>
</html>
