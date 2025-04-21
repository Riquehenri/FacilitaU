// Aguarda o carregamento completo do DOM (Document Object Model) antes de executar o script.
// Isso garante que todos os elementos HTML estejam disponíveis para manipulação.
document.addEventListener("DOMContentLoaded", () => {
  // --- Seleção de Elementos do DOM ---

  // Seleciona o formulário de envio de mensagens pelo seu ID.
  const messageForm = document.getElementById("message-form");
  // Seleciona o campo de entrada de texto onde o usuário digita a mensagem.
  const messageInput = document.getElementById("message-input");
  // Seleciona o container onde as mensagens do chat serão exibidas.
  const messagesContainer = document.getElementById("messages-container");
  // Seleciona a mensagem de boas-vindas inicial (pode ser nulo se não existir).
  const welcomeMessage = document.getElementById("welcome-message");
  // Seleciona o botão "Novo Chat" pela sua classe CSS.
  const newChatButton = document.querySelector(".new-chat-btn");

  // --- Funções Auxiliares ---

  /**
   * Adiciona uma nova mensagem (do usuário ou do bot) ao container de mensagens.
   * @param {string} text - O texto da mensagem a ser adicionada.
   * @param {string} sender - Quem enviou a mensagem ('user' ou 'bot'). Usado para aplicar classes CSS.
   */
  function addMessage(text, sender) {
      // Cria um novo elemento <div> para representar a mensagem.
      const messageElement = document.createElement("div");
      // Adiciona as classes CSS 'message' e a classe específica do remetente ('user' ou 'bot')
      // para estilização.
      messageElement.classList.add("message", sender);
      // Define o conteúdo de texto do elemento da mensagem.
      messageElement.textContent = text;
      // Anexa o novo elemento de mensagem ao container de mensagens.
      messagesContainer.appendChild(messageElement);

      // Faz o scroll automático do container para a última mensagem adicionada.
      // Isso mantém a mensagem mais recente visível.
      messagesContainer.scrollTop = messagesContainer.scrollHeight;

      // Verifica se a mensagem de boas-vindas existe e ainda está visível.
      // Se sim, a esconde, pois o chat já começou.
      if (welcomeMessage && !welcomeMessage.hidden && window.getComputedStyle(welcomeMessage).display !== 'none') {
          welcomeMessage.style.display = "none"; // Altera o estilo para esconder o elemento
      }
  }

  /**
   * Simula uma resposta do bot baseada na mensagem do usuário.
   * ATENÇÃO: Esta função contém lógica de resposta fixa e deverá ser substituída
   * pela integração com uma API real no futuro.
   * @param {string} userMessage - A mensagem enviada pelo usuário.
   */
  function simulateBotResponse(userMessage) {
      // Define uma resposta padrão caso nenhuma condição seja atendida.
      let botText = "Desculpe, não entendi. Pode reformular sua pergunta?";

      // Converte a mensagem do usuário para minúsculas para facilitar a comparação (case-insensitive).
      const lowerCaseMessage = userMessage.toLowerCase();

      // --- Lógica de Resposta Simples (Baseada em Palavras-chave) ---
      // Verifica se a mensagem contém palavras-chave específicas e define a resposta do bot.
      if (
          lowerCaseMessage.includes("olá") ||
          lowerCaseMessage.includes("oi") ||
          lowerCaseMessage.includes("bom dia")
      ) {
          botText = "Olá! Como posso ajudar você com a Facilita U hoje?";
      } else if (
          lowerCaseMessage.includes("matrícula") ||
          lowerCaseMessage.includes("inscricao") // Considera "inscricao" sem acento também
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
      // Adicione mais `else if` aqui para outras perguntas frequentes.

      // --- Simulação de Atraso ---
      // Usa setTimeout para simular um pequeno atraso antes do bot responder,
      // tornando a interação um pouco mais natural.
      setTimeout(() => {
          // Após o atraso (800ms), chama a função addMessage para exibir a resposta do bot.
          addMessage(botText, "bot");
      }, 800); // Atraso de 800 milissegundos (0.8 segundos).
  }

  // --- Event Listeners (Ouvintes de Eventos) ---

  // Adiciona um ouvinte de evento para o envio ('submit') do formulário.
  messageForm.addEventListener("submit", (event) => {
      // Previne o comportamento padrão do formulário, que é recarregar a página.
      event.preventDefault();

      // Obtém o texto digitado pelo usuário, removendo espaços em branco extras no início e fim.
      const userText = messageInput.value.trim();

      // Verifica se o usuário realmente digitou algo (não está vazio).
      if (userText) {
          // Adiciona a mensagem do usuário à interface.
          addMessage(userText, "user");
          // Limpa o campo de entrada para a próxima mensagem.
          messageInput.value = "";
          // Coloca o foco de volta no campo de entrada para facilitar a digitação contínua.
          messageInput.focus();
          // Chama a função para simular a resposta do bot à mensagem do usuário.
          simulateBotResponse(userText);
      }
  });

  // Adiciona um ouvinte de evento para o clique no botão "Novo Chat".
  newChatButton.addEventListener("click", () => {
      // Limpa todo o conteúdo HTML dentro do container de mensagens.
      messagesContainer.innerHTML = "";
      // Verifica se a mensagem de boas-vindas existe.
      if (welcomeMessage) {
          // Mostra a mensagem de boas-vindas novamente, definindo seu estilo de display para 'flex'.
          // Use 'block' ou outro valor apropriado se o layout original for diferente.
          welcomeMessage.style.display = "flex";
      }
      // Limpa o campo de entrada de texto.
      messageInput.value = "";
      // Linha para debug ou futuras implementações ao iniciar um novo chat.
      console.log("Novo chat iniciado!");
      // (Opcional) Poderia colocar o foco no input aqui também: messageInput.focus();
  });

  // --- Inicialização ---

  // Bloco comentado: Verificação inicial para esconder a mensagem de boas-vindas
  // se já houver mensagens no container (útil se o histórico for persistido).
  
  
  if (messagesContainer.children.length > 0 && welcomeMessage) {
      welcomeMessage.style.display = "none";
  }
  
});