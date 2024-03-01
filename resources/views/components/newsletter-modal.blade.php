<div class="newsletter-modal-message modal-message d-none align-items-center justify-content-center">
  <div class="position-relative">
    <button class="modal-message__close position-absolute bg-transparent border-0" title="Close">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" tabindex="-1">
        <path d="M20 20L4 4m16 0L4 20"></path>
      </svg>
    </button>
    <form class="position-relative modal-content p-4 rounded-30 bg-white">
      <img class="position-absolute" src="{{ asset('assets/images/swimsuit-girl.png') }}" alt="Swimsuit girl"/>
      <div class="d-flex-center flex-column">
        <x-header
          sm="true"
          subtitle="Sign-up now to receive the most recent news, updates on new clubs, and exclusive deals sent directly to your email inbox."
          class="px-5 my-3"
        >
          Stay tuned!
        </x-header>
        <div class="form-group">
          <input placeholder="Your name" type="text" class="form-control" name="name" required=""/>
        </div>
        <div class="form-group">
          <input placeholder="Email" type="email" class="form-control" name="email" required=""/>
        </div>
        <div class="form-group">
          <div class="message"></div>
        </div>
        <div class="form-group">
          <button class="btn btn-warning w-100 request-form__inputs__submit" type="submit"> Join the list
            <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px"
                 xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" xml:space="preserve"> <path
                fill="#fff"
                d="M 98.293 50 C 98.293 23.334 76.666 1.707 50 1.707 C 23.334 1.707 1.707 23.334 1.707 50 M 9.896 50 C 9.896 27.953 27.743 9.896 50 9.896 C 72.257 9.896 90.104 27.953 90.104 50">
                <animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50"
                                  to="360 50 50" repeatCount="indefinite"></animateTransform>
              </path> </svg>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
