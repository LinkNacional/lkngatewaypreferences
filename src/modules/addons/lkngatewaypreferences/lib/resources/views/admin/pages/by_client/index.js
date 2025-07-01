/* globals Modal */

const formClientPrefs = document.getElementById('form-client-prefs')

const modal = new Modal()

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

class ClientPreference {
  constructor(container, clientId, clientName) {
    this.clientId = clientId

    this.container = container
    this.container.style.display = 'block'
    this.container.id = `${clientId}-pref`

    this.clientName = this.container.querySelector('.client-name')

    if (this.clientName.innerHTML === '') {
      this.clientName.innerHTML = clientName
    }

    this.selectGateways = this.container.querySelector('.select-gateways')

    this.btnDeleteClientPref = this.container.querySelector('.btn-delete-client-pref')
    this.btnDeleteClientPref.addEventListener('click', evt => this.deletePrefBtn(evt))

    this.btnStoreClientPref = this.container.querySelector('.btn-store-client-pref')
    this.btnStoreClientPref.addEventListener('click', evt => this.savePrefBtn(evt))
  }

  deletePrefBtn(evt) {
    request(
      'delete-client-pref',
      {
        clientId: this.clientId
      },
      () => { this.btnDeleteClientPref.disabled = true },
      res => {
        if (res.success) {
          this.container.remove()

          modal.showAndUpdate(
            window.lkn.gflang.Success,
            window.lkn.gflang['The preference has been deleted.']
          )
        } else {
          modal.show(
            window.lkn.gflang['Unable to delete preference'],
            window.lkn.gflang['Please try again or check the module logs.']
          )
        }
      },
      () => {
        modal.show(
          window.lkn.gflang['Unable to delete preference'],
          window.lkn.gflang['Please try again or check the module logs.']
        )
      },
      () => { this.btnDeleteClientPref.disabled = false }
    )
  }

  savePrefBtn(evt) {
    request(
      'store-client-pref',
      {
        clientId: this.clientId,
        gateways: this.getSelectedGateways()
      },
      () => { this.btnStoreClientPref.disabled = true },
      res => {
        if (res.success) {
          modal.showAndUpdate(
            window.lkn.gflang.Success,
            window.lkn.gflang['Preferences have been saved.']
          )
        } else {
          modal.show(
            window.lkn.gflang['Unable to save preference'],
            window.lkn.gflang['Please try again or check the module logs.']
          )
        }
      },
      () => {
        modal.show(
          window.lkn.gflang['Unable to save preference'],
          window.lkn.gflang['Please try again or check the module logs.']
        )
      },
      () => { this.btnStoreClientPref.disabled = false }
    )
  }

  getSelectedGateways() {
    return getSelectedOptionsFromSelect(this.selectGateways)
  }

  addToList() {
    formClientPrefs.appendChild(this.container)
  }

  static doesPrefAlreadyExists(clientId) {
    return document.getElementById(`${clientId}-pref`) !== null
  }

  removeConfig
}

function bindCurrentPrefsToListeners() {
  const currentPrefs = formClientPrefs.children

  for (let index = 0; index < currentPrefs.length; index++) {
    const pref = currentPrefs[index]

    if (pref.classList.contains('client-preferences')) {
      new ClientPreference(pref, pref.dataset.clientId)
    }
  }
}

bindCurrentPrefsToListeners()
