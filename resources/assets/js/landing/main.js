import './slick.min'
import './matchHeight'
import axios from 'axios'

$('.lf-col-holders').slick({
    dots: false,
    arrows: false,
    infinite: false,
    slidesToShow: 3,
    slidesToScroll: 3,
    responsive: [
        {
            breakpoint: 1024,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                centerMode: true,
                centerPadding: '90px',
            }
        },
        {
            breakpoint: 767,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                centerMode: true,
                centerPadding: '60px',
            }
        }
    ]
});

$('.testi-slide-holder').slick({
    dots: false,
    arrows: false,
    infinite: false,
    slidesToShow: 3,
    slidesToScroll: 3,
    responsive: [
        {
            breakpoint: 1024,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
                dots: true
            }
        },
        {
            breakpoint: 767,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: true
            }
        }
    ]
});

$(function () {
    $('.lf-items-inner').matchHeight({
        byRow: true,
        property: 'height',
        target: null,
        remove: false
    });
    $('.testi-slides-inner').matchHeight({
        byRow: true,
        property: 'height',
        target: null,
        remove: false
    });
    $('.testi-content').matchHeight({
        byRow: true,
        property: 'height',
        target: null,
        remove: false
    });
    $('.testi-content p').matchHeight({
        byRow: true,
        property: 'height',
        target: null,
        remove: false
    });
});
$(function () {
    $('a[href*="#"]:not([href="#"])').click(function () {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top
                }, 1000);
                return false;
            }
        }
    });
});

$('.request-form').submit(e => {
  e.preventDefault()
  const self = $(e.target)
  const requestUrl = self.attr('data-request-url')
  const submitBtn = self.find('.request-form__inputs__submit')
  submitBtn.prop('disabled', true)
  submitBtn.addClass('loading')

  axios.post(requestUrl + window.location.search, self.serialize()).then((response) => {
    self.find('.request-form__inputs').hide()
    self.find('.request-form__success').show()
    self.find('.request-form__header').hide()
  })
})
