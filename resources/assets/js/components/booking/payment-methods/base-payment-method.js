export default class BasePaymentMethod {
  blockName
  submitButton = $('#bookingStepTwoSubmit')
  form = $('#bookingStepTwoForm')
  paymentData = $('#paymentData')
  loadingBlock = $('#paymentMethodLoading')
  errorModalText = $('#errorModalText')

  constructor (blockName) {
    this.blockName = '#' + blockName + '_payment_block'
    this.block
  }

  show () {
    const block = $(this.blockName)
    const isAvailable = block.data('is-available') ?? true

    block.show()
    isAvailable ? this.enablePaymentBtn() : this.disablePaymentBtn()
  }

  hide () {
    this.submitButton.removeClass('loading')
    this.paymentData.val('')
    $(this.blockName).hide()
    this.hideLoadingBlock()
  }

  enablePaymentBtn () {
    this.submitButton.attr('disabled', false)
  }

  disablePaymentBtn () {
    this.submitButton.attr('disabled', true)
  }

  showLoadingBlock () {
    this.loadingBlock.show()
  }

  hideLoadingBlock () {
    this.loadingBlock.hide()
  }

  showErrorModal (text) {
    this.errorModalText.html(text)

    Fancybox.show(
      [
        {
          src: '#errorModal',
          type: 'inline',
        }],
    )
  }

  submit () {
    this.submitButton.addClass('loading')
    this.disablePaymentBtn()

    return axios.post(this.form.attr('action'), this.form.serialize()).
      then(({ data }) => {
        if (data.data.url) {
          window.location = data.data.url
        }
      }).
      catch(({ response }) => {
        this.hide()
        this.show()

        if (response.data?.data?.content) {
          this.showErrorModal(response.data.data.content)
        }
        else {
          new bs5t.Toast({
            body: response.data.message,
            className: 'border-0 bg-danger text-white',
            btnCloseWhite: true,
          }).show()
        }
      })
  }
}
