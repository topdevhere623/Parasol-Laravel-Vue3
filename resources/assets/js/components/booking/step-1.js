class BookingStepOne {

  planId = null
  vatType = null
  #childrenBlock = $('#childrenBlock')
  #childrenInput = $('#numberOfChildren')
  #juniorInput = $('#numberOfJuniors')
  showChildrenBlock = 0
  allowedJuniorCount = 0
  allowedChildrenCount = 0

  #coupon = null
  #applyCouponBtn = $('#applyCouponBtn')
  #couponInput = $('#couponCode')
  couponRequiredByPlan = false
  couponRequiredBySource = false
  allowedClubsCount = 0
  fixedClubs = []
  includedClubs = []
  selectedClubs = []
  tabbyPaymentsCount = 0
  #clubItemsSelector = $('.plan-cfg__clubs__item')
  #selectedClubsInput = $('#selectedClubs')
  #selectedClubsCountSelector = $('#selectedClubsCount')
  #allowedClubsCountSelector = $('#allowedClubsCount')
  submitBtn = $('#bookingStepOneSubmit')
  #giftCardAmount = $('#giftCardAmount')
  #giftCardSummaryBlock = $('#gift_card_summary_block')
  #giftCardSummaryAmount = $('#gift_card_summary_amount')
  #cityInput = $('#city')
  #areaInput = $('#area')
  #priceCalculateTimeout = null
  #priceCalculateAbortController = new AbortController()
  #dubaiBlueWaterAreaId = 34
  #coveBeachId = 83
  #selectedAreaId

  #summarySelectors = {
    block: $('#summaryBlock'),
    package: $('#summaryPackage'),
    childrenBlock: $('#summaryChildrenBlock'),
    children: $('#summaryChildren'),
    juniorBlock: $('#summaryJuniorBlock'),
    junior: $('#summaryJunior'),
    coupon: $('#summaryCoupon'),
    vat: $('#summaryVat'),
    total: $('#summaryTotal'),
  }
  #totalPriceSelector = $('#totalPrice')

  constructor () {
    this.#applyCouponBtn.on('click', e => {
      e.preventDefault()
      this.fetchCoupon()
    })

    $('input[name="plan_id"]').on('change', e => {
      const planElement = $(e.target)
      this.planId = planElement.val()
      this.allowedClubsCount = planElement.data('number-clubs')
      this.allowedChildrenCount = planElement.data('number-of-allowed-children')
      this.allowedJuniorCount = planElement.data('number-of-allowed-juniors')
      this.showChildrenBlock = planElement.data('show-children-block')
      this.couponRequiredByPlan = planElement.data('coupon-required')
      this.fixedClubs = [...planElement.data('fixed-clubs')]
      this.includedClubs = [...planElement.data('include-clubs')]
      this.tabbyPaymentsCount = planElement.data('tabby-payments-count')

      if (this.#areaInput.val() == this.#dubaiBlueWaterAreaId) {
        let index = this.includedClubs.indexOf(this.#coveBeachId)
        if (index !== -1) {
          this.includedClubs.splice(index, 1)

        }

        if (this.fixedClubs.length == this.allowedClubsCount) {
          this.allowedClubsCount -= 1
        }

        index = this.fixedClubs.indexOf(this.#coveBeachId)
        if (index !== -1) {
          this.fixedClubs.splice(index, 1)
        }

      }

      this.fetchCoupon()
      this.showPlanClubs()
      this.calculateTotalPrice()
      this.updateChildBlock()
    })

    $('.club-select').on('click', e => {
      e.preventDefault()
      this.toggleClubSelect($(e.target).data('id'))
    })

    this.#childrenInput.on('change', () => this.childrenJuniorChange())
    this.#juniorInput.on('change', () => this.childrenJuniorChange())

    this.#cityInput.on('change', () => this.fetchAreas())
    this.#areaInput.on('change', () => {
      const selectedPlan = $('input[name="plan_id"]:checked')
      // Cove beach club is unavailable in Dubai Blue Waters location
      // TODO: refactor this to general setting
      if (this.#areaInput.val() == this.#dubaiBlueWaterAreaId
        || this.#selectedAreaId == this.#dubaiBlueWaterAreaId
      ) {
        selectedPlan.change()
      }

      this.#selectedAreaId = this.#areaInput.val()
    })

    this.#giftCardAmount.on('change', () => this.calculateTotalPrice())

    $('#membershipSourceId').on('change', (e) => {
      const showInput = !!$('#membershipSourceId option:selected').data('value')
      $('input#membershipSourceOther').
        toggle(showInput).
        attr('required', showInput)

      // required if membershipSourceId is "Member referral"
      this.couponRequiredBySource = e.target.value === "15";
    })

    $('#bookingStepOneForm').on('submit', e => {
      e.preventDefault()
      this.submit()
    })

    $(() => {
      $('input[name="plan_id"]:checked').change()
      this.#childrenInput.change()
      this.#juniorInput.change()
      this.fetchAreas()
    })
  }

  getBookingEmail () {
    return $('#bookingEmail').val()
  }

  fetchCoupon () {
    const couponCode = this.#couponInput.val()
    const couponInput = $('#bookingCouponCode')
    const button = this.#applyCouponBtn

    if (couponCode?.length < 1) {
      this.#coupon = null
      if (couponInput.val()) {
        couponInput.val('')
        this.calculateTotalPrice()
      }
      return false
    }

    button.prop('disabled', true)
    button.addClass('loading')

    const params = {
      code: couponCode,
      plan_id: this.planId,
    }

    if (this.getBookingEmail()) {
      params.email = this.getBookingEmail()
    }

    this.#coupon = null
    couponInput.val('')
    this.submitBtn.attr('disabled', true)
    return axios.get('coupon/check', { params }).then((response) => {
      this.#coupon = response.data.data
      couponInput.val(couponCode)
    }).catch(({ response }) => {
      if (response.status) {
        new bs5t.Toast({
          body: response.data.message,
          className: 'border-0 bg-danger text-white',
          btnCloseWhite: true,
        }).show()
      }
    }).finally(() => {
      button.prop('disabled', false)
      button.removeClass('loading')
      this.calculateTotalPrice()
      this.submitBtn.attr('disabled', false)
    })
  }

  fetchAreas () {
    const cityId = this.#cityInput.val()
    const areaSelect = this.#areaInput
    areaSelect.html('<option value="">Select area</option>')
    if (!cityId) {
      return
    }
    return axios.get(`areas/${cityId}`).then((response) => {
      let areas = response.data.data
      $.each(areas, function (ind) {
        let area = areas[ind]
        areaSelect.append($('<option />').val(area.id).text(area.name))
      })

      areaSelect.val(areaSelect.data('selected'))
    }).catch(({ response }) => {
      if (response.status) {
        new bs5t.Toast({
          body: response.data.message,
          className: 'border-0 bg-danger text-white',
          btnCloseWhite: true,
        }).show()
      }
    })
  }

  showPlanClubs () {

    this.selectedClubs = []
    this.#selectedClubsInput.val('')
    this.#selectedClubsCountSelector.text(0)
    this.#allowedClubsCountSelector.text(this.allowedClubsCount)

    this.#clubItemsSelector.attr('disabled', true).addClass('d-none')
    this.#clubItemsSelector.each((index, item) => {
      const clubItem = $(item)

      if (this.includedClubs.includes(clubItem.data('id'))) {
        clubItem.attr('disabled', false).
          removeClass('selected disabled').
          removeClass('d-none').
          find('.club-select').
          attr('disabled', false)
      }
    })

    this.fixedClubs.forEach(clubId => {
      this.toggleClubSelect(clubId)
    })
  }

  toggleClubSelect (clubId) {

    if (
      (this.fixedClubs.includes(clubId) && this.selectedClubs.includes(clubId))
      || !this.includedClubs.includes(clubId)
    ) {
      return false
    }

    const clubItem = $('#clubItem' + clubId)

    if (this.selectedClubs.includes(clubId)) {
      this.selectedClubs.splice(this.selectedClubs.findIndex(v => v === clubId),
        1)

      clubItem.removeClass('selected')
      if (this.selectedClubs.length < this.allowedClubsCount) {
        this.#clubItemsSelector.removeClass('disabled').
          find('.club-select').
          attr('disabled', false)
      }

    }
    else {
      this.selectedClubs.push(clubId)
      if (!clubItem.hasClass('disabled')) {
        clubItem.addClass('selected')
      }

      if (this.selectedClubs.length >= this.allowedClubsCount) {
        this.#clubItemsSelector.not('.selected').
          addClass('disabled').
          find('.club-select').
          attr('disabled', true)
      }
    }
    this.#selectedClubsCountSelector.text(this.selectedClubs.length)
    this.#selectedClubsInput.val(this.selectedClubs.join())
  }

  calculateTotalPrice () {
    const form = $('#bookingStepOneForm')
    clearTimeout(this.#priceCalculateTimeout)

    this.#priceCalculateAbortController.abort()
    this.#priceCalculateAbortController = new AbortController()

    this.#priceCalculateTimeout = setTimeout(() => {
      this.submitBtn.prop('disabled', true)
      this.#summarySelectors.block.addClass('loading')
      axios.post('checkout/get-price', form.serialize(), {
        signal: this.#priceCalculateAbortController.signal,
      }).
        then(({ data }) => {
          const prices = data.data

          if (window.TabbyPromo && this.tabbyPaymentsCount) {
            $('#tabby_widget').show()
            new window.TabbyPromo({
              selector: '#tabby_widget',
              lang: 'en',
              currency: 'AED',
              price: prices.total_price,
              installmentsCount: this.tabbyPaymentsCount,
              productType: 'creditCardInstallments',
            })
          }
          else {
            $('#tabby_widget').hide()
          }

          this.#summarySelectors.package.text(
            moneyFormat(prices.plan_amount ?? 0),
          )
          this.#summarySelectors.vat.text(moneyFormat(prices.vat_amount ?? 0))
          this.#summarySelectors.total.text(
            moneyFormat(prices.total_price ?? 0),
          )
          this.#totalPriceSelector.text(moneyFormat(prices.total_price ?? 0))
          if (prices.extra_junior_amount) {
            this.#summarySelectors.juniorBlock.show()
            this.#summarySelectors.junior.text(
              moneyFormat(prices.extra_junior_amount))
          }
          else {
            this.#summarySelectors.juniorBlock.hide()
          }
          if (prices.extra_child_amount) {
            this.#summarySelectors.childrenBlock.show()
            this.#summarySelectors.children.text(
              moneyFormat(prices.extra_child_amount))
          }
          else {
            this.#summarySelectors.childrenBlock.hide()
          }

          if (prices.coupon_amount) {
            this.#summarySelectors.coupon.html(
              'AED <span style="font-family: sans-serif; width: auto">-</span>' +
              moneyFormat(prices.coupon_amount))
          }
          else {
            this.#summarySelectors.coupon.text('N/A')
          }

          if (prices.gift_card_discount_amount) {
            this.#giftCardSummaryBlock.show()
            this.#giftCardSummaryAmount.html(
              moneyFormat(prices.gift_card_discount_amount))
          }
          else {
            this.#giftCardSummaryBlock.hide()
            this.#giftCardSummaryAmount.html('N/A')
          }

        }).catch((error) => {
        if (error.response) {
          new bs5t.Toast({
            body: error.response.data?.message ?? error.response.message,
            className: 'border-0 bg-danger text-white',
            btnCloseWhite: true,
          }).show()
        }
      }).finally(() => {
        this.submitBtn.prop('disabled', false)
        this.#summarySelectors.block.removeClass('loading')
      })

    }, 100)

  }

  childrenJuniorChange () {

    const showAgeConfirmation = this.#childrenInput.val() > 0 ||
      this.#juniorInput.val() > 0
    this.calculateTotalPrice()

    $('#ageConfirmation').toggle(showAgeConfirmation)
    $('#ageConfirmation input').attr('required', showAgeConfirmation)
  }

  updateChildBlock () {
    if (!this.showChildrenBlock) {
      this.#childrenInput.val(0).change()
      this.#juniorInput.val(0).change()
    }

    this.#childrenInput.html('<option value="0" selected>No child</option>')
    this.#juniorInput.html('<option value="0" selected>No junior</option>')

    for (let i = 1; i <= this.allowedChildrenCount; i++) {
      this.#childrenInput.append(`<option value="${i}">${i} ${i === 1 ? 'child' : 'children'}</option>`)
    }

    for (let i = 1; i <= this.allowedJuniorCount; i++) {
      this.#juniorInput.append(`<option value="${i}">${i} ${i === 1 ? 'junior' : 'juniors'}</option>`)
    }

    this.#childrenBlock.toggle(this.showChildrenBlock)
  }

  submit () {
    if (this.couponRequiredByPlan && this.#coupon === null) {
      this.submitErrorMessage(`Please enter a coupon to purchase this plan`)
      return false
    }

    if (this.couponRequiredBySource && this.#coupon === null) {
      this.submitErrorMessage(
        `Please enter a referral coupon to continue purchase this plan or choose another option "HOW DID YOU HEAR ABOUT US?"`,
        5000
      )
      setTimeout(function() {
        $('#couponCode').focus();
      }, 5000);

      return false
    }

    if (this.allowedClubsCount > this.selectedClubs.length) {
      this.submitErrorMessage(
        `Please select ${this.allowedClubsCount} clubs to proceed to payment`)
      return false
    }

    if (!$('#bookingName').val() && !$('#bookingEmail').val()) {
      this.submitErrorMessage(`Please fill required(*) fields`)
      return false
    }

    const form = $('#bookingStepOneForm')
    this.submitBtn.prop('disabled', true)
    this.submitBtn.addClass('loading')

    axios.post(form.attr('action'), form.serialize()).then(({ data }) => {
      window.location.replace(data.data.url)
    }).catch(({ response }) => {
      new bs5t.Toast({
        body: response.data.message,
        className: 'border-0 bg-danger text-white',
        btnCloseWhite: true,
      }).show()
      this.submitBtn.prop('disabled', false)
      this.submitBtn.removeClass('loading')
    })
  }

  submitErrorMessage (message, timeout = 3000) {
    const errorBlock = $('.alertErrorMsg')
    errorBlock.text(message).show()
    return setTimeout(() => errorBlock.hide(), timeout)
  }
}

window.BookingStepOne = BookingStepOne

$('.acc-tgg-button').on('click', function () {
  $(this).toggleClass('collapsed')
  $('#' + $(this).data('parent')).toggleClass('open')
})
