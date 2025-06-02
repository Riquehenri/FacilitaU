/**
 * TUDO O QUE ACONTECE QUANDO A PÁGINA DE LOGIN TERMINA DE CARREGAR
 * 
 * Essa parte do código configura tudo o que a página precisa fazer:
 * 1. Alternar entre as telas de Login e Cadastro
 * 2. Mostrar/esconder a senha quando clicar no olhinho
 * 3. Verificar se as senhas são iguais no cadastro
 */
document.addEventListener("DOMContentLoaded", () => {
  // Aqui pegamos todos os elementos importantes da página
  const loginForm = document.getElementById("login-form"); // Formulário de login
  const registerForm = document.getElementById("register-form"); // Formulário de cadastro
  const showRegisterBtn = document.getElementById("show-register-btn"); // Botão "Quero me cadastrar"
  const showLoginBtn = document.getElementById("show-login-btn"); // Botão "Já tenho conta"
  
  // Caixas de informação que mostram dicas
  const infoSignupBox = document.querySelector(".info-section .signup-box");
  const infoLoginBox = document.querySelector(".info-section .login-box");

  /**
   * FUNÇÃO QUE MOSTRA OU ESCONDE A SENHA
   * 
   * Quando a pessoa clica no ícone de olho, essa função:
   * 1. Muda o campo de senha para mostrar texto ou bolinhas
   * 2. Muda o ícone do olho para aberto ou fechado
   * 
   * @param {HTMLInputElement} inputElement - O campo onde se digita a senha
   * @param {HTMLElement} toggleElement - O ícone do olho que foi clicado
   */
  const togglePasswordVisibility = (inputElement, toggleElement) => {
    // Verifica se a senha está escondida (com bolinhas)
    const isPassword = inputElement.type === "password";
    
    // Alterna entre texto visível e bolinhas
    inputElement.type = isPassword ? "text" : "password";
    
    // Muda o ícone do olho (aberto/fechado)
    toggleElement.classList.toggle("fa-eye-slash", !isPassword);
    toggleElement.classList.toggle("fa-eye", isPassword);
  };

  // Configura o olhinho do formulário de LOGIN
  const loginPasswordInput = document.querySelector("#login-form input[name='senha']");
  const loginTogglePassword = document.querySelector("#login-form .password-toggle");
  if (loginTogglePassword) {
    loginTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(loginPasswordInput, loginTogglePassword);
    });
  }

  // Configura o olhinho do formulário de CADASTRO (senha)
  const registerPasswordInput = document.querySelector("#register-form input[name='senha']");
  const registerTogglePassword = document.querySelector("#register-form .password-toggle");
  if (registerTogglePassword) {
    registerTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(registerPasswordInput, registerTogglePassword);
    });
  }

  // Configura o olhinho do formulário de CADASTRO (confirmar senha)
  const confirmPasswordInput = document.getElementById("register-confirm-password");
  const confirmTogglePassword = document.querySelector("#register-confirm-password + .password-toggle");
  if (confirmTogglePassword) {
    confirmTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(confirmPasswordInput, confirmTogglePassword);
    });
  }

  /**
   * BOTÃO "QUERO ME CADASTRAR" - Mostra o formulário de cadastro
   */
  if (showRegisterBtn) {
    showRegisterBtn.addEventListener("click", (e) => {
      e.preventDefault(); // Impede o comportamento padrão do botão
      
      // Esconde o login e mostra o cadastro
      loginForm.classList.add("hidden");
      registerForm.classList.remove("hidden");
      
      // Atualiza as dicas na lateral
      infoSignupBox.classList.add("hidden");
      infoLoginBox.classList.remove("hidden");
    });
  }

  /**
   * BOTÃO "JÁ TENHO CONTA" - Mostra o formulário de login
   */
  if (showLoginBtn) {
    showLoginBtn.addEventListener("click", (e) => {
      e.preventDefault(); // Impede o comportamento padrão do botão
      
      // Esconde o cadastro e mostra o login
      registerForm.classList.add("hidden");
      loginForm.classList.remove("hidden");
      
      // Atualiza as dicas na lateral
      infoLoginBox.classList.add("hidden");
      infoSignupBox.classList.remove("hidden");
    });
  }

  /**
   * VERIFICAÇÃO QUANDO ENVIA O FORMULÁRIO DE CADASTRO
   * 
   * Verifica se as duas senhas digitadas são iguais antes de enviar
   */
  document.querySelector("#register-form form").addEventListener("submit", function (e) {
    // Pega os valores das senhas
    const senha = document.querySelector("#register-form input[name='senha']").value;
    const confirmacao = document.getElementById("register-confirm-password").value;

    // Se forem diferentes, mostra alerta e impede o envio
    if (senha !== confirmacao) {
      e.preventDefault(); // Cancela o envio
      alert("As senhas não coincidem!");
    }
  });
});