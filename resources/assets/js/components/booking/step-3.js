import moment from "moment";

$('#billing_is_needed').click(e => {
  toggleRequiredBillings(true)
  $('.billing-details').show(500)
})

$('#billing_is_not_needed').click(e => {
  toggleRequiredBillings(false)
  $('.billing-details').hide(500)
})

function toggleRequiredBillings(state) {
  $('.billing-required-input').each((key, input) => {
    input.required = state
  })
}

class BookingStepThree {

  kidsDateInput = $('.input-date-kids')
  juniorsDateInput = $('.input-date-juniors')
  membersDateInput = $('.input-date-members')
  memberStartDateInput = $('#memberStartDate')
  form = $('#bookingStepThreeForm')
  formDataTmp = []
  emailsList = []

  constructor() {
    let memberStartDate = this.memberStartDateInput.data('value')
    this.createDatePicker(this.memberStartDateInput, moment(memberStartDate), moment(memberStartDate).add(30, 'days'),)
    this.childDatePicker(this.juniorsDateInput, 20)
    this.childDatePicker(this.kidsDateInput, 15)
    this.createDatePicker(
      this.membersDateInput,
      moment().subtract(110, 'years').startOf('month'),
      moment().subtract(21, 'years').startOf('day')
    )

    this.handleInput(this.memberStartDateInput)
    this.handleInput(this.kidsDateInput)
    this.handleInput(this.juniorsDateInput)
    this.handleInput(this.membersDateInput)

    $('.image-uploader').on('change', e => {
      const files = $(e.target)[0].files
      const previewImage = $('.' + $(e.target).attr('id'))

      if (files.length > 0) {
        previewImage.attr('style', 'background-image:url(' + URL.createObjectURL(files[0]) + ')')
      } else {
        previewImage.hide()
      }
    })

    this.form.on('submit', e => {
      e.preventDefault()
      return this.submit()
    })

    $('#membership_for_gift').click(() => {
      this.saveFormData()
    })
    $('#membership_for_me').click(() => {
      this.restoreForm()
    })
  }

  saveFormData() {
    let exceptFields = [
      '[photo]',
      '_token',
    ]
    let shouldSkip = false
    this.formDataTmp = this.form
    this.form.find(':input').each((key, value) => {
      shouldSkip = false

      exceptFields.every((except) => {
        if (value.name.indexOf(except) >= 0) {
          shouldSkip = true
          return false
        }
      })

      if (shouldSkip === false) {
        this.formDataTmp[value.name] = value.value
        value.value = ''
      }
    })
  }

  restoreForm() {
    let savedValue
    this.form.find(':input').each((key, value) => {
      savedValue = this.formDataTmp?.[value.name]
      if (savedValue !== undefined && savedValue !== '') {
        value.value = savedValue
      }
    })
  }
  submit() {
    this.emailsList = []
    let hasInvalidEmails = false
    const partnerEmailInput = $('input[name=\'partner[email]\']')
    this.emailsList.push($('#personal').is(':checked') ? $('#email').val() : $('#recovery_email').val())
    if (!this.validateEmail(partnerEmailInput.get(0))) {
      return false
    }

    $('input[type=email][name^=\'junior\']').each((_, input) => {
      hasInvalidEmails = hasInvalidEmails ? hasInvalidEmails : !this.validateEmail(input)
    })

    if (hasInvalidEmails) {
      return false
    }

    if ($('#billing_is_not_needed').is(':checked')) {
      this.form.find('.billing-required-input').each((key, input) => {
        input.disabled = true
      })
    } else {
      this.form.find('.billing-required-input').each((key, input) => {
        input.disabled = false
      })
    }

    const requestUrl = $(this.form).attr('action')
    const submitBtn = $('#bookingStepThreeSubmit')
    submitBtn.prop('disabled', true)
    submitBtn.addClass('loading')

    axios.post(requestUrl, new FormData(this.form.get(0))).then(response => {
      window.location.replace(response.data.url)
    }).catch(({response}) => {
      new bs5t.Toast({
        body: response.data.message, className: 'border-0 bg-danger text-white', btnCloseWhite: true,
      }).show()

      submitBtn.prop('disabled', false)
      submitBtn.removeClass('loading')
    })
  }

  validateEmail(input) {

    if (!input) {
      return true
    }

    const email = $(input).val()

    if (email) {
      if (this.emailsList.includes(email)) {
        input.setCustomValidity('Unique email ID is required for each member.')
        input.reportValidity()
        setTimeout(() => {
          input.setCustomValidity('')
        }, 5000)
        return false
      } else {
        this.emailsList.push(email)
        return true
      }
    }
  }

  childDatePicker(input, years) {
    this.createDatePicker(input, moment().subtract(years, 'years').startOf('month'), moment())
  }

  createDatePicker(input, minDate, maxDate) {
    input.daterangepicker({
      singleDatePicker: true, showDropdowns: true, autoApply: true, autoUpdateInput: false, locale: {
        format: 'DD MMMM YYYY',
      },
      minDate: minDate,
      maxDate: maxDate,
      startDate: maxDate,
    })
  }

  handleInput(input) {
    // actually event doesn't trigger
    // I have modified sources in /resources/assets/js/vendor/daterangepicker:1407
    input.on('apply.daterangepicker', (e, picker) => {
      $(e.target).val(picker.startDate.format('DD MMMM YYYY'))
      $(e.target).next().val(picker.startDate.format('YYYY-MM-DD'))
    })
  }
}

window.BookingStepThree = BookingStepThree
