import CheckoutPaymentMethod from './checkout-payment-method'

export default class HsbcCheckoutPaymentMethod extends CheckoutPaymentMethod {

  show () {
    $(this.blockName).addClass('hsbc_checkout')
    super.show()
  }

  hide () {
    $(this.blockName).removeClass('hsbc_checkout')
    super.hide()
  }
}
