$('.request-form').submit(e => {
  e.preventDefault()
  const self = $(e.target)
  const requestUrl = self.attr('data-request-url')
  const submitBtn = self.find('.request-form__inputs__submit')
  submitBtn.prop('disabled', true)
  submitBtn.addClass('loading')

  axios.post(requestUrl, self.serialize()).then((response) => {
    self.find('.request-form__inputs').hide()
    self.find('.request-form__success').show()
    self.find('.request-form__header').hide()
  })
})
