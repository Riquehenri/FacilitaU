document.addEventListener("DOMContentLoaded", () => {
  const messageForm = document.getElementById("message-form");
  const messageInput = document.getElementById("message-input");
  const messagesContainer = document.getElementById("messages-container");
  const welcomeMessage = document.getElementById("welcome-message");
  const newChatButton = document.querySelector(".new-chat-btn");

  function addMessage(text, sender) {
    const messageElement = document.createElement("div");
    messageElement.classList.add("message", sender); 
    messageElement.textContent = text;
    messagesContainer.appendChild(messageElement);

    // Auto-scroll para a última mensagem
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Esconde a mensagem de boas-vindas se ainda estiver visível
    if (welcomeMessage && !welcomeMessage.hidden) {
      welcomeMessage.style.display = "none"; 
    }
  }

  // Função para simular resposta do bot
  function simulateBotResponse(userMessage) {
    // Respostas simples baseadas na entrada do usuário(retirar dps da api)
    let botText = "Desculpe, não entendi. Pode reformular sua pergunta?"; // Resposta padrão

    const lowerCaseMessage = userMessage.toLowerCase();

    if (
      lowerCaseMessage.includes("olá") ||
      lowerCaseMessage.includes("oi") ||
      lowerCaseMessage.includes("bom dia")
    ) {
      botText = "Olá! Como posso ajudar você com a Facilita U hoje?";
    } else if (
      lowerCaseMessage.includes("matrícula") ||
      lowerCaseMessage.includes("inscricao")
    ) {
      botText =
        "Para informações sobre matrícula, você pode consultar a seção 'Acadêmico' no portal do aluno ou entrar em contato com a secretaria.";
    } else if (
      lowerCaseMessage.includes("horário") ||
      lowerCaseMessage.includes("aulas")
    ) {
      botText =
        "Seu horário de aulas geralmente está disponível no portal do aluno. Verifique a seção 'Meu Horário'.";
    } else if (lowerCaseMessage.includes("biblioteca")) {
      botText =
        "A biblioteca funciona de segunda a sexta, das 8h às 22h. Você pode pesquisar o acervo online.";
    } else if (
      lowerCaseMessage.includes("obrigado") ||
      lowerCaseMessage.includes("agradecido")
    ) {
      botText = "De nada! Se precisar de mais alguma coisa, é só perguntar.";
    }

    // Simula um pequeno atraso para a resposta do bot
    setTimeout(() => {
      addMessage(botText, "bot");
    }, 800); // Atraso de 800ms
  }

  // Evento de envio do formulário
  messageForm.addEventListener("submit", (event) => {
    event.preventDefault(); 

    const userText = messageInput.value.trim();

    if (userText) {
      addMessage(userText, "user"); // Adiciona a mensagem do usuário
      messageInput.value = ""; // Limpa o campo de entrada
      messageInput.focus(); 
      simulateBotResponse(userText); 
    }
  });

  // Evento do botão "Novo Chat"
  newChatButton.addEventListener("click", () => {
    messagesContainer.innerHTML = ""; // Limpa as mensagens
    if (welcomeMessage) {
      welcomeMessage.style.display = "flex"; // Mostra a mensagem de boas-vindas novamente
    }
    messageInput.value = ""; 
    // Parte do novo chat(Futuras melhorias)
    console.log("Novo chat iniciado!");
  });

  // Inicialmente, esconde o welcome message se já houver histórico (simulação)
  // ou se decidir iniciar com um chat ativo (remova se quiser sempre iniciar com welcome)
  if (messagesContainer.children.length > 0 && welcomeMessage) {
    welcomeMessage.style.display = "none";
  }
});
