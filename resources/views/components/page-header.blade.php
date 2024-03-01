<section class="page-header">
  <div class="container">
    @if($theme->showBreadcrumbs)
      <ul vocab="http://schema.org/" typeof="BreadcrumbList" class="breadcrumbs">
        <li property="itemListElement" typeof="ListItem">
          <a property="item" typeof="WebPage" href="{{ route('home') }}">Home</a>
        </li>
        <li>{{ $slot }}</li>
      </ul>
    @endif

    <h1 class="text-uppercase fw-bolder mt-4">{{ $slot }}</h1>
  </div>
</section>
