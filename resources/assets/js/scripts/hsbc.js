inputMask = IMask(document.getElementById('hsbc_bin_number'), {
  mask: '0000 00',
})

$('.join_today').on('click', function () {
  $('.modal-message .modal-content.success').hide()
  $('.modal-message .modal-content.error').hide()
  $('.modal-message .modal-content.form').show()
})

$('form#check_hsbc_bin').submit(function (e) {
  const requestUrl = $(this).attr('action')
  const submitBtn = $(this).find('.btn')
  e.preventDefault()
  submitBtn.attr('disabled')
  submitBtn.addClass('loading')

  axios.post(requestUrl, $(this).serialize()).then(response => {
    const data = response.data.data

    $('.package-link').attr('href', data.url)
    $('.modal-message .modal-content.form').hide()

    if (data.free_checkout) {
      $('.modal-message .modal-content.success-red').show()
    }
    else {
      $('.modal-message .modal-content.success-green').show()
    }
  }).catch(({ response }) => {
    $('form#check_hsbc_bin')[0].reset()

    $('.modal-message .modal-content.form').hide()
    $('.modal-message .modal-content.error').show()
  }).finally(() => {
    submitBtn.prop('disabled', false)
    submitBtn.removeClass('loading')
  })

})
