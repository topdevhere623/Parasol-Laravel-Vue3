import moment from 'moment'
import DateRangePicker from '../vendor/daterangepicker-modified'

window.moment = moment

$.fn.daterangepicker = function (options, callback) {
  const implementOptions = $.extend(true, {}, $.fn.daterangepicker.defaultOptions, options)
  this.each(function () {
    const el = $(this)
    if (el.data('daterangepicker')) {
      el.data('daterangepicker').remove()
    }
    el.data('daterangepicker', new DateRangePicker(el, implementOptions, callback))
  })
  return this
}
