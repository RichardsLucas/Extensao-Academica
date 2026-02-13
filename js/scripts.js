// Funções auxiliares JavaScript

function confirmarDelecao(mensagem) {
  return confirm(mensagem || "Tem certeza que deseja deletar este item?")
}

function abrirModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.add("show")
  }
}

function fecharModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.remove("show")
  }
}

document.addEventListener("click", (event) => {
  if (event.target.classList.contains("modal")) {
    event.target.classList.remove("show")
  }
})

function validarFormulario(formId) {
  const form = document.getElementById(formId)
  if (!form) return true

  const inputs = form.querySelectorAll("input[required], select[required], textarea[required]")
  let valido = true

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.style.borderColor = "#ef4444"
      valido = false
    } else {
      input.style.borderColor = ""
    }
  })

  return valido
}

document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      if (!validarFormulario(form.id)) {
        e.preventDefault()
      }
    })
  })
})
