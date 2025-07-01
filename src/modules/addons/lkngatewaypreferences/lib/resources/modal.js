class Modal {
  constructor() {
    this.modal = document.getElementById('lkngatewayprefs-modal')
    this.closeButton = document.getElementById('close-button')
    this.modalTitle = this.modal.querySelector('.modal-title')
    this.modalBody = this.modal.querySelector('.modal-body p')
  }

  show(title, body) {
    this.modalTitle.innerHTML = title
    this.modalBody.innerHTML = body

    $('#lkngatewayprefs-modal').modal()
  }

  showAndUpdate(title, body) {
    this.modalTitle.innerHTML = title
    this.modalBody.innerHTML = body

    $('#lkngatewayprefs-modal').modal()

    this.closeButton.addEventListener('click', evt => {
      window.location.reload()
    })
  }
}
