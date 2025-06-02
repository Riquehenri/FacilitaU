/**
 * TUDO O QUE ACONTECE QUANDO A PÁGINA TERMINA DE CARREGAR
 * 
 * Configura o sistema de configurações do usuário, incluindo:
 * 1. Modal de configurações que aparece quando clica no botão
 * 2. Opções de acessibilidade (contraste e tamanho da fonte)
 * 3. Lembrar das preferências mesmo depois de fechar o navegador
 */
document.addEventListener("DOMContentLoaded", function () {
  // Pega o botão que abre as configurações (o ícone de engrenagem)
  const configBtn = document.querySelector(".sidebar-btn:not(.profile-btn)");
  
  // Cria a janela de configurações do zero
  const configModal = document.createElement("div");
  configModal.className = "config-modal"; // Define a classe para o estilo CSS

  // Conteúdo HTML que vai dentro da janela de configurações
  configModal.innerHTML = `
    <div class="config-content">
      <div class="config-header">
        <h3>Configurações</h3>
        <button class="close-config">&times;</button>
      </div>
      <div class="config-body">
        <div class="config-section">
          <h4>Acessibilidade</h4>
          <div class="config-option">
            <label for="high-contrast">Alterar Contraste</label>
            <label class="switch">
              <input type="checkbox" id="high-contrast">
              <span class="slider"></span>
            </label>
          </div>
          <div class="config-option">
            <label for="font-size">Tamanho da Fonte</label>
            <select id="font-size">
              <option value="16px">Normal</option>
              <option value="18px">Grande</option>
              <option value="20px">Extra Grande</option>
            </select>
          </div>
        </div>
        
        <div class="config-section about-section">
          <h4>Sobre Nós</h4>
          <p>O Facilita U é uma plataforma desenvolvida para ajudar estudantes a organizarem seus estudos.</p>
        </div>
      </div>
    </div>
  `;

  // Adiciona a janela de configurações ao final da página
  document.body.appendChild(configModal);

  // Pega os controles de dentro do modal
  const highContrast = document.getElementById("high-contrast"); // Checkbox de alto contraste
  const fontSize = document.getElementById("font-size"); // Seletor de tamanho de fonte

  /**
   * ABRIR O MODAL DE CONFIGURAÇÕES
   * 
   * Quando clica no botão de configurações:
   * 1. Mostra a janela de configurações
   * 2. Carrega as preferências salvas
   */
  configBtn.addEventListener("click", function () {
    configModal.style.display = "flex"; // Mostra o modal
    loadPreferences(); // Carrega as configurações salvas
  });

  /**
   * FECHAR O MODAL DE CONFIGURAÇÕES
   * 
   * Funciona de três formas:
   * 1. Clicando no X no canto superior direito
   * 2. Clicando fora da área do modal
   * 3. Pressionando ESC (configurado em outro arquivo)
   */
  // Fecha ao clicar no X
  configModal.querySelector(".close-config").addEventListener("click", function () {
    configModal.style.display = "none";
  });

  // Fecha ao clicar fora do modal
  configModal.addEventListener("click", function (e) {
    if (e.target === configModal) { // Verifica se clicou na área escura
      configModal.style.display = "none";
    }
  });

  /**
   * CARREGAR PREFERÊNCIAS SALVAS
   * 
   * Busca no computador do usuário as configurações que ele salvou antes
   */
  function loadPreferences() {
    // Configuração de Alto Contraste
    if (localStorage.getItem("highContrast") === "true") {
      highContrast.checked = true; // Marca o checkbox
      toggleHighContrast(true); // Aplica o contraste alto
    }

    // Configuração de Tamanho da Fonte
    const savedFontSize = localStorage.getItem("fontSize") || "16px"; // Pega o valor salvo ou usa "16px" como padrão
    fontSize.value = savedFontSize; // Define o seletor
    document.documentElement.style.fontSize = savedFontSize; // Aplica o tamanho
  }

  /**
   * SALVAR PREFERÊNCIAS
   * 
   * Guarda no computador do usuário as configurações escolhidas
   */
  function savePreferences() {
    // Salva o estado do alto contraste (true/false)
    localStorage.setItem("highContrast", highContrast.checked);
    
    // Salva o tamanho da fonte selecionado
    localStorage.setItem("fontSize", fontSize.value);
  }

  /**
   * ALTERNAR MODO DE ALTO CONTRASTE
   * 
   * Muda as cores do site para facilitar a leitura
   * @param {boolean} enable - Ativa ou desativa o alto contraste
   */
  function toggleHighContrast(enable) {
    if (enable) {
      // Cores para alto contraste (fundo branco, texto preto)
      document.documentElement.style.setProperty("--bg-primary", "#ffffff");
      document.documentElement.style.setProperty("--bg-secondary", "#f0f0f0");
      document.documentElement.style.setProperty("--text-primary", "#000000");
      document.documentElement.style.setProperty("--border-color", "#cccccc");
    } else {
      // Cores normais (fundo escuro, texto claro)
      document.documentElement.style.setProperty("--bg-primary", "#212121");
      document.documentElement.style.setProperty("--bg-secondary", "#2d2d2d");
      document.documentElement.style.setProperty("--text-primary", "#e0e0e0");
      document.documentElement.style.setProperty("--border-color", "#424242");
    }
  }

  /**
   * EVENTOS QUE FICAM ESCUTANDO MUDANÇAS NAS CONFIGURAÇÕES
   */
  // Quando muda o checkbox de alto contraste
  highContrast.addEventListener("change", function () {
    toggleHighContrast(this.checked); // Aplica as mudanças
    savePreferences(); // Salva a escolha
  });

  // Quando muda o tamanho da fonte
  fontSize.addEventListener("change", function () {
    document.documentElement.style.fontSize = this.value; // Aplica o novo tamanho
    savePreferences(); // Salva a escolha
  });

  // Carrega as preferências assim que a página abre
  loadPreferences();
});