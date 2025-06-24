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

  // Log para depuração
  console.log("usuarioId:", usuarioId, "tipoUsuario:", tipoUsuario);
  addMessage(`Debug: Logado como usuarioId=${usuarioId}, tipoUsuario=${tipoUsuario}`, "bot");

  // Verifica autenticação
  if (!usuarioId || !tipoUsuario) {
    addMessage("Erro: Usuário não autenticado. Faça login novamente.", "bot");
    return;
  }

  // Verifica chave da API
  if (!apiKey || apiKey === "SUA_API") {
    addMessage("Erro: Chave da API da OpenAI não configurada. Contate o administrador.", "bot");
    return;
  }

  // Prompt otimizado para a IA
  const systemPrompt = `
Você é um assistente da Facilita U, baseado no banco de dados facilitau_db. Ajude usuários (estudantes, professores, coordenadores) a criar avisos, planejamentos de estudos e tarefas/eventos via endpoints PHP. Siga estas regras:

1. **Interpretação**:
   - Identifique intenções: "criar aviso" (professores/coordenadores), "planejar estudo" ou "adicionar tarefa/evento" (estudantes).
   - Extraia: título, descrição, data (YYYY-MM-DD), horário (HH:MM:SS), recorrência.
   - Valide permissões: "estudante" para planejamentos/tarefas, "professor" ou "coordenador" para avisos.
   - Use fuso horário do Brasil (-03:00).

2. **Resposta**:
   - Retorne JSON: { "action": string, "endpoint": string, "method": string, "parameters": object, "message": string }.
   - Ações: "execute" (chamar endpoint), "clarify" (pedir detalhes), "text" (resposta simples).

3. **Endpoints**:
   - **Avisos** (professores/coordenadores):
     - Endpoint: "cadastrar_aviso.php"
     - Método: POST
     - Parâmetros: tipo_aviso ('aviso', 'oportunidade'), titulo, descricao, data_inicial, tipo_recorrencia ('nao', 'semanal', 'mensal', 'anual'), ativo (true).
   - **Planejamentos** (estudantes):
     - Endpoint: "planejamento_estudos.php"
     - Método: POST
     - Parâmetros: dia_semana ('segunda', 'terca', etc.), horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia ('nao', 'diario', 'semanal', 'mensal', 'anual'), ativo (true).
   - **Tarefas/Eventos** (estudantes):
     - Endpoint: "calendario-ajax.php"
     - Método: POST
     - Parâmetros: acao ('criar_planejamento'), titulo, descricao, data, tipo ('tarefa', 'evento').

4. **Validações**:
   - Verifique permissões: rejeite "criar aviso" para "estudante".
   - Valide formatos: data (YYYY-MM-DD), horário (HH:MM:SS).
   - Se faltar info, use "clarify" com exemplo.
   - Se sem permissão, use "text" com "Você não tem permissão. Tipo: [tipoUsuario]".

5. **Exemplos**:
   - "Criar aviso sobre reunião amanhã às 10h" (professor)
     - { "action": "execute", "endpoint": "cadastrar_aviso.php", "method": "POST", "parameters": { "tipo_aviso": "aviso", "titulo": "Reunião", "descricao": "Reunião às 10h", "data_inicial": "2025-06-24", "tipo_recorrencia": "nao", "ativo": true }, "message": "Aviso criado!" }
   - "Planejar estudo de matemática toda terça das 14h às 16h" (estudante)
     - { "action": "execute", "endpoint": "planejamento_estudos.php", "method": "POST", "parameters": { "dia_semana": "terca", "horario_inicio": "14:00:00", "horario_fim": "16:00:00", "atividade": "Matemática", "data_inicial": "2025-06-24", "tipo_recorrencia": "semanal", "ativo": true }, "message": "Planejamento criado!" }
   - "Adicionar tarefa para amanhã às 15h" (estudante)
     - { "action": "execute", "endpoint": "calendario-ajax.php", "method": "POST", "parameters": { "acao": "criar_planejamento", "titulo": "Estudar", "data": "2025-06-24", "tipo": "tarefa" }, "message": "Tarefa criada!" }
   - "Criar aviso" (estudante)
     - { "action": "text", "message": "Você não tem permissão. Tipo: estudante" }
`;

  // Função para adicionar mensagens
  function addMessage(text, sender) {
    const messageElement = document.createElement("div");
    messageElement.classList.add("message", sender);
    messageElement.textContent = text;
    messagesContainer.appendChild(messageElement);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    if (welcomeMessage) welcomeMessage.style.display = "none";
  }

  // Função para chamar endpoints PHP
  async function callBackendApi(endpoint, method, parameters) {
    try {
      const response = await fetch(endpoint, {
        method: method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...parameters, usuario_id: usuarioId }),
      });
      const result = await response.json();
      console.log("Backend Response:", result);
      return result.success ? result.message : `Erro: ${result.message || "Falha no servidor"}`;
    } catch (error) {
      console.error("API Error:", error);
      return "Erro de conexão com o servidor.";
    }
  }

  // Função para obter resposta da IA
  async function getBotResponse(userMessage) {
    let retries = 3;
    while (retries > 0) {
      try {
        const typing = document.createElement("div");
        typing.classList.add("message", "bot", "typing");
        typing.textContent = "Digitando...";
        messagesContainer.appendChild(typing);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        const response = await fetch("https://api.openai.com/v1/chat/completions", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${apiKey}`,
          },
          body: JSON.stringify({
            model: "gpt-3.5-turbo",
            messages: [{ role: "system", content: systemPrompt }, { role: "user", content: `Usuário: ${tipoUsuario}, ID: ${usuarioId}. Mensagem: ${userMessage}` }],
            max_tokens: 500,
            temperature: 0.5,
          }),
        });

        typing.remove();

        if (!response.ok) {
          if (response.status === 429 && retries > 1) {
            await new Promise(r => setTimeout(r, 1000));
            retries--;
            continue;
          }
          throw new Error(`Erro API: ${response.status}`);
        }

        const data = await response.json();
        console.log("API Response:", data);

        if (data.choices?.length) {
          const reply = JSON.parse(data.choices[0].message.content.trim());
          if (!reply.action || !reply.message) throw new Error("Resposta inválida da IA");
          localStorage.setItem(`${tipoUsuario}:${userMessage}`, JSON.stringify(reply));
          if (reply.action === "execute") {
            const backendMsg = await callBackendApi(reply.endpoint, reply.method, reply.parameters);
            addMessage(`${reply.message} ${backendMsg}`, "bot");
          } else {
            addMessage(reply.message, "bot");
          }
          return;
        }
        throw new Error("Nenhuma resposta válida da IA");
      } catch (error) {
        console.error("Erro na IA:", error);
        typing.remove();
        addMessage(`Erro: ${error.message}. Tente novamente.`, "bot");
        return;
      }
    }
    addMessage("Limite de requisições atingido. Tente mais tarde.", "bot");
  }

  // Evento de envio
  messageForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const userText = messageInput.value.trim();
    if (userText) {
      addMessage(userText, "user");
      if (userText.toLowerCase().includes("criar aviso") && tipoUsuario !== "professor" && tipoUsuario !== "coordenador") {
        addMessage(`Você não tem permissão para criar avisos. Tipo: ${tipoUsuario}`, "bot");
      } else {
        getBotResponse(userText);
      }
      messageInput.value = "";
      messageInput.focus();
    } else {
      messageInput.style.border = "1px solid red";
      setTimeout(() => (messageInput.style.border = ""), 2000);
    }
  });

  // Nova conversa
  newChatButton.addEventListener("click", () => {
    messagesContainer.innerHTML = "";
    if (welcomeMessage) welcomeMessage.style.display = "flex";
    messageInput.value = "";
    messageInput.focus();
    console.log("Nova conversa iniciada");
  });
});