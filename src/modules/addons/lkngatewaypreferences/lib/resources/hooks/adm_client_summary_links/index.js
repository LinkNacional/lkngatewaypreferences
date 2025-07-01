const selectClientPrefs = document.getElementById('select-client-prefs')
const btnStoreClientPrefs = document.getElementById('btn-store-client-prefs')
const feedbackCont = document.getElementById('save-prefs-feedback')

selectClientPrefs.size = selectClientPrefs.options.length

/**
 * @param {string} action
 * @param {Array} data
 * @param {Function} preRequest
 * @param {Function} onThen
 * @param {Function} onCatch
 * @param {Function} onFinally
 */
function request(action, data, preRequest, onThen, onCatch, onFinally) {
  preRequest()

  data.a = action

  fetch(systemURL + '/modules/addons/lkngatewaypreferences/api.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
    .then(res => res.json())
    .then(res => { onThen(res) })
    .catch(() => { onCatch() })
    .finally(() => { onFinally() })
}

/**
 * @param {HTMLSelectElement} element
 */
function getSelectedOptionsFromSelect(element) {
  return Array.from(element.options)
    .filter(option => option.selected)
    .map(option => option.value)
}

const setFeedback = (show, msg = '') => {
  if (show) {
    feedbackCont.innerHTML = msg
    feedbackCont.style.display = 'flex'
  } else {
    feedbackCont.style.display = 'none'
  }
}

btnStoreClientPrefs.addEventListener('click', evt => {
  try {
    if (!isPro) throw 'Not a Pro User'

    setFeedback(false)
    const clientId = (new URLSearchParams(window.location.search)).get('userid')
    const gateways = getSelectedOptionsFromSelect(selectClientPrefs)

    const preRequest = () => { btnStoreClientPrefs.disabled = true }
    const onFinally = () => { btnStoreClientPrefs.disabled = false }
    const onThen = res => {
      if (res.success) {
        setFeedback(true, window.lkn.gflang['Preferences have been saved.'])
      } else {
        setFeedback(true, window.lkn.gflang['Please try again or check the module logs.'])
      }
    }
    const onCatch = () => {
      setFeedback(true, window.lkn.gflang['Please try again or check the module logs.'])
    }

    request(
      'store-client-pref',
      { clientId, gateways },
      preRequest,
      onThen,
      onCatch,
      onFinally
    )
  } catch (e) { setFeedback(true, window.lkn.gflang[e]) }
})
