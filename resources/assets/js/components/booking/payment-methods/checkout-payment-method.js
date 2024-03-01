import BasePaymentMethod from './base-payment-method'

export default class CheckoutPaymentMethod extends BasePaymentMethod {

  errorMessage = {
    'card-number': 'Please enter a valid card number',
    'expiry-date': 'Please enter a valid expiry date',
    'cvv': 'Please enter a valid cvv code',
  }

  cardFullName = $('#checkoutCardFullName')

  constructor () {
    super('checkout')
    this.cardFullName.on('input', () => {
      Frames.isCardValid() && this.cardFullName.val().length > 0 ? this.enablePaymentBtn() : this.disablePaymentBtn()
    })
  }

  show () {
    this.showLoadingBlock()
    this.init()
  }

  hide () {
    super.hide()
    this.clearEventHandlers()
    this.cardFullName.val('')

    Object.entries(this.errorMessage).forEach(([field]) => {
      this.clearErrorMessage(field)
    })
  }

  submit () {
    this.submitButton.addClass('loading')
    this.disablePaymentBtn()
    Frames.cardholder = {
      name: this.cardFullName.val(),
    }
    Frames.submitCard()
  }

  init () {
    this.clearEventHandlers()
    this.disablePaymentBtn()
    this.changePaymentMethodIcon()

    Frames.init({
      publicKey: $('.visa-card').data('public-key'),
      localization: {
        cardNumberPlaceholder: '0000 0000 0000 0000',
        expiryMonthPlaceholder: 'MM',
        expiryYearPlaceholder: 'YY',
        cvvPlaceholder: 'CVV',
      },
      style: {
        base: {
          color: 'white',
          fontSize: '18px',
        },
        focus: {
          color: 'white',
        },
        valid: {
          color: 'white',
        },
        invalid: {
          color: 'red',
        },
        placeholder: {
          base: {
            color: '#87b8d9',
          },
        },
      },
    })

    Frames.addEventHandler(Frames.Events.FRAME_VALIDATION_CHANGED, (event) => {
        const field = event.element

        if (event.isValid || event.isEmpty) {
          this.clearErrorMessage(field)
        }
        else {
          this.setErrorMessage(field)
        }
      },
    )

    Frames.addEventHandler(Frames.Events.PAYMENT_METHOD_CHANGED,
      ({ paymentMethod }) => this.changePaymentMethodIcon(paymentMethod))
    Frames.addEventHandler(Frames.Events.CARD_VALIDATION_CHANGED, () => {
      Frames.isCardValid() && this.cardFullName.val().length > 0 ? this.enablePaymentBtn() : this.disablePaymentBtn()
    })

    Frames.addEventHandler(Frames.Events.CARD_TOKENIZATION_FAILED, () => {
      this.submitButton.removeClass('loading')
      this.enablePaymentBtn()
      Frames.enableSubmitForm()
    })

    Frames.addEventHandler(Frames.Events.CARD_TOKENIZED, ({ token }) => {
      this.paymentData.val(JSON.stringify({ token }))
      super.submit()
    })

    Frames.addEventHandler(Frames.Events.READY, () => {
      this.hideLoadingBlock()
      super.show()
      this.disablePaymentBtn()
    })
  }

  setErrorMessage (field) {
    $('.error-message__' + field).text(this.errorMessage[field])
  }

  clearErrorMessage (field) {
    $('.error-message__' + field).text('')
  }

  clearEventHandlers () {
    Frames.removeAllEventHandlers(Frames.Events.CARD_SUBMITTED)
    Frames.removeAllEventHandlers(Frames.Events.CARD_TOKENIZATION_FAILED)
    Frames.removeAllEventHandlers(Frames.Events.CARD_TOKENIZED)
    Frames.removeAllEventHandlers(Frames.Events.CARD_VALIDATION_CHANGED)
    Frames.removeAllEventHandlers(Frames.Events.FRAME_ACTIVATED)
    Frames.removeAllEventHandlers(Frames.Events.FRAME_BLUR)
    Frames.removeAllEventHandlers(Frames.Events.FRAME_FOCUS)
    Frames.removeAllEventHandlers(Frames.Events.FRAME_VALIDATION_CHANGED)
    Frames.removeAllEventHandlers(Frames.Events.PAYMENT_METHOD_CHANGED)
    Frames.removeAllEventHandlers(Frames.Events.READY)
  }

  changePaymentMethodIcon (paymentMethod = null) {
    const img = $('#checkoutCardLogo')
    img.hide()

    if (paymentMethod) {
      img.attr('src', '/assets/images/card-icons/' + paymentMethod.toLowerCase() + '.svg')
      img.show()
    }
    else {
      img.hide()
    }
  }
}
