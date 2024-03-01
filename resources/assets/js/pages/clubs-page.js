import Swiper, { Autoplay, Lazy, Navigation, Pagination } from 'swiper'
import LazyLoad from 'vanilla-lazyload'

Swiper.use([Navigation, Pagination, Lazy, Autoplay])

const clubItemSwiperOptions = {
  slidesPerView: 1,
  pagination: false,
  // lazy: true,
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  on: {
    // LazyLoad swiper images after swiper initialization
    afterInit: (swiper) => {
      new LazyLoad({
        container: swiper.el,
        cancel_on_exit: false,
      })
    },
  },
}

new Swiper('.club-item__swiper', clubItemSwiperOptions)

// Initialize all other swipers as they enter the viewport
// new LazyLoad({
//   elements_selector: ".club-detail__swiper",
//   unobserve_entered: true,
//   callback_enter: function (swiperElement) {
//     new Swiper("#" + swiperElement.id, clubItemSwiperOptions);
//   }
// })

new Swiper('.club-detail__swiper', {
  slidesPerView: 1,
  pagination: false,
  lazy: true,
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
})

$('.filter-btn').on('click', function (e) {
  e.preventDefault()
  $('.filter-btn').removeClass('active')
  $(this).addClass('active')

  const city = $(this).data('city')
  if (city === 'all') {
    $('.club-city').fadeIn(100)
  }
  else {
    $('.club-city').fadeOut(1)
    $('.club-city.' + city).fadeIn(100)
  }

  return false
})
