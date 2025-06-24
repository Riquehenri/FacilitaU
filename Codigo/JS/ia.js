document.addEventListener("DOMContentLoaded", () => {
  const messageForm = document.getElementById("message-form");
  const messageInput = document.getElementById("message-input");
  const messagesContainer = document.getElementById("messages-container");
  const welcomeMessage = document.getElementById("welcome-message");
  const newChatButton = document.querySelector(".new-chat-btn");

  // Obtenha usuario_id e tipo_usuario do backend (via PHP embutido)
  const usuarioId = "<?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : ''; ?>";
  const tipoUsuario = "<?php echo isset($_SESSION['tipo']) ? $_SESSION['tipo'] : ''; ?>";

  // Log para depuração
  console.log("usuarioId:", usuarioId, "tipoUsuario:", tipoUsuario);

  // Verifica se o usuário está autenticado
  if (!usuarioId || !tipoUsuario) {
    addMessage("Erro: Usuário não autenticado. Verifique sua sessão e faça login novamente.", "bot");
    return;
  }

  // Função para adicionar mensagens ao container
  function addMessage(text, sender) {
    const messageElement = document.createElement("div");
    messageElement.classList.add("message", sender);
    messageElement.textContent = text;
    messagesContainer.appendChild(messageElement);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    if (welcomeMessage && !welcomeMessage.hidden) {
      welcomeMessage.style.display = "none";
    }
  }

  // Função para chamar endpoints PHP via AJAX
  async function callBackendApi(endpoint, method, parameters) {
    try {
      const response = await fetch(endpoint, {
        method: method,
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ ...parameters, usuario_id: usuarioId }),
      });

      const result = await response.json();
      console.log("Resposta do backend:", result); // Log para depuração
      return result.success
        ? result.message || "Ação realizada com sucesso!"
        : result.message || "Erro ao processar a ação no servidor.";
    } catch (error) {
      console.error("Erro ao chamar endpoint:", error);
      return "Erro ao conectar com o servidor. Tente novamente.";
    }
  }

  // Função para processar comandos do usuário (substitui a IA)
  function processUserCommand(userMessage) {
    const lowerMessage = userMessage.toLowerCase();
    const response = { action: "text", message: "Desculpe, não entendi o comando. Tente algo como 'criar aviso', 'planejar estudo' ou 'adicionar tarefa'." };

    // Regex para extrair datas (ex.: amanhã, sexta-feira, 23/06/2025)
    const dateRegex = /(amanhã|hoje|\d{1,2}\/\d{1,2}\/\d{4}|[a-z]+-feira)/i;
    const timeRegex = /(\d{1,2}(?::\d{2})?(?:\s*(?:h|horas))?)/i;
    const durationRegex = /(\d+\s*(?:minutos|horas))/i;
    const recurrenceRegex = /(todo\s*(?:dia|semana|mês|ano)|diario|semanal|mensal|anual)/i;

    // Função para converter data textual em YYYY-MM-DD
    function parseDate(dateStr) {
      const today = new Date();
      today.setHours(today.getHours() - 3); // Ajuste para fuso horário do Brasil (-03:00)
      if (dateStr === "hoje") {
        return today.toISOString().split("T")[0];
      } else if (dateStr === "amanhã") {
        today.setDate(today.getDate() + 1);
        return today.toISOString().split("T")[0];
      } else if (dateStr.match(/\d{1,2}\/\d{1,2}\/\d{4}/)) {
        const [day, month, year] = dateStr.split("/");
        return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
      } else if (dateStr.match(/[a-z]+-feira/)) {
        const days = ["domingo", "segunda", "terca", "quarta", "quinta", "sexta", "sabado"];
        const targetDay = days.indexOf(dateStr.replace("-feira", ""));
        if (targetDay === -1) return null;
        const currentDay = today.getDay();
        let daysToAdd = (targetDay - currentDay + 7) % 7;
        if (daysToAdd === 0) daysToAdd = 7;
        today.setDate(today.getDate() + daysToAdd);
        return today.toISOString().split("T")[0];
      }
      return null;
    }

    // Função para converter horário em HH:MM:SS
    function parseTime(timeStr) {
      if (!timeStr) return "00:00:00";
      const [hours, minutes = "00"] = timeStr.replace(/h|horas/i, "").split(":");
      return `${hours.padStart(2, "0")}:${minutes.padStart(2, "0")}:00`;
    }

    // 1. Criar Aviso (professores/coordenadores)
    if (lowerMessage.includes("criar aviso") && ["professor", "coordenador"].includes(tipoUsuario)) {
      if (!lowerMessage.includes("sobre")) {
        return { action: "clarify", message: "Por favor, informe o título ou descrição do aviso. Ex.: 'Criar aviso sobre reunião amanhã às 10h'" };
      }

      const title = userMessage.match(/sobre\s+(.+?)(?:\s+na\s+|\s+às\s+|$)/i)?.[1] || "Aviso Geral";
      const dateMatch = userMessage.match(dateRegex)?.[0];
      const timeMatch = userMessage.match(timeRegex)?.[0];
      const recurrenceMatch = userMessage.match(recurrenceRegex)?.[0];
      const date = dateMatch ? parseDate(dateMatch.toLowerCase()) : null;
      const time = timeMatch ? parseTime(timeMatch) : null;
      const recurrence = recurrenceMatch
        ? recurrenceMatch.includes("diario") ? "diario"
        : recurrenceMatch.includes("semanal") ? "semanal"
        : recurrenceMatch.includes("mensal") ? "mensal"
        : recurrenceMatch.includes("anual") ? "anual"
        : "nao"
        : "nao";

      if (!date) {
        return { action: "clarify", message: "Por favor, informe a data do aviso. Ex.: 'amanhã', 'sexta-feira' ou '23/06/2025'" };
      }

      return {
        action: "execute",
        endpoint: "cadastrar_aviso.php",
        method: "POST",
        parameters: {
          tipo_aviso: "aviso",
          titulo: title,
          descricao: time ? `Aviso: ${title} às ${time.slice(0, 5)}` : `Aviso: ${title}`,
          data_inicial: date,
          tipo_recorrencia: recurrence,
        },
        message: `Aviso "${title}" criado!`,
      };
    } else if (lowerMessage.includes("criar aviso") && tipoUsuario === "estudante") {
      return { action: "text", message: "Estudantes não podem criar avisos. Deseja criar uma tarefa ou planejamento?" };
    }

    // 2. Planejar Estudo (estudantes)
    if (lowerMessage.includes("planejar estudo") && tipoUsuario === "estudante") {
      const activityMatch = userMessage.match(/de\s+(.+?)(?:\s+todo|\s+na\s+|\s+das\s+|$)/i)?.[1];
      const dayMatch = userMessage.match(/(segunda|terca|quarta|quinta|sexta|sabado|domingo)/i)?.[0];
      const timeRangeMatch = userMessage.match(/das\s+(\d{1,2}(?::\d{2})?)\s*(?:h|horas)?\s*(?:às|ate)\s+(\d{1,2}(?::\d{2})?)\s*(?:h|horas)?/i);
      const recurrenceMatch = userMessage.match(recurrenceRegex)?.[0];
      const dateMatch = userMessage.match(dateRegex)?.[0];

      if (!activityMatch) {
        return { action: "clarify", message: "Por favor, informe a atividade do estudo. Ex.: 'Planejar estudo de física'" };
      }
      if (!dayMatch && !dateMatch) {
        return { action: "clarify", message: "Por favor, informe o dia da semana ou a data inicial. Ex.: 'toda segunda' ou 'a partir de 23/06/2025'" };
      }
      if (!timeRangeMatch) {
        return { action: "clarify", message: "Por favor, informe o horário do estudo. Ex.: 'das 14h às 16h'" };
      }

      const activity = activityMatch.trim();
      const startTime = parseTime(timeRangeMatch[1]);
      const endTime = parseTime(timeRangeMatch[2]);
      const day = dayMatch ? dayMatch.toLowerCase() : null;
      const date = dateMatch ? parseDate(dateMatch.toLowerCase()) : new Date().toISOString().split("T")[0];
      const recurrence = recurrenceMatch
        ? recurrenceMatch.includes("diario") ? "diario"
        : recurrenceMatch.includes("semanal") ? "semanal"
        : recurrenceMatch.includes("mensal") ? "mensal"
        : recurrenceMatch.includes("anual") ? "anual"
        : "nao"
        : "semanal";

      return {
        action: "execute",
        endpoint: "planejamento_estudos.php",
        method: "POST",
        parameters: {
          dia_semana: day || "segunda",
          horario_inicio: startTime,
          horario_fim: endTime,
          atividade: activity,
          data_inicial: date,
          tipo_recorrencia: recurrence,
        },
        message: `Planejamento de estudo para ${activity} criado!`,
      };
    } else if (lowerMessage.includes("planejar estudo") && !["estudante"].includes(tipoUsuario)) {
      return { action: "text", message: "Apenas estudantes podem planejar estudos. Deseja criar um aviso?" };
    }

    // 3. Adicionar Tarefa/Evento (estudantes)
    if ((lowerMessage.includes("adicionar tarefa") || lowerMessage.includes("criar evento")) && tipoUsuario === "estudante") {
      const activityMatch = userMessage.match(/(?:tarefa|evento)\s+(?:para|de)\s+(.+?)(?:\s+na\s+|\s+às\s+|$)/i)?.[1];
      const dateMatch = userMessage.match(dateRegex)?.[0];
      const timeMatch = userMessage.match(timeRegex)?.[0];
      const durationMatch = userMessage.match(durationRegex)?.[0];
      const recurrenceMatch = userMessage.match(recurrenceRegex)?.[0];

      if (!activityMatch) {
        return { action: "clarify", message: "Por favor, informe a descrição da tarefa ou evento. Ex.: 'Adicionar tarefa para estudar matemática'" };
      }
      if (!dateMatch) {
        return { action: "clarify", message: "Por favor, informe a data da tarefa/evento. Ex.: 'amanhã' ou '23/06/2025'" };
      }
      if (!timeMatch) {
        return { action: "clarify", message: "Por favor, informe o horário da tarefa/evento. Ex.: 'às 14h'" };
      }

      const activity = activityMatch.trim();
      const date = parseDate(dateMatch.toLowerCase());
      const time = parseTime(timeMatch);
      const duration = durationMatch ? parseInt(durationMatch.match(/\d+/)[0]) * (durationMatch.includes("horas") ? 60 : 1) : 60;
      const recurrence = recurrenceMatch
        ? recurrenceMatch.includes("diario") ? "diario"
        : recurrenceMatch.includes("semanal") ? "semanal"
        : recurrenceMatch.includes("mensal") ? "mensal"
        : recurrenceMatch.includes("anual") ? "anual"
        : "nao"
        : "nao";

      return {
        action: "execute",
        endpoint: "calendario-ajax.php",
        method: "POST",
        parameters: {
          acao: "criar_planejamento",
          atividade: activity,
          horario_inicio: time,
          duracao: duration,
          data: date,
          repetir: recurrence,
        },
        message: `Tarefa/evento "${activity}" criado!`,
      };
    } else if ((lowerMessage.includes("adicionar tarefa") || lowerMessage.includes("criar evento")) && !["estudante"].includes(tipoUsuario)) {
      return { action: "text", message: "Apenas estudantes podem criar tarefas ou eventos. Deseja criar um aviso?" };
    }

    return response;
  }

  // Evento de envio do formulário
  messageForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const userText = messageInput.value.trim();

    if (userText) {
      addMessage(userText, "user");
      const response = processUserCommand(userText);

      if (response.action === "execute") {
        callBackendApi(response.endpoint, response.method, response.parameters).then((backendMessage) => {
          addMessage(`${response.message} ${backendMessage}`, "bot");
        });
      } else {
        addMessage(response.message, "bot");
      }

      messageInput.value = "";
      messageInput.focus();
    } else {
      messageInput.style.border = "1px solid red";
      setTimeout(() => {
        messageInput.style.border = "";
      }, 2000);
    }
  });

  // Evento do botão "Nova Conversa"
  newChatButton.addEventListener("click", () => {
    messagesContainer.innerHTML = "";
    if (welcomeMessage) {
      welcomeMessage.style.display = "flex";
    }
    messageInput.value = "";
    messageInput.focus();
    console.log("Novo chat iniciado!");
  });

  // Oculta mensagem de boas-vindas se houver mensagens
  if (messagesContainer.children.length > 0 && welcomeMessage) {
    welcomeMessage.style.display = "none";
  }
});