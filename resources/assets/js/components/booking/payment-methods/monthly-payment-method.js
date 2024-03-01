import CheckoutPaymentMethod from './checkout-payment-method'

export default class MonthlyPaymentMethod extends CheckoutPaymentMethod {

  descriptionBlock = $('#monthlyPaymentDescription')

  show () {
    super.show()
    this.descriptionBlock.show()
  }

  hide () {
    super.hide()
    this.descriptionBlock.hide()
  }

}
