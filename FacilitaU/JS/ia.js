const apiKey = "SUA_API"; // Substitua por sua chave de API da OpenAI

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
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    if (welcomeMessage && !welcomeMessage.hidden) {
      welcomeMessage.style.display = "none";
    }
  }

  async function getBotResponse(userMessage) {
    try {
      // Mostra "Digitando..."
      const typingIndicator = document.createElement("div");
      typingIndicator.classList.add("message", "bot", "typing");
      typingIndicator.textContent = "Digitando...";
      messagesContainer.appendChild(typingIndicator);
      messagesContainer.scrollTop = messagesContainer.scrollHeight;

      const response = await fetch(
        "https://api.openai.com/v1/chat/completions",
        {
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
                content: "Você é um assistente útil da Facilita U.",
              },
              {
                role: "user",
                content: userMessage,
              },
            ],
            max_tokens: 1024,
            temperature: 0.5,
          }),
        }
      );

      const data = await response.json();

      setTimeout(() => {
        typingIndicator.remove();

        if (data.choices && data.choices.length > 0) {
          const reply = data.choices[0].message.content.trim();
          addMessage(reply, "bot");
        } else {
          fallbackBotResponse(userMessage);
        }
      }, 3000);
    } catch (error) {
      console.error("Erro com a API:", error);
      fallbackBotResponse(userMessage);
    }
  }

  function fallbackBotResponse(userMessage) {
    const lower = userMessage.toLowerCase();
    let botText = "Desculpe, não entendi. Pode reformular sua pergunta?";

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

    // Simula digitação antes de mostrar resposta alternativa
    const typingIndicator = document.createElement("div");
    typingIndicator.classList.add("message", "bot", "typing");
    typingIndicator.textContent = "Digitando...";
    messagesContainer.appendChild(typingIndicator);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    setTimeout(() => {
      typingIndicator.remove();
      addMessage(botText, "bot");
    }, 1000);
  }

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
    }
  });

  newChatButton.addEventListener("click", () => {
    messagesContainer.innerHTML = "";
    if (welcomeMessage) {
      welcomeMessage.style.display = "flex";
    }
    messageInput.value = "";
    messageInput.focus();
    console.log("Novo chat iniciado!");
  });

  if (messagesContainer.children.length > 0 && welcomeMessage) {
    welcomeMessage.style.display = "none";
  }
});
