const enableOptions = document.getElementById('enable-fraud-gateways')
const btnAddNewFraudPreference = document.getElementById('addNewFraudPreference')
const selectNewFraudPreference = document.getElementById('selectNewFraudPreference')
const formCountriesPreferencesCont = document.getElementById('formFraudPreferencesCont')
const countryPreferenceTemplate = document.getElementById('countryPreferenceTemplate')

const changedElements = [document.getElementById('global-gateways-box')]
changedElements.push(btnAddNewFraudPreference)
changedElements.push(selectNewFraudPreference)

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

class FraudPreference {
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
    this.btnDeleteCountryPref.addEventListener('click', (evt) => {
      request(
        'delete-fraud-pref',
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
    })

    this.btnSaveCountryPref = this.container.querySelector('.btn-save-country-pref')
    this.btnSaveCountryPref.addEventListener('click', (evt) => {
      request(
        'save-fraud-pref',
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
    })

    if (isLicensed) {
      this.elementsToChange = [this.container.querySelector('.country-gateway-prefs-select')]
      this.elementsToChange.push(this.container.querySelector('.btn-save-country-pref'))
      this.elementsToChange.push(this.container.querySelector('.btn-remove-country-pref'))

      this.elementsToChange.forEach((element) => {
        element.disabled = !enableOptions.checked
      })

      enableOptions.addEventListener('change', () => {
        this.elementsToChange.forEach((element) => {
          element.disabled = !enableOptions.checked
        })
      })
    }
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
    new FraudPreference(pref, pref.dataset.countryCode)
  }
}

btnAddNewFraudPreference.addEventListener('click', evt => {
  const countryCode = selectNewFraudPreference.value

  if (
    countryCode !== '' &&
    !FraudPreference.doesPrefAlreadyExists(countryCode)
  ) {
    const country = countries.find(country => country.code === countryCode).name
    const container = countryPreferenceTemplate.cloneNode(true)
    const gateways = []

    const pref = new FraudPreference(container, countryCode, country)

    request(
      'save-fraud-pref',
      { countryCode, gateways },
      () => { btnAddNewFraudPreference.disabled = true },
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
        btnAddNewFraudPreference.disabled = false
      }
    )
  }
})

if (isLicensed) {
  enableOptions.addEventListener('change', () => {
    changedElements.forEach((element) => {
      element.disabled = !enableOptions.checked
    })
  })

  changedElements.forEach((element) => {
    element.disabled = !enableOptions.checked
  })
}
