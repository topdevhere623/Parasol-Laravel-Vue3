import BasePaymentMethod from './base-payment-method'

export default class AmazonPayfortPaymentMethod extends BasePaymentMethod {

  successMessageBlock = $('#amazonPayfortSuccess')
  iframe = () => $('#amazon_block__iframe')

  constructor (blockName) {
    super(blockName)

    window.document.addEventListener('amazonPayFortToken', (event) => {
      const response = event.detail

      this.paymentData.val(JSON.stringify({ token: response.token }))
      if (response.status && response.token) {
        this.iframe().remove()
        this.successMessageBlock.show()
        this.enablePaymentBtn()
      }
      else if (!response.status) {
        if (response.error_message) {
          this.showErrorModal(response.error_message)
          this.hide()
          this.show()
        }
        else {
          window.location.replace(response.url)
        }
      }
    }, false)

  }

  show () {
    this.showLoadingBlock()
    this.init()
  }

  hide () {
    super.hide()
    this.iframe().attr('about:blank')
    this.iframe().remove()
  }

  init () {
    const amazonBlock = $('.amazonBlock')
    this.showLoadingBlock()
    this.successMessageBlock.hide()
    this.disablePaymentBtn()
    this.iframe().remove()
    amazonBlock.append(
      '<iframe id="amazon_block__iframe" src="/checkout/' + amazonBlock.data('booking') +
      '/payment/amazon-request" name="amazon_block__iframe" style="display: none"></iframe>',
    )
    this.iframe().on('load', () => {
      this.hideLoadingBlock()
      this.iframe().show()
      super.show()
      this.disablePaymentBtn()
    })

  }
}
