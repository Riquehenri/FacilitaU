const apiKey = "SUA_API"; // Substitua por sua chave de API da OpenAI

// Espera o carregamento completo do DOM antes de executar o script
document.addEventListener("DOMContentLoaded", () => {
  // Seleciona os elementos do DOM necessários
  const messageForm = document.getElementById("message-form");
  const messageInput = document.getElementById("message-input");
  const messagesContainer = document.getElementById("messages-container");
  const welcomeMessage = document.getElementById("welcome-message");
  const newChatButton = document.querySelector(".new-chat-btn");

  // Função para adicionar mensagens ao container (tanto do usuário quanto do bot)
  function addMessage(text, sender) {
    const messageElement = document.createElement("div");
    messageElement.classList.add("message", sender); // Adiciona classes para estilização
    messageElement.textContent = text;
    messagesContainer.appendChild(messageElement); // Adiciona a mensagem ao container
    messagesContainer.scrollTop = messagesContainer.scrollHeight; // Faz scroll automático para o final

    // Oculta a mensagem de boas-vindas se ainda estiver visível
    if (welcomeMessage && !welcomeMessage.hidden) {
      welcomeMessage.style.display = "none";
    }
  }

  // Função assíncrona para enviar a mensagem do usuário à API da OpenAI e obter a resposta do bot
  async function getBotResponse(userMessage) {
    try {
      // Cria um indicador de "Digitando..." para simular que o bot está respondendo
      const typingIndicator = document.createElement("div");
      typingIndicator.classList.add("message", "bot", "typing");
      typingIndicator.textContent = "Digitando...";
      messagesContainer.appendChild(typingIndicator);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;

      // Requisição à API da OpenAI com modelo GPT
      const response = await fetch(
        "https://api.openai.com/v1/chat/completions",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${apiKey}`, // Usa a chave de API fornecida
          },
          body: JSON.stringify({
            model: "gpt-3.5-turbo",
            messages: [
              {
                role: "system",
                content: "Você é um assistente útil da Facilita U.", // Prompt do sistema
              },
              {
                role: "user",
                content: userMessage, // Mensagem enviada pelo usuário
              },
            ],
            max_tokens: 1024, // Limite de tokens da resposta
            temperature: 0.5, // Grau de criatividade da resposta
          }),
        }
      );

      const data = await response.json(); // Converte a resposta em JSON

      // Simula atraso para mostrar resposta após "digitando..."
      setTimeout(() => {
        typingIndicator.remove(); // Remove o indicador de digitação

        if (data.choices && data.choices.length > 0) {
          const reply = data.choices[0].message.content.trim(); // Extrai a resposta do bot
          addMessage(reply, "bot"); // Adiciona a resposta ao chat
        } else {
          fallbackBotResponse(userMessage); // Se não houver resposta válida, usa resposta alternativa
        }
      }, 3000); // Tempo simulado de digitação
    } catch (error) {
      console.error("Erro com a API:", error); // Log de erro
      fallbackBotResponse(userMessage); // Usa fallback em caso de erro na API
    }
  }

  // Função para respostas alternativas pré-definidas se a API falhar ou não entender
  function fallbackBotResponse(userMessage) {
    const lower = userMessage.toLowerCase(); // Converte a mensagem para minúsculas
    let botText = "Desculpe, não entendi. Pode reformular sua pergunta?"; // Resposta padrão

    // Respostas alternativas com base em palavras-chave
    if (
      lower.includes("olá") ||
      lower.includes("oi") ||
      lower.includes("bom dia")
    ) {
      botText = "Olá! Como posso ajudar você com a Facilita U hoje?";
    } else if (lower.includes("matrícula") || lower.includes("inscricao")) {
      botText =
        "Para informações sobre matrícula, consulte o portal do aluno ou a secretaria.";
    } else if (lower.includes("horário") || lower.includes("aulas")) {
      botText =
        "Seu horário de aulas está disponível na seção 'Meu Horário' no portal.";
    } else if (lower.includes("biblioteca")) {
      botText = "A biblioteca funciona das 8h às 22h de segunda a sexta.";
    } else if (lower.includes("obrigado") || lower.includes("agradecido")) {
      botText = "De nada! Se precisar de algo mais, estou por aqui.";
    }

    // Simula digitação antes de mostrar a resposta alternativa
    const typingIndicator = document.createElement("div");
    typingIndicator.classList.add("message", "bot", "typing");
    typingIndicator.textContent = "Digitando...";
    messagesContainer.appendChild(typingIndicator);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    setTimeout(() => {
      typingIndicator.remove();
      addMessage(botText, "bot"); // Adiciona a resposta alternativa
    }, 3000);
  }

  // Evento de envio do formulário de mensagem
  messageForm.addEventListener("submit", (event) => {
    event.preventDefault(); // Evita o recarregamento da página
    const userText = messageInput.value.trim(); // Obtém e limpa o texto do input

    if (userText) {
      addMessage(userText, "user"); // Adiciona a mensagem do usuário no chat
      messageInput.value = ""; // Limpa o campo de entrada
      messageInput.focus(); // Mantém o foco no input
      getBotResponse(userText); // Envia para o bot
    } else {
      messageInput.style.border = "1px solid red"; // Indica erro se estiver vazio
    }
  });

  // Evento do botão "Nova Conversa"
  newChatButton.addEventListener("click", () => {
    messagesContainer.innerHTML = ""; // Limpa o histórico de mensagens
    if (welcomeMessage) {
      welcomeMessage.style.display = "flex"; // Exibe novamente a mensagem de boas-vindas
    }
    messageInput.value = ""; // Limpa o campo de entrada
    messageInput.focus(); // Coloca o foco de volta no input
    console.log("Novo chat iniciado!"); // Log para depuração
  });

  // Oculta a mensagem de boas-vindas se já houver mensagens
  if (messagesContainer.children.length > 0 && welcomeMessage) {
    welcomeMessage.style.display = "none";
  }
});
