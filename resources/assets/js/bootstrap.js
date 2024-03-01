import { Fancybox } from '@fancyapps/ui'
import 'bootstrap/js/dist/collapse'
import 'bootstrap/js/dist/modal'
import 'bootstrap/js/dist/offcanvas'
import bs5 from './plugins/bs5-toast'
import Tooltip from 'bootstrap/js/dist/tooltip'
import axios from 'axios'
import jQuery from 'jquery/dist/jquery.min'
import LazyLoad from 'vanilla-lazyload'
import * as Sentry from '@sentry/browser'
import { BrowserTracing } from '@sentry/tracing'

Fancybox.defaults.autoFocus = false
Fancybox.defaults.closeButton = 'outside'
Fancybox.defaults.fullScreen = false
Fancybox.defaults.dragToClose = false

Fancybox.bind('[data-fancybox]', {
  Thumbs: false,
  fullscreen: false,
})

window.Fancybox = Fancybox

// import lozad from 'lozad'

window.bs5t = bs5

const tooltipTriggerList = document.querySelectorAll(
  '[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(
  tooltipTriggerEl => new Tooltip(tooltipTriggerEl))

window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
window.axios.defaults.baseURL = siteUrl

window.$ = jQuery
window.jQuery = jQuery

new LazyLoad()

if (sentryDns) {
  Sentry.init({
    dsn: sentryDns,
    release: sentryReleaseVersion,
    integrations: [new BrowserTracing()],

    tracesSampleRate: 0,

    denyUrls: [

      // Facebook flakiness
      /graph\.facebook\.com/i,
      // Facebook blocked
      /connect\.facebook\.net\/en_US\/all\.js/i,
      // Woopra flakiness
      /eatdifferent\.com\.woopra-ns\.com/i,
      /static\.woopra\.com\/js\/woopra\.js/i,
      // Chrome extensions
      /extensions\//i,
      /^chrome:\/\//i,
      /^chrome-extension:\/\//i,
      // Other plugins
      /127\.0\.0\.1:4001\/isrunning/i, // Cacaoweb
      /webappstoolbarba\.texthelp\.com\//i,
      /metrics\.itunes\.apple\.com\.edgesuite\.net\//i,

      // Yandex metrika
      /mc\.yandex\.ru/i,

      // Tawkto
      /embed\.tawk\.to/i,

      // Cloudfront
      /cloudfront\.net/i,

      // Tabby widget
      /checkout\.tabby\.ai/i,

      // Safari extensions
      /^safari-web-extension:\/\//i,
      /^:\/\/hidden\/\/\//i,

    ],

    ignoreErrors: [
      // Random plugins/extensions
      'top.GLOBALS',
      // See: http://blog.errorception.com/2012/03/tale-of-unfindable-js-error.html
      'originalCreateNotification',
      'canvas.contentDocument',
      'MyApp_RemoveAllHighlights',
      'http://tt.epicplay.com',
      'Can\'t find variable: ZiteReader',
      'jigsaw is not defined',
      'ComboSearch is not defined',
      'http://loading.retry.widdit.com/',
      'atomicFindClose',
      // Facebook borked
      'fb_xd_fragment',
      // ISP "optimizing" proxy - `Cache-Control: no-transform` seems to
      // reduce this. (thanks @acdha)
      // See http://stackoverflow.com/questions/4113268
      'bmi_SafeAddOnload',
      'EBCallBackMessageReceived',
      // See http://toolbar.conduit.com/Developer/HtmlAndGadget/Methods/JSInjection.aspx
      'conduitPage',

      // ignore safari webkit
      /.*@webkit-masked-url.*/,

      'Drip_',
    ],

  })
}
