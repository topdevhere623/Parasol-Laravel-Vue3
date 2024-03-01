import BasePaymentMethod from './base-payment-method'

export default class MamoMonthlyPaymentMethod extends BasePaymentMethod {

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
