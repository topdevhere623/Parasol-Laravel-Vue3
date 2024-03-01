import BasePaymentMethod from './payment-methods/base-payment-method'
import CheckoutPaymentMethod from './payment-methods/checkout-payment-method'
import HsbcCheckoutPaymentMethod from './payment-methods/hsbc-checkout-payment-method'
import MonthlyPaymentMethod from './payment-methods/monthly-payment-method'
import AmazonPayfortPaymentMethod from './payment-methods/amazon-payfort-payment-method'
import BankTransferPaymentMethod from './payment-methods/bank-transfer-payment-method'
import PaytabsMonthlyPaymentMethod from './payment-methods/paytabs-monthly-payment-method'
import MamoMonthlyPaymentMethod from './payment-methods/mamo-monthly-payment-method'

class BookingStepTwo {
  #paymentMethodsAliases = {
    basePayment: BasePaymentMethod,
    checkout: CheckoutPaymentMethod,
    monthly: MonthlyPaymentMethod,
    hsbc_checkout: HsbcCheckoutPaymentMethod,
    amazon_payfort: AmazonPayfortPaymentMethod,
    bank_transfer: BankTransferPaymentMethod,
    paytabs_monthly: PaytabsMonthlyPaymentMethod,
    mamo_monthly: MamoMonthlyPaymentMethod,
  }
  #initiatedPaymentMethods = {}
  #activePaymentMethodCode

  errorModal = $('#errorModal')

  constructor () {
    $('.paymentMethodInput').on('change', e => {
      this.#activePaymentMethodCode = $(e.target).attr('id')

      Object.entries(this.#initiatedPaymentMethods).forEach(([_, item]) => {
        item.hide()
      })
      this.getActivePaymentMethodClass().show()
    })

    $('#bookingStepTwoForm').on('submit', e => {
      e.preventDefault()
      this.getActivePaymentMethodClass().submit()
    })

    $(() => {
      $('.paymentMethodInput:checked').change()
    })

    console.log(this.errorModal.data('show'))
    if (this.errorModal.data('show')) {
      Fancybox.show(
        [
          {
            src: '#errorModal',
            type: 'inline',
          }],
      )
    }
  }

  getPaymentMethodClass = (code) => {
    return this.#initiatedPaymentMethods[code] ??= new (this.#paymentMethodsAliases.hasOwnProperty(code)
      ? this.#paymentMethodsAliases[code]
      : this.#paymentMethodsAliases.basePayment)(code)
  }

  getActivePaymentMethodClass () {
    return this.getPaymentMethodClass(this.#activePaymentMethodCode)
  }

}

window.BookingStepTwo = BookingStepTwo
