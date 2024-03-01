import './bootstrap'

import './pages/home'
import './pages/clubs-page'
import './pages/blogs'

import './components/club-details-request-form'
import './components/booking/booking'
import './components/newsletter-modal'

$('.scrollToBottom').on('click', e => {
  e.preventDefault()

  let href = $(e.target).attr('href')
  if (href?.charAt(0) === '/') {
    window.location = href
    return
  }
  $('html, body').animate({
    scrollTop: $(href).offset().top,
  }, 500)
})

window.vat = 0.05
window.moneyFormat = value => {
  return new Intl.NumberFormat('en-US',
    {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value.toFixed(2))
}
