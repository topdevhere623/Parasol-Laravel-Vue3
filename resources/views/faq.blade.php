@extends('layouts.main')
@include('partials.theme-variables')

@section('title')
  Frequently Asked Questions - ADVPLUS
@endsection
@section('description')
  Frequently Asked Questions about ADVPLUS club membership programme in the UAE for individuals, families and businesses
@endsection

@section('content')

  <x-page-header>FAQ</x-page-header>

  <section class="bg-white faq pt-1 pb-5">
    <div class="container">

      @if (count($faqs))
        @foreach ($faqs as $key => $faq)
          <x-header tag="h3" sm="true" class="text-left mb-3 mt-4 fs-sm-4">{{ $faq->name }}</x-header>
          <div class="accordion">

            @foreach ($faq->faqs as $item)
              <div class="accordion-item">
                <div class="accordion-header">
                  <button class="accordion-button fw-bold fs-5 collapsed pe-5" type="button" data-bs-toggle="collapse"
                          data-bs-target="#faqItem{{ $loop->parent->iteration.$loop->iteration }}"
                          aria-expanded="false">
                    {{ $item->question }}
                  </button>
                </div>
                <div id="faqItem{{ $loop->parent->iteration.$loop->iteration }}"
                     class="accordion-collapse collapse fs-5">
                  <div class="accordion-body">{!! $item->answer !!}</div>
                </div>
              </div>
            @endforeach

            @endforeach


          </div>
          @endif
    </div>
  </section>

@endsection
