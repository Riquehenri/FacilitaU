const apiKey = "SUA_API"; // Substitua por sua chave de API da OpenAI

document.addEventListener("DOMContentLoaded", () => {
  const messageForm = document.getElementById("message-form");
  const messageInput = document.getElementById("message-input");
  const messagesContainer = document.getElementById("messages-container");
  const welcomeMessage = document.getElementById("welcome-message");
  const newChatButton = document.querySelector(".new-chat-btn");

  // Obtenha usuario_id e tipo_usuario do backend (via PHP embutido)
  const usuarioId = "<?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : ''; ?>";
  const tipoUsuario = "<?php echo isset($_SESSION['tipo']) ? $_SESSION['tipo'] : ''; ?>".toLowerCase();

  // Normalizar tipoUsuario para aceitar variações
  const isEstudante = ["estudante", "aluno", "student"].includes(tipoUsuario);
  const isProfessorOrCoordenador = ["professor", "coordenador"].includes(tipoUsuario);

  // Log para depuração
  console.log("usuarioId:", usuarioId, "tipoUsuario:", tipoUsuario);
  addMessage(`Debug: Você está logado com usuarioId=${usuarioId}, tipoUsuario=${tipoUsuario}`, "bot");

  // Verifica se o usuário está autenticado
  if (!usuarioId || !tipoUsuario) {
    addMessage("Erro: Usuário não autenticado. Verifique sua sessão e faça login novamente.", "bot");
    return;
  }

  // Verifica se a chave da API está configurada
  if (!apiKey || apiKey === "SUA_API") {
    addMessage("Erro: Chave da API da OpenAI não configurada. Contate o administrador.", "bot");
    return;
  }

  // Prompt do sistema revisado
  const systemPrompt = `
Você é um assistente virtual da Facilita U, uma plataforma para gestão acadêmica. Sua função é ajudar usuários (estudantes, professores, coordenadores) a criar avisos, eventos ou planejamentos de estudos via endpoints PHP. Siga estas diretrizes:

1. **Interpretação de Comandos**:
   - Identifique a intenção: criar aviso (professores/coordenadores), evento/tarefa (estudantes), ou planejamento de estudos (estudantes).
   - Extraia informações: título, descrição, data (YYYY-MM-DD), horário (HH:MM:SS), tipo de recorrência, etc.
   - Se informações estiverem faltando, peça esclarecimentos via ação "clarify".
   - Valide permissões com base no tipo de usuário (estudante, professor, coordenador). Aceite "estudante", "aluno", ou "student" como estudante.
   - Use o fuso horário do Brasil (-03:00).

2. **Formato de Resposta**:
   - Sempre retorne um JSON válido: { "action": string, "endpoint": string, "method": string, "parameters": object, "message": string }.
   - Ações:
     - "execute": Chamar endpoint PHP com parâmetros.
     - "clarify": Pedir mais informações.
     - "text": Resposta genérica sem ação.
   - Confirme ações com mensagens amigáveis.

3. **Endpoints Disponíveis**:
   - **Avisos** (professores/coordenadores):
     - Endpoint: "cadastrar_aviso.php"
     - Método: POST
     - Parâmetros: tipo_aviso ('aviso', 'oportunidade'), titulo, descricao, data_inicial, tipo_recorrencia ('nao', 'semanal', 'mensal', 'anual'), ativo (boolean).
   - **Planejamentos** (estudantes):
     - Endpoint: "planejamento_estudos.php"
     - Método: POST
     - Parâmetros: dia_semana ('segunda', 'terca', etc.), horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia ('nao', 'diario', 'semanal', 'mensal', 'anual'), ativo (boolean).
   - **Eventos/Tarefas** (estudantes):
     - Endpoint: "calendario-ajax.php"
     - Método: POST
     - Ação: "criar_planejamento"
     - Parâmetros: atividade, horario_inicio, duracao (minutos), data, repetir ('nao', 'diario', 'semanal', 'mensal', 'anual').

4. **Validações**:
   - Verifique permissões antes de sugerir ações.
   - Valide formatos de data (YYYY-MM-DD) e horário (HH:MM:SS).
   - Se o comando for ambíguo, retorne { "action": "clarify", "message": "Por favor, especifique [detalhe faltante]." }.
   - Se o usuário não tiver permissão, retorne { "action": "text", "message": "Você não tem permissão para essa ação. Tipo de usuário: [tipoUsuario]." }.

5. **Exemplos**:
   - Entrada: "Criar um aviso sobre reunião na sexta-feira às 10h" (professor)
     - Saída: { "action": "execute", "endpoint": "cadastrar_aviso.php", "method": "POST", "parameters": { "tipo_aviso": "aviso", "titulo": "Reunião Geral", "descricao": "Reunião às 10h", "data_inicial": "2025-06-27", "tipo_recorrencia": "nao", "ativo": true }, "message": "Aviso sobre reunião criado!" }
   - Entrada: "Planejar estudo de física toda segunda das 14h às 16h" (estudante)
     - Saída: { "action": "execute", "endpoint": "planejamento_estudos.php", "method": "POST", "parameters": { "dia_semana": "segunda", "horario_inicio": "14:00:00", "horario_fim": "16:00:00", "atividade": "Estudar Física", "data_inicial": "2025-06-30", "tipo_recorrencia": "semanal", "ativo": true }, "message": "Planejamento de física criado!" }
   - Entrada: "Adicionar tarefa para amanhã às 14h" (estudante)
     - Saída: { "action": "execute", "endpoint": "calendario-ajax.php", "method": "POST", "parameters": { "acao": "criar_planejamento", "atividade": "Estudar", "horario_inicio": "14:00:00", "duracao": 60, "data": "2025-06-24", "repetir": "nao" }, "message": "Tarefa para amanhã criada!" }
   - Entrada: "Criar aviso" (estudante)
     - Saída: { "action": "text", "message": "Estudantes não podem criar avisos. Deseja criar uma tarefa ou planejamento? Tipo de usuário: estudante" }
`;

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
      console.log("Resposta do backend:", result);
      return result.success
        ? result.message || "Ação realizada com sucesso!"
        : result.message || `Erro ao processar a ação no servidor. Detalhes: ${JSON.stringify(result)}`;
    } catch (error) {
      console.error("Erro ao chamar endpoint:", error);
      return "Erro ao conectar com o servidor. Tente novamente.";
    }
  }

  // Função para obter resposta da IA
  async function getBotResponse(userMessage) {
    let retries = 3;
    while (retries > 0) {
      try {
        const typingIndicator = document.createElement("div");
        typingIndicator.classList.add("message", "bot", "typing");
        typingIndicator.textContent = "Digitando...";
        messagesContainer.appendChild(typingIndicator);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        const response = await fetch("https://api.openai.com/v1/chat/completions", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${apiKey}`,
          },
          body: JSON.stringify({
            model: "gpt-3.5-turbo",
            messages: [
              { role: "system", content: systemPrompt },
              { role: "user", content: `Usuário: ${tipoUsuario}, ID: ${usuarioId}. Mensagem: ${userMessage}` },
            ],
            max_tokens: 700,
            temperature: 0.5,
          }),
        });

        typingIndicator.remove();

        if (!response.ok) {
          if (response.status === 429 && retries > 1) {
            await new Promise(resolve => setTimeout(resolve, 1000));
            retries--;
            continue;
          }
          throw new Error(`Erro na API da OpenAI: ${response.status}`);
        }

        const data = await response.json();
        console.log("Resposta da API:", data);

        if (data.choices && data.choices.length > 0) {
          let reply;
          try {
            reply = JSON.parse(data.choices[0].message.content.trim());
            if (!reply.action || !reply.message) {
              throw new Error("Resposta da IA não contém action ou message.");
            }
          } catch (e) {
            console.error("Erro ao parsear resposta da IA:", e);
            addMessage("Erro ao processar resposta da IA. Tente novamente.", "bot");
            return;
          }

          localStorage.setItem(`${tipoUsuario}:${userMessage}`, JSON.stringify(reply));

          if (reply.action === "execute") {
            const backendMessage = await callBackendApi(reply.endpoint, reply.method, reply.parameters);
            addMessage(`${reply.message} ${backendMessage}`, "bot");
          } else {
            addMessage(reply.message, "bot");
          }
          return;
        } else {
          throw new Error("Nenhuma escolha retornada pela API.");
        }
      } catch (error) {
        console.error("Erro com a API da OpenAI:", error);
        typingIndicator.remove();
        addMessage(`Erro ao conectar com a API da OpenAI: ${error.message}. Tente novamente.`, "bot");
        return;
      }
    }
    addMessage("Limite de requisições atingido. Tente novamente mais tarde.", "bot");
  }

  // Função de fallback para respostas genéricas
  function fallbackBotResponse(userMessage) {
    const lower = userMessage.toLowerCase();
    let botText = `Desculpe, não entendi. Tente algo como 'criar aviso', 'planejar estudo' ou 'adicionar tarefa'. Tipo de usuário: ${tipoUsuario}`;

    if (lower.includes("olá") || lower.includes("oi") || lower.includes("bom dia")) {
      botText = `Olá! Como posso ajudar com a Facilita U? Tente criar um aviso, evento ou planejamento. Tipo de usuário: ${tipoUsuario}`;
    } else if (lower.includes("matrícula") || lower.includes("inscricao")) {
      botText = `Para matrículas, acesse o portal do aluno ou contate a secretaria. Posso ajudar com algo mais? Tipo de usuário: ${tipoUsuario}`;
    } else if (lower.includes("horário") || lower.includes("aulas")) {
      botText = `Consulte seus horários no portal. Quer planejar um estudo ou criar uma tarefa? Tipo de usuário: ${tipoUsuario}`;
    } else if (lower.includes("biblioteca")) {
      botText = `A biblioteca funciona das 8h às 22h (seg-sex). Posso criar um evento para você? Tipo de usuário: ${tipoUsuario}`;
    } else if (lower.includes("obrigado") || lower.includes("agradecido")) {
      botText = `De nada! Estou aqui para ajudar com avisos, eventos ou planejamentos. Tipo de usuário: ${tipoUsuario}`;
    }

    addMessage(botText, "bot");
  }

  // Evento de envio do formulário
  messageForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const userText = messageInput.value.trim();

    if (userText) {
      // Validação inicial de permissões
      if (userText.toLowerCase().includes("criar aviso") && !isProfessorOrCoordenador) {
        addMessage(`Estudantes não podem criar avisos. Deseja criar uma tarefa ou planejamento? Tipo de usuário: ${tipoUsuario}`, "bot");
        messageInput.value = "";
        messageInput.focus();
        return;
      }

      // Verifica cache
      const cachedResponse = localStorage.getItem(`${tipoUsuario}:${userText}`);
      if (cachedResponse) {
        const reply = JSON.parse(cachedResponse);
        if (reply.action === "execute") {
          callBackendApi(reply.endpoint, reply.method, reply.parameters).then((backendMessage) => {
            addMessage(`${reply.message} ${backendMessage}`, "bot");
          });
        } else {
          addMessage(reply.message, "bot");
        }
        messageInput.value = "";
        messageInput.focus();
        return;
      }

      addMessage(userText, "user");
      messageInput.value = "";
      messageInput.focus();
      getBotResponse(userText);
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