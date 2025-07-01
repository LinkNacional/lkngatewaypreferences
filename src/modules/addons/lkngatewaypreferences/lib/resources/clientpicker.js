const selectClientPrefs = document.getElementById('select-client-prefs')
const selectGateways = document.getElementById('select-gateways')
const btnStoreClientPrefs = document.getElementById('btn-store-client-prefs')
const btnClose = document.getElementById('btn-close')
const btnDismiss = document.getElementById('btn-dismiss')
const feedbackCont = document.getElementById('save-prefs-feedback')
const searchInput = document.getElementById('search-input')
const searchType = document.getElementById('select-search-type')

let searchText = ''
let loaded = false
let timer

class ClientPicker {
  constructor() {
    this.clientpicker = document.getElementById('lkngatewayprefs-clientpicker')
    this.clientpickerBody = this.clientpicker.querySelector('.modal-body p')
  }

  show() {
    $('#lkngatewayprefs-clientpicker').modal()
  }
}

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

const setFeedback = (show, msg = '') => {
  if (show) {
    feedbackCont.innerHTML = msg
    feedbackCont.style.display = 'flex'
  } else {
    feedbackCont.style.display = 'none'
  }
}

btnClose.addEventListener('click', evt => {
  window.location.reload()
})

btnDismiss.addEventListener('click', evt => {
  window.location.reload()
})

/**
 * @param {HTMLSelectElement} element
 */
function getSelectedOptionsFromSelect(element) {
  return Array.from(element.options)
    .filter(option => option.selected)
    .map(option => option.value)
}

btnStoreClientPrefs.addEventListener('click', evt => {
  setFeedback(false)
  const clientId = String(selectClientPrefs.value)
  const gateways = getSelectedOptionsFromSelect(selectGateways)

  request(
    'store-client-pref',
    { clientId, gateways },
    () => { btnStoreClientPrefs.disabled = true },
    res => {
      if (res.success) {
        setFeedback(true, window.lkn.gflang['Preferences have been saved.'])
      } else {
        setFeedback(true, window.lkn.gflang['Please try again or check the module logs.'])
      }
    },
    () => {
      setFeedback(true, window.lkn.gflang['Please try again or check the module logs.'])
    },
    () => { btnStoreClientPrefs.disabled = false }
  )
})

async function getClientsByType(firstRun = true) {
  const filter = firstRun ? '' : searchType.value
  let whmcsClients = []

  const data = { searchText }

  switch (filter) {
    case '1':
      data.a = 'get-clients-by-id'

      break

    case '2':
      data.a = 'get-clients-by-email'

      break
    case '3':
      data.a = 'get-clients-by-domain'

      break
    case '4':
      data.a = 'get-clients-by-name'

      break
    default:
      data.a = 'get-all-clients'

      break
  }

  const res = await fetch(systemURL + '/modules/addons/lkngatewaypreferences/api.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
  whmcsClients = res.json()

  return whmcsClients
}

async function updateList(firstRun) {
  const whmcsClients = await getClientsByType(firstRun)

  loaded = true
  selectClientPrefs.innerHTML = ''
  selectClientPrefs.disabled = false
  btnStoreClientPrefs.disabled = false

  const shown = whmcsClients.filter(element => {
    return (!prefs.some(pref => {
      return (String(pref.client_id).valueOf() === String(element.id).valueOf())
    }))
      ? element
      : undefined
  }).filter(element => { return element !== undefined && element !== null }).slice(0, 15)

  if (shown.length > 0) {
    shown.forEach(element => {
      selectClientPrefs.innerHTML += '<option value="' + element.id + '">' + '#' + element.id + ' ' + ((searchType.value !== '2') ? (element.firstname + ' ' + element.lastname) : element.email) + '</option>'
    })
  } else {
    selectClientPrefs.innerHTML += '<option value="" disabled selected>' + window.lkn.gflang['No clients found'] + '</option>'
    selectClientPrefs.disabled = true
    btnStoreClientPrefs.disabled = true
  }
}

searchInput.addEventListener('input', value => {
  searchText = value.target.value

  loaded = false
  if (!loaded) {
    selectClientPrefs.innerHTML = '<option value="" disabled selected>' + window.lkn.gflang['Loading, please wait...'] + '</option>'
    selectClientPrefs.disabled = true
    btnStoreClientPrefs.disabled = true
  }

  clearTimeout(timer)

  timer = setTimeout(() => {
    updateList(false)
  }, 1500)
})

updateList(true)
