document.addEventListener("DOMContentLoaded", function () {
  // Elementos do modal
  const configBtn = document.querySelector(".sidebar-btn:not(.profile-btn)");
  const configModal = document.createElement("div");
  configModal.className = "config-modal";

  // Conteúdo do modal
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

  // Adiciona o modal ao body
  document.body.appendChild(configModal);

  // Elementos de configuração
  const highContrast = document.getElementById("high-contrast");
  const fontSize = document.getElementById("font-size");

  // Abrir modal
  configBtn.addEventListener("click", function () {
    configModal.style.display = "flex";
    loadPreferences();
  });

  // Fechar modal
  configModal
    .querySelector(".close-config")
    .addEventListener("click", function () {
      configModal.style.display = "none";
    });

  // Fechar ao clicar fora
  configModal.addEventListener("click", function (e) {
    if (e.target === configModal) {
      configModal.style.display = "none";
    }
  });

  // Carrega preferências do localStorage
  function loadPreferences() {
    // Alto Contraste
    if (localStorage.getItem("highContrast") === "true") {
      highContrast.checked = true;
      toggleHighContrast(true);
    }

    // Tamanho da Fonte
    const savedFontSize = localStorage.getItem("fontSize") || "16px";
    fontSize.value = savedFontSize;
    document.documentElement.style.fontSize = savedFontSize;
  }

  // Salva preferências no localStorage
  function savePreferences() {
    localStorage.setItem("highContrast", highContrast.checked);
    localStorage.setItem("fontSize", fontSize.value);
  }

  // Alternar alto contraste
  function toggleHighContrast(enable) {
    if (enable) {
      document.documentElement.style.setProperty("--bg-primary", "#ffffff");
      document.documentElement.style.setProperty("--bg-secondary", "#f0f0f0");
      document.documentElement.style.setProperty("--text-primary", "#000000");
      document.documentElement.style.setProperty("--border-color", "#cccccc");
    } else {
      document.documentElement.style.setProperty("--bg-primary", "#212121");
      document.documentElement.style.setProperty("--bg-secondary", "#2d2d2d");
      document.documentElement.style.setProperty("--text-primary", "#e0e0e0");
      document.documentElement.style.setProperty("--border-color", "#424242");
    }
  }

  // Event Listeners
  highContrast.addEventListener("change", function () {
    toggleHighContrast(this.checked);
    savePreferences();
  });

  fontSize.addEventListener("change", function () {
    document.documentElement.style.fontSize = this.value;
    savePreferences();
  });

  // Carrega as preferências ao iniciar
  loadPreferences();
});
