/* globals Modal countries */

const btnAddNewCountryPreference = document.getElementById('addNewCountryPreference')
const selectNewCountryPreference = document.getElementById('selectNewCountryPreference')
const formCountriesPreferencesCont = document.getElementById('formCountriesPreferencesCont')
const countryPreferenceTemplate = document.getElementById('countryPreferenceTemplate')
const btnSaveGlobalPref = document.getElementById('btn-global-pref-save')
const selectGlobalPref = document.getElementById('select-global-pref')

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

const modal = new Modal()

class CountryPreference {
  constructor(container, countryCode, countryLabel) {
    this.countryCode = countryCode

    this.container = container
    this.container.style.display = 'block'
    this.container.id = `${countryCode}-pref`

    this.countryLabel = this.container.querySelector('.country-label')

    if (this.countryLabel.innerHTML === '') {
      this.countryLabel.innerHTML = countryLabel
    }

    this.selectCountryGateways = this.container.querySelector('.country-gateway-prefs-select')
    this.selectCountryGateways.name = `${countryCode}-allowed-gateways[]`

    this.btnDeleteCountryPref = this.container.querySelector('.btn-remove-country-pref')
    this.btnDeleteCountryPref.addEventListener('click', evt => this.deletePrefBtn(evt))

    this.btnSaveCountryPref = this.container.querySelector('.btn-save-country-pref')
    this.btnSaveCountryPref.addEventListener('click', evt => this.savePrefBtn(evt))
  }

  deletePrefBtn(evt) {
    request(
      'delete-country-pref',
      {
        countryCode: this.countryCode
      },
      () => { this.btnDeleteCountryPref.disabled = true },
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
      () => {
        this.btnDeleteCountryPref.disabled = false
      }
    )
  }

  savePrefBtn(evt) {
    request(
      'save-country-pref',
      {
        countryCode: this.countryCode,
        gateways: this.getSelectedGateways()
      },
      () => { this.btnSaveCountryPref.disabled = true },
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
      () => {
        this.btnSaveCountryPref.disabled = false
      }
    )
  }

  getSelectedGateways() {
    return getSelectedOptionsFromSelect(this.selectCountryGateways)
  }

  addToList() {
    formCountriesPreferencesCont.appendChild(this.container)
  }

  static doesPrefAlreadyExists(countryCode) {
    return document.getElementById(`${countryCode}-pref`) !== null
  }

  removeConfig
}

const currentPrefs = formCountriesPreferencesCont.children

for (let index = 0; index < currentPrefs.length; index++) {
  const pref = currentPrefs[index]

  if (pref.classList.contains('country-preference')) {
    new CountryPreference(pref, pref.dataset.countryCode)
  }
}

btnAddNewCountryPreference.addEventListener('click', evt => {
  const countryCode = selectNewCountryPreference.value

  if (
    countryCode !== '' &&
    !CountryPreference.doesPrefAlreadyExists(countryCode)
  ) {
    const country = countries.find(country => country.code === countryCode).name
    const container = countryPreferenceTemplate.cloneNode(true)
    const gateways = []

    const pref = new CountryPreference(container, countryCode, country)

    request(
      'save-country-pref',
      { countryCode, gateways },
      () => { btnAddNewCountryPreference.disabled = true },
      res => {
        if (res.success) {
          modal.showAndUpdate(
            window.lkn.gflang.Success,
            window.lkn.gflang['Preferences have been saved.']
          )

          pref.addToList()
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
      () => {
        btnAddNewCountryPreference.disabled = false
      }
    )
  }
})

btnSaveGlobalPref.addEventListener('click', evt => {
  request(
    'save-global-pref',
    { gateways: getSelectedOptionsFromSelect(selectGlobalPref) },
    () => { btnSaveGlobalPref.disabled = true },
    res => {
      if (res.success) {
        modal.show(
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
    () => { btnSaveGlobalPref.disabled = false }
  )
})
