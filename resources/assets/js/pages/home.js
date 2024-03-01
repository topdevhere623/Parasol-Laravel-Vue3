import Swiper, { Navigation, Pagination, Lazy, Autoplay } from 'swiper'

Swiper.use([Navigation, Pagination, Lazy, Autoplay])

new Swiper('.our-partners-swiper', {
  slidesPerView: 3,
  spaceBetween: 10,
  pagination: false,
  navigation: false,
  preloadImages: true,
  lazy: {
    loadPrevNext: true,
    loadPrevNextAmount: 2,
  },
  loop: true,
  autoplay: {
    delay: 3000,
    disableOnInteraction: false,
  },
  breakpoints: {
    640: {
      slidesPerView: 4,
      spaceBetween: 20,
    },
    768: {
      slidesPerView: 5,
      spaceBetween: 30,
    },
    1024: {
      slidesPerView: 6,
      spaceBetween: 40,
    },
  },
})

new Swiper('.clubs-slider-swiper', {
  slidesPerView: 1,
  spaceBetween: 10,
  pagination: false,
  lazy: true,
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  breakpoints: {
    768: {
      slidesPerView: 2,
      spaceBetween: 20,
    },
    1024: {
      slidesPerView: 3,
      spaceBetween: 30,
    },
  },
})

new Swiper('.testimonials-swiper', {
  slidesPerView: 1,
  spaceBetween: 15,
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  navigation: false,
  breakpoints: {
    630: {
      slidesPerView: 1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  },
})

new Swiper('.instagram-feed', {
  slidesPerView: 1,
  spaceBetween: 5,
  pagination: false,
  navigation: false,
  lazy: true,
  loadPrevNext: true,
  autoplay: {
    delay: 2500,
    disableOnInteraction: false,
  },
  breakpoints: {
    630: {
      slidesPerView: 2,
    },
    768: {
      slidesPerView: 3,
    },
    1024: {
      slidesPerView: 4,
    },
  },
})
