class NewsletterModal {
  VISITED_COOKIE_NAME = 'site-visited';
  VISITED_FIRST_TIME_COOKIE_NAME = 'site-visited-first-time';
  MODAL_CLOSED_COOKIE_NAME = 'newsletter-modal-closed';
  SUCCESS_CLASS_NAME = 'text-success';
  ERROR_CLASS_NAME = 'text-danger';

  constructor() {

    const visited = this.getCookie(this.VISITED_COOKIE_NAME);
    const showPopup =
      visited
      && !this.getCookie(this.VISITED_FIRST_TIME_COOKIE_NAME)
      && !this.getCookie(this.MODAL_CLOSED_COOKIE_NAME);

    if (showPopup) {
      this.openModal()
    }
    if (!visited) {
      this.setCookie(this.VISITED_COOKIE_NAME, true);
      this.setCookie(this.VISITED_FIRST_TIME_COOKIE_NAME, true, 5);
    }

    // Event handlers

    $('#subscribe-newsletter').click((e) => {
      e.preventDefault();

      this.openModal()
    })

    $('.newsletter-modal-message form').submit(e => {
      e.preventDefault();

      const form = $(e.target)
      axios
        .post('/api/newsletter-subscribe', form.serialize())
        .then((response) => {
          $('.message')
            .removeClass(this.ERROR_CLASS_NAME)
            .addClass(this.SUCCESS_CLASS_NAME)
            .html('Your are successfully subscribed to newsletter.')

          setTimeout(() => {
            this.closeModal()
            $('.message').html('')
          }, 3000);
        })
        .catch((response) => {
          $('.message')
            .removeClass(this.SUCCESS_CLASS_NAME)
            .addClass(this.ERROR_CLASS_NAME)
            .html(response.response.data.message)
        })

      return false
    })

    $('.newsletter-modal-message .modal-message__close').click((e) => {
      this.closeModal()
    })
  }

  openModal = () => {
    $('.newsletter-modal-message').removeClass('d-none').addClass('d-flex')
  }

  closeModal = () => {
    $('.newsletter-modal-message').removeClass('d-flex').addClass('d-none')
    this.setCookie(this.MODAL_CLOSED_COOKIE_NAME, true)
  }

  getCookie = function (name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    if (match) return match[2];
  }

  setCookie = function (name, value, mins) {
    var expires = '';
    if (mins) {
      var date = new Date();
      date.setTime(date.getTime() + (mins * 60 * 1000));
      expires = '; expires=' + date.toUTCString();
    }
    document.cookie = name + '=' + (value || '') + expires + '; path=/';
  }
}

const newsletterModal = new NewsletterModal();