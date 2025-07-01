/* globals unallowedGateways paymentMethodsSelectName */

const paymentMethodsSelect = document.getElementsByName(paymentMethodsSelectName)

paymentMethodsSelect.forEach(select => { removeUnallowedOptions(select) })

/**
 * @param {HTMLSelectElement} select
 */
function removeUnallowedOptions (select) {
  const optionsElementsToRemove = []

  for (let optionIndex = 0; optionIndex < select.options.length; optionIndex++) {
    const option = select.options[optionIndex]

    if (unallowedGateways.includes(option.value)) {
      optionsElementsToRemove.push(option)
    }
  }

  optionsElementsToRemove.forEach(option => {
    select.removeChild(option)
  })
}
