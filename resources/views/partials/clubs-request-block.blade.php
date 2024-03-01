<div class="clubs-request-block m-md-4 rounded-0 rounded-md-30 d-flex-center">
  <form class="clubs-request-block__form request-form p-4 rounded-30 d-flex-center flex-column"
        data-request-url="web-form-request/club-information">
    <x-header
      subtitle="{!! $theme->clubRequestSubtitle !!}"
      sm="true"
      class="my-3 text-center request-form__header"
    >
      {{ $theme->clubRequestTitle }}
    </x-header>

    <div class="request-form__success" style="display: none;">
      <div class="alert alert-success fs-5 mb-0 text-center">Awesome! the detailed clubs' guide is on its way
        <img
          draggable="false" role="img" width="16px" class="emoji" alt="💪"
          src="https://s.w.org/images/core/emoji/13.0.0/svg/1f4aa.svg"></div>
    </div>
    <div class="request-form__inputs">
      <div class="form-group">
        <input placeholder="Your name" type="text" class="form-control" name="name" required>
      </div>
      <div class="form-group">
        <input placeholder="Your email" type="email" class="form-control" name="email" required>
      </div>
      <div class="form-group">
        <input placeholder="Your mobile +971xxxxxxxxx" type="tel" class="form-control" name="phone"
               required>
      </div>
      <p class="text-muted">By submitting this request, you agree to our
        <a href="{{ route('page.show', 'privacy-policy') }}">Privacy Policy</a>
      </p>
      <div class="form-group">
        <button
          class="btn btn-warning w-100 request-form__inputs__submit"
          type="submit"
        >
          Send me the guide
          <x-loading-svg/>
        </button>
      </div>
    </div>
  </form>
</div>
