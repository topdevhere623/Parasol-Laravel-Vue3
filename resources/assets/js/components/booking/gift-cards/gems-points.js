export default class GemsPointsGiftCard {

  #cardBlock = $('#gemsPointsGiftCardBlock')
  #cardNumberInput = $('#giftCardNumber')
  #cardAmountInput = $('#giftCardAmount')
  #amountInputMask = null
  #applyBtn = $('#gemsPointsGiftCardApply')
  #balanceBlock = $('#gemsPointsGiftCardBalance')

  constructor () {
    const cardNumber = this.#cardBlock.data('card-number')
    if (!cardNumber) {
      return
    }
    this.#cardNumberInput.val(cardNumber)
    $(() => {
      this.getBalance()
      this.#applyBtn.on('click', () => {
        this.getDiscount()
      })
    })
  }

  getBalance () {
    const params = {
      card_number: this.#cardNumberInput.val(),
      card_type: this.#cardBlock.data('card-type'),
    }

    this.#applyBtn.attr('disabled', true)
    this.#applyBtn.addClass('loading')

    return axios.get('gift-card/balance', { params }).
      then(({ data }) => {
        this.#balanceBlock.html(data.data.balance.toLocaleString('en-US',
          { maximumFractionDigits: 2 }))
        this.setInputMask(data.data.min, data.data.max)
      }).
      catch((error) => {
        if (error.response) {
          new bs5t.Toast({
            body: error.response.data.message,
            className: 'border-0 bg-danger text-white',
            btnCloseWhite: true,
          }).show()

          this.setInputMask(0, 0)
        }

      }).finally(() => {
        this.#applyBtn.attr('disabled', false)
        this.#applyBtn.removeClass('loading')
      })
  }

  getDiscount () {
    this.#applyBtn.attr('disabled', true)
    this.#applyBtn.addClass('loading')

    const params = {
      card_number: this.#cardNumberInput.val(),
      card_type: this.#cardBlock.data('card-type'),
      amount: this.#amountInputMask.unmaskedValue,
    }

    return axios.get('gift-card/discount', { params }).
      then(({ data }) => {
        this.#cardAmountInput.val(this.#amountInputMask.unmaskedValue)
        this.#cardAmountInput.trigger('change')
      }).
      catch((error) => {
        if (error.response) {
          new bs5t.Toast({
            body: error.response.data.message,
            className: 'border-0 bg-danger text-white',
            btnCloseWhite: true,
          }).show()
        }
      }).finally(() => {
        this.#applyBtn.attr('disabled', false)
        this.#applyBtn.removeClass('loading')
      })

  }

  setInputMask (min, max) {
    this.#amountInputMask = IMask(
      document.getElementById('gemsPointsGiftCardAmountInput'),
      {
        mask: 'num',
        blocks: {
          num: {
            // nested masks are available!
            mask: Number,
            thousandsSeparator: ',',
            max: max,
            min: min,
          },
        },
      })
  }
}

new GemsPointsGiftCard()
