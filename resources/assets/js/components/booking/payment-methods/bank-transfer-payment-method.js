import BasePaymentMethod from './base-payment-method'
import ClipboardJS from 'clipboard'

export default class BankTransferPaymentMethod extends BasePaymentMethod {
  constructor (blockName) {
    super(blockName)
    $(() => {
      $(this.blockName + ' span').each((index, item) => {
        const element = $(item)
        element.attr('title', 'Click to copy')
        element.attr('data-clipboard-text', element.html())
      })
      new ClipboardJS(this.blockName + ' span')
    })
  }
}
