<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
  <title>@yield('title', $theme->metaTitle)</title>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
  <meta name="facebook-domain-verification" content="rfu0p4f2oo255hf5g9hb0zqr01sr6z"/>

  <meta name="description" content="@yield('description', $theme->metaDescription)">
  <meta name="author" content="ADVPLUS">

  <meta property="og:site_name" content="{{ url('/') }}"/>
  <meta property="og:locale" content="{{ App::getLocale() }}"/>
  <meta property="og:title" content="@yield('title', $theme->metaTitle)"/>
  <meta property="og:url" content="{{ url()->full() }}"/>

  <meta name="msapplication-TileImage" content="{{ $theme->favicon }}"/>
  <link rel="apple-touch-icon" href="{{ $theme->favicon }}"/>
  <link rel="icon" href="{{ $theme->favicon }}" sizes="32x32"/>
  <link rel="icon" href="{{ $theme->favicon }}" sizes="192x192"/>
  <link rel='stylesheet' href='{{ mix("assets/css/app.css") }}' type='text/css' media='all'/>

  @stack('styles')

  {{-- PHP Storm formatter @formatter:off--}}
  <script>
    window.siteUrl = "{{ url('/')}}";
    window.sentryDns = "{{ config('app.sentry_website_dsn') }}";
    window.sentryReleaseVersion = "{{ config('app.sentry_release_version') }}";
  </script>
  {{--  @formatter:on--}}

  <!--[if lte IE 8]>
  <script type="text/javascript">
    var $buoop = {
      vs: { i: 10, f: 25, o: 12.1, s: 7, n: 9 }
    };

    $buoop.ol = window.onload;

    window.onload = function () {
      try {
        if ($buoop.ol) {
          $buoop.ol()
        }
      }
      catch (e) {
      }

      var e = document.createElement("script");
      e.setAttribute("type", "text/javascript");
      e.setAttribute("src", "https://browser-update.org/update.js");
      document.body.appendChild(e);
    };
  </script>
  <![endif]-->

  <!-- for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
  <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  @production
    @include('partials.metrics.gtag')
    @include('partials.metrics.facebook-pixel')
    @include('partials.metrics.drip')
    @include('partials.metrics.netcorecloud')
    @include('partials.metrics.trustpilot')
  @endproduction

</head>
