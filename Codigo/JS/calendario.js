// Variáveis globais
let diaSelecionado = null
let dataSelecionada = null

// URL para requisições AJAX
const ajaxEndpoint = 'calendario-ajax.php'

// Textos informativos para recorrência
const textosRecorrencia = {
  nao: "O evento será criado apenas para o dia selecionado.",
  diario: "O evento aparecerá todos os dias a partir da data selecionada.",
  semanal: "O evento aparecerá toda semana no mesmo dia da semana.",
  mensal: "O evento aparecerá todo mês no mesmo dia do mês.",
  anual: "O evento aparecerá todo ano na mesma data.",
}

const textosRecorrenciaAviso = {
  nao: "O aviso será criado apenas para o dia selecionado.",
  semanal: "O aviso aparecerá toda semana no mesmo dia da semana.",
  mensal: "O aviso aparecerá todo mês no mesmo dia do mês.",
  anual: "O aviso aparecerá todo ano na mesma data.",
}

// Inicialização quando o DOM estiver carregado
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM carregado, inicializando eventos...")

  // Event listeners para mudança de recorrência - planejamentos
  const radiosRepetir = document.querySelectorAll('input[name="repetir"]')
  radiosRepetir.forEach((radio) => {
    radio.addEventListener("change", function () {
      const infoTexto = document.getElementById("info-texto")
      if (infoTexto) {
        infoTexto.textContent = textosRecorrencia[this.value]
      }
    })
  })

  // Event listeners para mudança de recorrência - avisos
  const radiosRepetirAviso = document.querySelectorAll('input[name="repetir-aviso"]')
  radiosRepetirAviso.forEach((radio) => {
    radio.addEventListener("change", function () {
      const infoTextoAviso = document.getElementById("info-texto-aviso")
      if (infoTextoAviso) {
        infoTextoAviso.textContent = textosRecorrenciaAviso[this.value]
      }
    })
  })

  // Event listeners para formulários
  const formPlanejamento = document.getElementById("form-planejamento")
  if (formPlanejamento) {
    formPlanejamento.addEventListener("submit", submeterFormularioPlanejamento)
  }

  const formAviso = document.getElementById("form-aviso")
  if (formAviso) {
    formAviso.addEventListener("submit", submeterFormularioAviso)
  }

  // Event listener para fechar modal clicando fora
  window.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal")) {
      fecharModal()
    }
  })

  // Event listener para navegação por teclado
  document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") {
      const btnAnterior = document.querySelector(".navegacao .btn-nav:first-child")
      if (btnAnterior) btnAnterior.click()
    } else if (e.key === "ArrowRight") {
      const btnProximo = document.querySelector(".navegacao .btn-nav:last-child")
      if (btnProximo) btnProximo.click()
    } else if (e.key === "Escape") {
      fecharModal()
    }
  })
})

// Funções de navegação
function navegarMes(mes, ano) {
  window.location.href = `?mes=${mes}&ano=${ano}`
}

function selecionarDia(dia, mes, ano) {
  // Remove seleção anterior
  if (diaSelecionado) {
    diaSelecionado.classList.remove("selecionado")
  }

  // Seleciona novo dia
  const elementoDia = event.target.closest('.dia') // Usar closest para garantir que pegamos o elemento dia
  elementoDia.classList.add("selecionado")
  diaSelecionado = elementoDia
  dataSelecionada = elementoDia.dataset.data

  console.log("Dia selecionado:", dataSelecionada)

  // Mostra detalhes do dia
  mostrarDetalhesDia(dia, mes, ano, dataSelecionada)
}

function mostrarDetalhesDia(dia, mes, ano, data) {
  const meses = [
    "Janeiro",
    "Fevereiro",
    "Março",
    "Abril",
    "Maio",
    "Junho",
    "Julho",
    "Agosto",
    "Setembro",
    "Outubro",
    "Novembro",
    "Dezembro",
  ]

  const infoInicial = document.getElementById("info-inicial")
  const diaDetalhado = document.getElementById("dia-detalhado")
  const tituloDia = document.getElementById("titulo-dia")

  if (infoInicial) infoInicial.style.display = "none"
  if (diaDetalhado) diaDetalhado.classList.add("ativo")
  if (tituloDia) tituloDia.textContent = `${dia} de ${meses[mes - 1]} de ${ano}`

  // Buscar eventos do dia
  buscarEventos(data)
}

// Funções de API
async function buscarEventos(data) {
  console.log("Buscando eventos para:", data)

  try {
    const response = await fetch(ajaxEndpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        acao: "buscar_eventos",
        data: data,
      }),
    })

    console.log("Response status:", response.status)
    
    // Verificar se a resposta é válida
    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status}`)
    }

    const responseText = await response.text()
    console.log("Response text:", responseText)

    let resultado
    try {
      resultado = JSON.parse(responseText)
    } catch (parseError) {
      console.error("Erro ao fazer parse do JSON:", parseError)
      console.error("Resposta recebida:", responseText)
      mostrarAlerta("Erro: Resposta inválida do servidor", "error")
      return
    }

    if (resultado.success) {
      exibirEventos(resultado.eventos)
    } else {
      mostrarAlerta("Erro ao carregar eventos: " + resultado.message, "error")
    }
  } catch (error) {
    console.error("Erro na requisição:", error)
    mostrarAlerta("Erro de conexão: " + error.message, "error")
  }
}

function exibirEventos(eventos) {
  const container = document.getElementById("eventos-container")
  if (!container) return

  container.innerHTML = ""

  if (eventos.length === 0) {
    container.innerHTML = '<p style="text-align: center; color: #666; margin: 20px 0;">Nenhum evento neste dia.</p>'
    return
  }

  eventos.forEach((evento) => {
    const eventoDiv = document.createElement("div")
    eventoDiv.className = `evento-item ${evento.tipo}`

    let conteudo = `<div class="evento-titulo">${evento.titulo}</div>`

    if (evento.tipo === "planejamento") {
      conteudo += `<div class="evento-info">
                ${evento.horario_inicio} - ${evento.horario_fim}
            </div>`

      // Mostrar tipo de recorrência
      if (evento.tipo_recorrencia && evento.tipo_recorrencia !== "nao") {
        const tiposTexto = {
          diario: "🔄 Diário",
          semanal: "📅 Semanal",
          mensal: "📆 Mensal",
          anual: "🗓️ Anual",
        }
        conteudo += `<div class="evento-recorrencia">${tiposTexto[evento.tipo_recorrencia] || ""}</div>`
      }

      if (evento.pode_editar) {
        conteudo += `<button class="btn-remover" onclick="removerPlanejamento(${evento.id})">✕</button>`
      }
    } else {
      conteudo += `<div class="evento-info">
                ${evento.descricao}<br>
                <strong>Por:</strong> ${evento.autor} (${evento.tipo_aviso})
            </div>`

      // Mostrar tipo de recorrência para avisos
      if (evento.tipo_recorrencia && evento.tipo_recorrencia !== "nao") {
        const tiposTexto = {
          semanal: "📅 Semanal",
          mensal: "📆 Mensal",
          anual: "🗓️ Anual",
        }
        conteudo += `<div class="evento-recorrencia">${tiposTexto[evento.tipo_recorrencia] || ""}</div>`
      }
    }

    eventoDiv.innerHTML = conteudo
    container.appendChild(eventoDiv)
  })
}

// Funções de modal
function abrirModal(tipo) {
  console.log("Abrindo modal:", tipo)

  if (tipo === "planejamento") {
    const modal = document.getElementById("modal-planejamento")
    if (modal) modal.style.display = "block"
  } else {
    const modal = document.getElementById("modal-aviso")
    if (modal) modal.style.display = "block"
  }
}

function fecharModal() {
  const modalPlanejamento = document.getElementById("modal-planejamento")
  const modalAviso = document.getElementById("modal-aviso")
  const formPlanejamento = document.getElementById("form-planejamento")
  const formAviso = document.getElementById("form-aviso")
  const infoTexto = document.getElementById("info-texto")
  const infoTextoAviso = document.getElementById("info-texto-aviso")

  if (modalPlanejamento) modalPlanejamento.style.display = "none"
  if (modalAviso) modalAviso.style.display = "none"
  if (formPlanejamento) formPlanejamento.reset()
  if (formAviso) formAviso.reset()

  // Resetar textos informativos
  if (infoTexto) infoTexto.textContent = textosRecorrencia["nao"]
  if (infoTextoAviso) infoTextoAviso.textContent = textosRecorrenciaAviso["nao"]
}

// Funções de remoção
async function removerPlanejamento(id) {
  if (confirm("Tem certeza que deseja remover este planejamento? (Isso removerá toda a série de repetições)")) {
    try {
      const response = await fetch(ajaxEndpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          acao: "remover_planejamento",
          id: id,
        }),
      })

      // Verificar se a resposta é válida
      if (!response.ok) {
        throw new Error(`Erro HTTP: ${response.status}`)
      }

      const responseText = await response.text()
      console.log("Response text (remover):", responseText)

      let resultado
      try {
        resultado = JSON.parse(responseText)
      } catch (parseError) {
        console.error("Erro ao fazer parse do JSON:", parseError)
        mostrarAlerta("Erro: Resposta inválida do servidor", "error")
        return
      }

      if (resultado.success) {
        mostrarAlerta(resultado.message, "success")
        buscarEventos(dataSelecionada)
        setTimeout(() => location.reload(), 1500)
      } else {
        mostrarAlerta("Erro: " + resultado.message, "error")
      }
    } catch (error) {
      console.error("Erro na requisição:", error)
      mostrarAlerta("Erro de conexão: " + error.message, "error")
    }
  }
}

// Funções de submissão de formulários
async function submeterFormularioPlanejamento(e) {
  e.preventDefault()

  console.log("Submetendo formulário de planejamento...")

  if (!dataSelecionada) {
    mostrarAlerta("Por favor, selecione um dia primeiro", "error")
    return
  }

  const repetirRadio = document.querySelector('input[name="repetir"]:checked')
  const repetir = repetirRadio ? repetirRadio.value : "nao"

  const dados = {
    acao: "criar_planejamento",
    data: dataSelecionada,
    atividade: document.getElementById("atividade").value,
    horario_inicio: document.getElementById("horario-inicio").value,
    duracao: document.getElementById("duracao").value,
    repetir: repetir,
  }

  console.log("Dados a enviar:", dados)

  try {
    const response = await fetch(ajaxEndpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(dados),
    })

    console.log("Response status (criar):", response.status)

    // Verificar se a resposta é válida
    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status}`)
    }

    const responseText = await response.text()
    console.log("Response text (criar):", responseText)

    let resultado
    try {
      resultado = JSON.parse(responseText)
    } catch (parseError) {
      console.error("Erro ao fazer parse do JSON:", parseError)
      console.error("Resposta recebida:", responseText)
      mostrarAlerta("Erro: Resposta inválida do servidor", "error")
      return
    }

    if (resultado.success) {
      mostrarAlerta(resultado.message, "success")
      fecharModal()
      buscarEventos(dataSelecionada)
      setTimeout(() => location.reload(), 2000)
    } else {
      mostrarAlerta("Erro: " + resultado.message, "error")
    }
  } catch (error) {
    console.error("Erro na requisição:", error)
    mostrarAlerta("Erro de conexão: " + error.message, "error")
  }
}

async function submeterFormularioAviso(e) {
  e.preventDefault()

  console.log("Submetendo formulário de aviso...")

  if (!dataSelecionada) {
    mostrarAlerta("Por favor, selecione um dia primeiro", "error")
    return
  }

  const repetirRadio = document.querySelector('input[name="repetir-aviso"]:checked')
  const repetir = repetirRadio ? repetirRadio.value : "nao"

  const dados = {
    acao: "criar_aviso",
    data: dataSelecionada,
    tipo_aviso: document.getElementById("tipo-aviso").value,
    titulo: document.getElementById("titulo-aviso").value,
    descricao: document.getElementById("descricao-aviso").value,
    repetir: repetir,
  }

  console.log("Dados a enviar:", dados)

  try {
    const response = await fetch(ajaxEndpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(dados),
    })

    // Verificar se a resposta é válida
    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status}`)
    }

    const responseText = await response.text()
    console.log("Response text (criar aviso):", responseText)

    let resultado
    try {
      resultado = JSON.parse(responseText)
    } catch (parseError) {
      console.error("Erro ao fazer parse do JSON:", parseError)
      mostrarAlerta("Erro: Resposta inválida do servidor", "error")
      return
    }

    if (resultado.success) {
      mostrarAlerta(resultado.message, "success")
      fecharModal()
      buscarEventos(dataSelecionada)
      setTimeout(() => location.reload(), 2000)
    } else {
      mostrarAlerta("Erro: " + resultado.message, "error")
    }
  } catch (error) {
    console.error("Erro na requisição:", error)
    mostrarAlerta("Erro de conexão: " + error.message, "error")
  }
}

// Função de alertas
function mostrarAlerta(mensagem, tipo) {
  const container = document.getElementById("alert-container")
  if (!container) return

  const alerta = document.createElement("div")
  alerta.className = `alert ${tipo}`
  alerta.textContent = mensagem
  alerta.style.display = "block"

  container.innerHTML = ""
  container.appendChild(alerta)

  setTimeout(() => {
    alerta.style.display = "none"
  }, 5000)
}