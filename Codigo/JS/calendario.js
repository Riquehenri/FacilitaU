// ========================================
// ARQUIVO JAVASCRIPT DO CALENDÁRIO
// ========================================
// Este arquivo contém toda a lógica do frontend (lado do cliente)
// Responsável por interações do usuário, requisições AJAX e manipulação do DOM

// ========================================
// VARIÁVEIS GLOBAIS
// ========================================
// Variáveis que podem ser acessadas por qualquer função no arquivo

let diaSelecionado = null // Elemento HTML do dia atualmente selecionado
let dataSelecionada = null // Data no formato YYYY-MM-DD do dia selecionado

// URL para requisições AJAX (comunicação com o backend)
const ajaxEndpoint = "calendario-ajax.php"

// ========================================
// TEXTOS INFORMATIVOS PARA RECORRÊNCIA
// ========================================
// Objetos que contêm textos explicativos para cada tipo de recorrência
// Estes textos são exibidos quando o usuário seleciona uma opção

// Textos para planejamentos de estudos
const textosRecorrencia = {
  nao: "O evento será criado apenas para o dia selecionado.",
  diario: "O evento aparecerá todos os dias a partir da data selecionada.",
  semanal: "O evento aparecerá toda semana no mesmo dia da semana.",
  mensal: "O evento aparecerá todo mês no mesmo dia do mês.",
  anual: "O evento aparecerá todo ano na mesma data.",
}

// Textos para avisos (professores/coordenadores)
const textosRecorrenciaAviso = {
  nao: "O aviso será criado apenas para o dia selecionado.",
  semanal: "O aviso aparecerá toda semana no mesmo dia da semana.",
  mensal: "O aviso aparecerá todo mês no mesmo dia do mês.",
  anual: "O aviso aparecerá todo ano na mesma data.",
}

// ========================================
// INICIALIZAÇÃO QUANDO O DOM ESTIVER CARREGADO
// ========================================
// DOMContentLoaded = evento que dispara quando o HTML foi completamente carregado
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM carregado, inicializando eventos...")

  // ========================================
  // EVENT LISTENERS PARA MUDANÇA DE RECORRÊNCIA - PLANEJAMENTOS
  // ========================================
  // Buscar todos os radio buttons de repetição para planejamentos
  const radiosRepetir = document.querySelectorAll('input[name="repetir"]')

  // Adicionar listener para cada radio button
  radiosRepetir.forEach((radio) => {
    radio.addEventListener("change", function () {
      // Quando o valor mudar, atualizar o texto informativo
      const infoTexto = document.getElementById("info-texto")
      if (infoTexto) {
        // this.value = valor do radio button selecionado
        infoTexto.textContent = textosRecorrencia[this.value]
      }
    })
  })

  // ========================================
  // EVENT LISTENERS PARA MUDANÇA DE RECORRÊNCIA - AVISOS
  // ========================================
  const radiosRepetirAviso = document.querySelectorAll('input[name="repetir-aviso"]')
  radiosRepetirAviso.forEach((radio) => {
    radio.addEventListener("change", function () {
      const infoTextoAviso = document.getElementById("info-texto-aviso")
      if (infoTextoAviso) {
        infoTextoAviso.textContent = textosRecorrenciaAviso[this.value]
      }
    })
  })

  // ========================================
  // EVENT LISTENERS PARA FORMULÁRIOS
  // ========================================
  // Formulário de planejamento de estudos
  const formPlanejamento = document.getElementById("form-planejamento")
  if (formPlanejamento) {
    // Quando o formulário for submetido, chamar função específica
    formPlanejamento.addEventListener("submit", submeterFormularioPlanejamento)
  }

  // Formulário de avisos
  const formAviso = document.getElementById("form-aviso")
  if (formAviso) {
    formAviso.addEventListener("submit", submeterFormularioAviso)
  }

  // ========================================
  // EVENT LISTENER PARA FECHAR MODAL CLICANDO FORA
  // ========================================
  window.addEventListener("click", (e) => {
    // Se clicou no fundo do modal (elemento com classe "modal")
    if (e.target.classList.contains("modal")) {
      fecharModal() // Fechar o modal
    }
  })

  // ========================================
  // EVENT LISTENER PARA NAVEGAÇÃO POR TECLADO
  // ========================================
  document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") {
      // Seta esquerda: ir para mês anterior
      const btnAnterior = document.querySelector(".navegacao .btn-nav:first-child")
      if (btnAnterior) btnAnterior.click()
    } else if (e.key === "ArrowRight") {
      // Seta direita: ir para próximo mês
      const btnProximo = document.querySelector(".navegacao .btn-nav:last-child")
      if (btnProximo) btnProximo.click()
    } else if (e.key === "Escape") {
      // ESC: fechar modal
      fecharModal()
    }
  })
})

// ========================================
// FUNÇÕES DE NAVEGAÇÃO
// ========================================

// Função para navegar entre meses
function navegarMes(mes, ano) {
  // Redirecionar para a mesma página com novos parâmetros de mês e ano
  window.location.href = `?mes=${mes}&ano=${ano}`
}

// Função chamada quando um dia é clicado
function selecionarDia(dia, mes, ano) {
  // Remove seleção anterior (se houver)
  if (diaSelecionado) {
    diaSelecionado.classList.remove("selecionado")
  }

  // Seleciona novo dia
  // event.target = elemento que foi clicado
  // closest('.dia') = busca o elemento pai mais próximo com classe 'dia'
  const elementoDia = event.target.closest(".dia")
  elementoDia.classList.add("selecionado") // Adicionar classe CSS para destacar
  diaSelecionado = elementoDia // Guardar referência do elemento
  dataSelecionada = elementoDia.dataset.data // Obter data do atributo data-data

  console.log("Dia selecionado:", dataSelecionada)

  // Mostrar detalhes do dia selecionado
  mostrarDetalhesDia(dia, mes, ano, dataSelecionada)
}

// Função para exibir detalhes de um dia específico
function mostrarDetalhesDia(dia, mes, ano, data) {
  // Array com nomes dos meses em português
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

  // Obter elementos do DOM
  const infoInicial = document.getElementById("info-inicial")
  const diaDetalhado = document.getElementById("dia-detalhado")
  const tituloDia = document.getElementById("titulo-dia")

  // Esconder informação inicial e mostrar detalhes do dia
  if (infoInicial) infoInicial.style.display = "none"
  if (diaDetalhado) diaDetalhado.classList.add("ativo")

  // Atualizar título com a data selecionada
  if (tituloDia) tituloDia.textContent = `${dia} de ${meses[mes - 1]} de ${ano}`

  // Buscar eventos do dia selecionado
  buscarEventos(data)
}

// ========================================
// FUNÇÕES DE API (COMUNICAÇÃO COM BACKEND)
// ========================================

// Função assíncrona para buscar eventos de uma data específica
async function buscarEventos(data) {
  console.log("Buscando eventos para:", data)

  try {
    // Fazer requisição AJAX para o backend
    const response = await fetch(ajaxEndpoint, {
      method: "POST", // Método HTTP POST para enviar dados
      headers: {
        "Content-Type": "application/json", // Informar que estamos enviando JSON
      },
      body: JSON.stringify({
        // Converter objeto JavaScript para JSON
        acao: "buscar_eventos", // Ação que queremos executar no backend
        data: data, // Data para buscar eventos
      }),
    })

    console.log("Response status:", response.status)

    // Verificar se a resposta HTTP foi bem-sucedida
    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status}`)
    }

    // Obter texto da resposta
    const responseText = await response.text()
    console.log("Response text:", responseText)

    let resultado
    try {
      // Tentar converter texto JSON em objeto JavaScript
      resultado = JSON.parse(responseText)
    } catch (parseError) {
      // Se não conseguir fazer parse do JSON, mostrar erro
      console.error("Erro ao fazer parse do JSON:", parseError)
      console.error("Resposta recebida:", responseText)
      mostrarAlerta("Erro: Resposta inválida do servidor", "error")
      return // Sair da função
    }

    // Verificar se a operação foi bem-sucedida
    if (resultado.success) {
      exibirEventos(resultado.eventos) // Mostrar eventos na interface
    } else {
      mostrarAlerta("Erro ao carregar eventos: " + resultado.message, "error")
    }
  } catch (error) {
    // Capturar qualquer erro de rede ou conexão
    console.error("Erro na requisição:", error)
    mostrarAlerta("Erro de conexão: " + error.message, "error")
  }
}

// Função para exibir eventos na interface do usuário
function exibirEventos(eventos) {
  // Obter container onde os eventos serão exibidos
  const container = document.getElementById("eventos-container")
  if (!container) return // Se não encontrar o container, sair

  // Limpar conteúdo anterior
  container.innerHTML = ""

  // Se não há eventos, mostrar mensagem
  if (eventos.length === 0) {
    container.innerHTML = '<p style="text-align: center; color: #666; margin: 20px 0;">Nenhum evento neste dia.</p>'
    return
  }

  // Loop através de cada evento e criar HTML
  eventos.forEach((evento) => {
    // Criar elemento div para o evento
    const eventoDiv = document.createElement("div")
    eventoDiv.className = `evento-item ${evento.tipo}` // Classes CSS baseadas no tipo

    // Começar com o título do evento
    let conteudo = `<div class="evento-titulo">${evento.titulo}</div>`

    // Verificar se é planejamento de estudos
    if (evento.tipo === "planejamento") {
      // Adicionar informações de horário
      conteudo += `<div class="evento-info">
                ${evento.horario_inicio} - ${evento.horario_fim}
            </div>`

      // Mostrar tipo de recorrência se não for "nao"
      if (evento.tipo_recorrencia && evento.tipo_recorrencia !== "nao") {
        const tiposTexto = {
          diario: "🔄 Diário",
          semanal: "📅 Semanal",
          mensal: "📆 Mensal",
          anual: "🗓️ Anual",
        }
        conteudo += `<div class="evento-recorrencia">${tiposTexto[evento.tipo_recorrencia] || ""}</div>`
      }

      // Se o usuário pode editar, mostrar botão de remoção
      if (evento.pode_editar) {
        conteudo += `<button class="btn-remover" onclick="removerPlanejamento(${evento.id})">✕</button>`
      }
    } else {
      // É um aviso - mostrar descrição e autor
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

    // Definir HTML do evento e adicionar ao container
    eventoDiv.innerHTML = conteudo
    container.appendChild(eventoDiv)
  })
}

// ========================================
// FUNÇÕES DE MODAL (JANELAS POPUP)
// ========================================

// Função para abrir modal baseado no tipo
function abrirModal(tipo) {
  console.log("Abrindo modal:", tipo)

  if (tipo === "planejamento") {
    const modal = document.getElementById("modal-planejamento")
    if (modal) modal.style.display = "block" // Mostrar modal
  } else {
    const modal = document.getElementById("modal-aviso")
    if (modal) modal.style.display = "block"
  }
}

// Função para fechar todos os modais
function fecharModal() {
  // Obter elementos dos modais e formulários
  const modalPlanejamento = document.getElementById("modal-planejamento")
  const modalAviso = document.getElementById("modal-aviso")
  const formPlanejamento = document.getElementById("form-planejamento")
  const formAviso = document.getElementById("form-aviso")
  const infoTexto = document.getElementById("info-texto")
  const infoTextoAviso = document.getElementById("info-texto-aviso")

  // Esconder modais
  if (modalPlanejamento) modalPlanejamento.style.display = "none"
  if (modalAviso) modalAviso.style.display = "none"

  // Resetar formulários (limpar campos)
  if (formPlanejamento) formPlanejamento.reset()
  if (formAviso) formAviso.reset()

  // Resetar textos informativos para o padrão
  if (infoTexto) infoTexto.textContent = textosRecorrencia["nao"]
  if (infoTextoAviso) infoTextoAviso.textContent = textosRecorrenciaAviso["nao"]
}

// ========================================
// FUNÇÕES DE REMOÇÃO
// ========================================

// Função assíncrona para remover planejamento
async function removerPlanejamento(id) {
  // Confirmar com o usuário antes de remover
  if (confirm("Tem certeza que deseja remover este planejamento? (Isso removerá toda a série de repetições)")) {
    try {
      // Fazer requisição AJAX para remover
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
        buscarEventos(dataSelecionada) // Atualizar lista de eventos
        setTimeout(() => location.reload(), 1500) // Recarregar página após 1.5s
      } else {
        mostrarAlerta("Erro: " + resultado.message, "error")
      }
    } catch (error) {
      console.error("Erro na requisição:", error)
      mostrarAlerta("Erro de conexão: " + error.message, "error")
    }
  }
}

// ========================================
// FUNÇÕES DE SUBMISSÃO DE FORMULÁRIOS
// ========================================

// Função assíncrona para submeter formulário de planejamento
async function submeterFormularioPlanejamento(e) {
  e.preventDefault() // Prevenir comportamento padrão do formulário (recarregar página)

  console.log("Submetendo formulário de planejamento...")

  // Verificar se um dia foi selecionado
  if (!dataSelecionada) {
    mostrarAlerta("Por favor, selecione um dia primeiro", "error")
    return
  }

  // Obter valor do radio button selecionado para repetição
  const repetirRadio = document.querySelector('input[name="repetir"]:checked')
  const repetir = repetirRadio ? repetirRadio.value : "nao"

  // Montar objeto com dados do formulário
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
    // Fazer requisição AJAX
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
      fecharModal() // Fechar modal
      buscarEventos(dataSelecionada) // Atualizar eventos
      setTimeout(() => location.reload(), 2000) // Recarregar após 2s
    } else {
      mostrarAlerta("Erro: " + resultado.message, "error")
    }
  } catch (error) {
    console.error("Erro na requisição:", error)
    mostrarAlerta("Erro de conexão: " + error.message, "error")
  }
}

// Função assíncrona para submeter formulário de aviso
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

// ========================================
// FUNÇÃO DE ALERTAS
// ========================================

// Função para mostrar mensagens de alerta ao usuário
function mostrarAlerta(mensagem, tipo) {
  const container = document.getElementById("alert-container")
  if (!container) return

  // Criar elemento de alerta
  const alerta = document.createElement("div")
  alerta.className = `alert ${tipo}` // Classes CSS: alert success ou alert error
  alerta.textContent = mensagem
  alerta.style.display = "block"

  // Limpar alertas anteriores e adicionar novo
  container.innerHTML = ""
  container.appendChild(alerta)

  // Remover alerta automaticamente após 5 segundos
  setTimeout(() => {
    alerta.style.display = "none"
  }, 5000)
}

// ========================================
// PONTOS DE EXPANSÃO FUTURA:
// ========================================

/* 
1. VALIDAÇÃO AVANÇADA DE FORMULÁRIOS:
   - Validação em tempo real
   - Máscaras de entrada
   - Validação de conflitos de horário
   - Sugestões inteligentes

2. INTERFACE MAIS RICA:
   - Drag & drop de eventos
   - Redimensionamento de eventos
   - Visualização de tooltip
   - Animações suaves

3. CACHE E PERFORMANCE:
   - Cache local de eventos
   - Lazy loading de meses
   - Debounce em pesquisas
   - Service Workers para offline

4. RECURSOS AVANÇADOS:
   - Undo/Redo de ações
   - Seleção múltipla de dias
   - Cópia de eventos
   - Templates de eventos

5. INTEGRAÇÃO COM APIS:
   - Sincronização com Google Calendar
   - Integração com sistemas externos
   - Webhooks para notificações
   - API REST para mobile

6. ACESSIBILIDADE:
   - Navegação por teclado completa
   - Suporte a leitores de tela
   - Alto contraste
   - Zoom e redimensionamento

7. PERSONALIZAÇÃO:
   - Temas customizáveis
   - Layout configurável
   - Atalhos personalizados
   - Preferências do usuário

8. COLABORAÇÃO:
   - Eventos compartilhados
   - Comentários em eventos
   - Aprovação de eventos
   - Histórico de mudanças

9. MOBILE E RESPONSIVIDADE:
   - Touch gestures
   - Swipe para navegação
   - Interface adaptativa
   - App mobile híbrido

10. ANALYTICS E INSIGHTS:
    - Tracking de uso
    - Métricas de produtividade
    - Relatórios visuais
    - Sugestões baseadas em padrões
*/
