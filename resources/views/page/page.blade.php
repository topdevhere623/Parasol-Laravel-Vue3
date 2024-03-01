@extends('layouts.main')
@include('partials.theme-variables')

@section('title')
  {{ $page->page_title ?? $theme->metaTag('title')}}
@endsection
@section('description')
  {{ $page->meta_description ?? $theme->metaTag('description')}}
@endsection

@section('content')
  <x-page-header>{{ $page->title }}</x-page-header>

  <section class="bg-white py-4">
    <div class="container">
      <div class="page-content">
        {!! $page->description !!}
      </div>
    </div>
  </section>

@endsection
