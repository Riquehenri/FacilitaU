// Aguarda o carregamento completo do DOM antes de executar o código
document.addEventListener("DOMContentLoaded", () => {
  // =============================================
  // SELEÇÃO DE ELEMENTOS
  // =============================================
  
  // Obtém referências para os elementos do formulário de login
  const loginForm = document.getElementById("login-form");
  // Obtém referências para os elementos do formulário de registro
  const registerForm = document.getElementById("register-form");
  // Botão que mostra o formulário de registro
  const showRegisterBtn = document.getElementById("show-register-btn");
  // Botão que mostra o formulário de login
  const showLoginBtn = document.getElementById("show-login-btn");
  // Caixa de cadastro na seção de informações
  const infoSignupBox = document.querySelector(".info-section .signup-box");
  // Caixa de login na seção de informações
  const infoLoginBox = document.querySelector(".info-section .login-box");

  // =============================================
  // FUNÇÃO PARA ALTERNAR VISIBILIDADE DA SENHA
  // =============================================
  const togglePasswordVisibility = (inputElement, toggleElement) => {
    // Verifica se o input está atualmente como tipo password
    const isPassword = inputElement.type === "password";
    // Alterna entre tipo text (mostrar) e password (ocultar)
    inputElement.type = isPassword ? "text" : "password";
    // Alterna as classes do ícone para mostrar o estado correto
    toggleElement.classList.toggle("fa-eye-slash", !isPassword);
    toggleElement.classList.toggle("fa-eye", isPassword);
  };

  // =============================================
  // CONFIGURAÇÃO DOS TOGGLES DE SENHA
  // =============================================
  
  // Obtém o campo de senha do formulário de login
  const loginPasswordInput = document.querySelector(
    "#login-form input[name='senha']"
  );
  // Obtém o ícone de toggle do formulário de login
  const loginTogglePassword = document.querySelector(
    "#login-form .password-toggle"
  );
  
  // Adiciona evento de clique ao ícone de senha do login
  if (loginTogglePassword) {
    loginTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(loginPasswordInput, loginTogglePassword);
    });
  }

  // Obtém o campo de senha do formulário de registro
  const registerPasswordInput = document.querySelector(
    "#register-form input[name='senha']"
  );
  // Obtém o ícone de toggle do formulário de registro
  const registerTogglePassword = document.querySelector(
    "#register-form .password-toggle"
  );
  
  // Adiciona evento de clique ao ícone de senha do registro
  if (registerTogglePassword) {
    registerTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(registerPasswordInput, registerTogglePassword);
    });
  }

  // Obtém o campo de confirmação de senha
  const confirmPasswordInput = document.getElementById(
    "register-confirm-password"
  );
  // Obtém o ícone de toggle da confirmação de senha
  const confirmTogglePassword = document.querySelector(
    "#register-confirm-password + .password-toggle"
  );
  
  // Adiciona evento de clique ao ícone de confirmação de senha
  if (confirmTogglePassword) {
    confirmTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(confirmPasswordInput, confirmTogglePassword);
    });
  }

  // =============================================
  // ALTERNÂNCIA ENTRE FORMULÁRIOS (LOGIN/REGISTRO)
  // =============================================
  
  // Evento para mostrar formulário de registro e esconder o de login
  if (showRegisterBtn) {
    showRegisterBtn.addEventListener("click", (e) => {
      e.preventDefault(); // Previne comportamento padrão do botão
      
      // Esconde formulário de login
      loginForm.classList.add("hidden");
      // Mostra formulário de registro
      registerForm.classList.remove("hidden");
      
      // Atualiza a seção de informações
      infoSignupBox.classList.add("hidden"); // Esconde opção de cadastro
      infoLoginBox.classList.remove("hidden"); // Mostra opção de login
    });
  }

  // Evento para mostrar formulário de login e esconder o de registro
  if (showLoginBtn) {
    showLoginBtn.addEventListener("click", (e) => {
      e.preventDefault(); // Previne comportamento padrão do botão
      
      // Esconde formulário de registro
      registerForm.classList.add("hidden");
      // Mostra formulário de login
      loginForm.classList.remove("hidden");
      
      // Atualiza a seção de informações
      infoLoginBox.classList.add("hidden"); // Esconde opção de login
      infoSignupBox.classList.remove("hidden"); // Mostra opção de cadastro
    });
  }

  // =============================================
  // VALIDAÇÃO DO FORMULÁRIO DE REGISTRO
  // =============================================
  
  // Adiciona validação ao enviar o formulário de registro
  document
    .querySelector("#register-form form")
    .addEventListener("submit", function (e) {
      // Obtém o valor do campo de senha
      const senha = document.querySelector(
        "#register-form input[name='senha']"
      ).value;
      // Obtém o valor do campo de confirmação de senha
      const confirmacao = document.getElementById(
        "register-confirm-password"
      ).value;

      // Verifica se as senhas coincidem
      if (senha !== confirmacao) {
        e.preventDefault(); // Impede o envio do formulário
        alert("As senhas não coincidem!"); // Mostra mensagem de erro
      }
    });
});