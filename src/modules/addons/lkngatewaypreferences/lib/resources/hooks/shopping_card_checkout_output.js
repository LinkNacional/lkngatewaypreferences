const gatewaysButtons = document.querySelector('#paymentGatewaysContainer').children[1].children

Array.from(gatewaysButtons).forEach(removeUnallowedOptions)

/**
 * @param {HTMLLabelElement} label
 */
function removeUnallowedOptions(label) {
  const labelText = label.innerText.trim()

  if (unallowedGateways.includes(labelText)) {
    label.remove()
  }
}

const firstAvailableGateway = document.querySelectorAll("input[name=paymentmethod]")[0]
firstAvailableGateway.checked = true
