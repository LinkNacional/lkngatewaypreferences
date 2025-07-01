const dismissIcon = document.getElementById('lkngatewaypreferences-dismiss-icon')

dismissIcon.addEventListener('click', async () => {
  const data = { a: 'new-version-dismiss-on-admin-home' }

  fetch(systemURL + '/modules/addons/lkngatewaypreferences/api.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
})
