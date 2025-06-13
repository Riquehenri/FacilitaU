const apiKey = "SUA_API"; // Substitua por sua chave de API da OpenAI

document.addEventListener("DOMContentLoaded", () => {
  const messageForm = document.getElementById("message-form");
  const messageInput = document.getElementById("message-input");
  const messagesContainer = document.getElementById("messages-container");
  const welcomeMessage = document.getElementById("welcome-message");
  const newChatButton = document.querySelector(".new-chat-btn");

  // Obtenha usuario_id e tipo_usuario do backend (via PHP embutido)
  const usuarioId = "<?php echo isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : ''; ?>";
  const tipoUsuario = "<?php echo isset($_SESSION['tipo']) ? $_SESSION['tipo'] : ''; ?>";

  // Verifica se o usuário está autenticado
  if (!usuarioId || !tipoUsuario) {
    addMessage("Erro: Usuário não autenticado. Faça login novamente.", "bot");
    return;
  }

  // Prompt do sistema para a OpenAI
  const systemPrompt = `
Você é um assistente virtual da Facilita U, uma plataforma para gestão acadêmica. Sua função é ajudar usuários (estudantes, professores, coordenadores) a criar avisos, eventos e planejamentos de estudos no banco de dados via endpoints PHP. Siga estas diretrizes:

1. **Interpretação de Comandos**:
   - Identifique intenções do usuário, como criar um aviso, evento (tarefa/evento) ou planejamento de estudos.
   - Extraia informações como título, descrição, data, horário, tipo de recorrência, etc.
   - Se informações estiverem faltando, peça esclarecimentos.
   - Considere o tipo de usuário:
     - Estudantes: podem criar planejamentos e eventos/tarefas.
     - Professores/Coordenadores: podem criar avisos.
   - Valide datas (formato YYYY-MM-DD) e horários (HH:MM:SS).

2. **Respostas**:
   - Retorne respostas no formato JSON: { "action": "[ação]", "endpoint": "[URL]", "method": "[método]", "parameters": { ... }, "message": "[mensagem para o usuário]" }.
   - Ações possíveis:
     - "execute": Chamar um endpoint PHP com os parâmetros fornecidos.
     - "clarify": Pedir mais informações ao usuário (ex.: { "action": "clarify", "message": "Por favor, informe a data do evento." }).
     - "text": Resposta genérica sem ação (ex.: { "action": "text", "message": "Não posso ajudar com isso." }).
   - Confirme ações com mensagens amigáveis (ex.: "Aviso sobre reunião criado!").

3. **Endpoints Disponíveis**:
   - **Avisos** (professores/coordenadores):
     - Endpoint: "cadastrar_aviso.php"
     - Método: POST
     - Parâmetros: tipo_aviso ('aviso' ou 'oportunidade'), titulo, descricao, data_inicial, tipo_recorrencia ('nao', 'semanal', 'mensal', 'anual').
   - **Planejamentos** (estudantes):
     - Endpoint: "planejamento_estudos.php"
     - Método: POST
     - Parâmetros: dia_semana ('segunda', 'terca', etc.), horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia ('nao', 'diario', 'semanal', 'mensal', 'anual').
   - **Eventos/Tarefas** (estudantes):
     - Endpoint: "calendario-ajax.php"
     - Método: POST
     - Ação: "criar_planejamento"
     - Parâmetros: atividade, horario_inicio, duracao (minutos), data, repetir ('nao', 'diario', 'semanal', 'mensal', 'anual').

4. **Contexto do Banco de Dados**:
   - Tabela Avisos: Campos usuario_id, tipo_aviso, titulo, descricao, data_publicacao, data_inicial, tipo_recorrencia, ativo.
   - Tabela Planejamento_Estudos: Campos usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia, ativo.
   - Tabela Tarefas_Eventos: Campos usuario_id, titulo, descricao, data, tipo ('tarefa' ou 'evento').

5. **Exemplos de Comandos**:
   - "Criar um aviso sobre reunião na sexta-feira às 10h" (professor/coordenador):
     - { "action": "execute", "endpoint": "cadastrar_aviso.php", "method": "POST", "parameters": { "tipo_aviso": "aviso", "titulo": "Reunião Geral", "descricao": "Reunião às 10h", "data_inicial": "2025-06-20", "tipo_recorrencia": "nao" }, "message": "Aviso sobre reunião criado!" }
   - "Planejar estudo de física toda segunda das 14h às 16h" (estudante):
     - { "action": "execute", "endpoint": "planejamento_estudos.php", "method": "POST", "parameters": { "dia_semana": "segunda", "horario_inicio": "14:00:00", "horario_fim": "16:00:00", "atividade": "Estudar Física", "data_inicial": "2025-06-16", "tipo_recorrencia": "semanal" }, "message": "Planejamento de física criado!" }
   - "Adicionar uma tarefa para estudar amanhã às 14h" (estudante):
     - { "action": "execute", "endpoint": "calendario-ajax.php", "method": "POST", "parameters": { "acao": "criar_planejamento", "atividade": "Estudar", "horario_inicio": "14:00:00", "duracao": 60, "data": "2025-06-13", "repetir": "nao" }, "message": "Tarefa para amanhã criada!" }

6. **Regras**:
   - Use o fuso horário do Brasil (-03:00).
   - Verifique permissões antes de sugerir ações.
   - Se o comando for ambíguo, peça esclarecimentos.
   - Para respostas genéricas, sugira ações relacionadas (ex.: "Não sei sobre o tempo, mas posso criar um aviso ou planejamento.").

Processe a mensagem do usuário e retorne a resposta no formato JSON.
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
          "X-Requested-With": "XMLHttpRequest", // Indica requisição AJAX
        },
        body: JSON.stringify(parameters),
      });

      const result = await response.json();
      if (result.success) {
        return result.message || "Ação realizada com sucesso!";
      } else {
        return result.message || "Erro ao processar a ação no servidor.";
      }
    } catch (error) {
      console.error("Erro ao chamar endpoint:", error);
      return "Erro ao conectar com o servidor. Tente novamente.";
    }
  }

  // Função para obter resposta da IA
  async function getBotResponse(userMessage) {
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
            {
              role: "system",
              content: systemPrompt,
            },
            {
              role: "user",
              content: `Usuário: ${tipoUsuario}, ID: ${usuarioId}. Mensagem: ${userMessage}`,
            },
          ],
          max_tokens: 1024,
          temperature: 0.5,
        }),
      });

      const data = await response.json();
      setTimeout(() => {
        typingIndicator.remove();

        if (data.choices && data.choices.length > 0) {
          let reply;
          try {
            reply = JSON.parse(data.choices[0].message.content.trim());
          } catch (e) {
            console.error("Erro ao parsear resposta da IA:", e);
            addMessage("Erro ao processar resposta da IA. Tente novamente.", "bot");
            return;
          }

          if (reply.action === "execute") {
            // Chama o endpoint PHP
            callBackendApi(reply.endpoint, reply.method, reply.parameters).then((backendMessage) => {
              addMessage(`${reply.message} ${backendMessage}`, "bot");
            });
          } else {
            addMessage(reply.message, "bot");
          }
        } else {
          fallbackBotResponse(userMessage);
        }
      }, 3000); // Simula atraso de digitação
    } catch (error) {
      console.error("Erro com a API da OpenAI:", error);
      setTimeout(() => {
        typingIndicator.remove();
        fallbackBotResponse(userMessage);
      }, 3000);
    }
  }

  // Função de fallback para respostas genéricas
  function fallbackBotResponse(userMessage) {
    const lower = userMessage.toLowerCase();
    let botText = "Desculpe, não entendi. Pode reformular sua pergunta?";

    if (lower.includes("olá") || lower.includes("oi") || lower.includes("bom dia")) {
      botText = "Olá! Como posso ajudar você com a Facilita U hoje?";
    } else if (lower.includes("matrícula") || lower.includes("inscricao")) {
      botText = "Para informações sobre matrícula, consulte o portal do aluno ou a secretaria.";
    } else if (lower.includes("horário") || lower.includes("aulas")) {
      botText = "Seu horário de aulas está disponível na seção 'Meu Horário' no portal.";
    } else if (lower.includes("biblioteca")) {
      botText = "A biblioteca funciona das 8h às 22h de segunda a sexta.";
    } else if (lower.includes("obrigado") || lower.includes("agradecido")) {
      botText = "De nada! Se precisar de algo mais, estou por aqui.";
    }

    addMessage(botText, "bot");
  }

  // Evento de envio do formulário
  messageForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const userText = messageInput.value.trim();

    if (userText) {
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