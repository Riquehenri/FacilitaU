document.addEventListener("DOMContentLoaded", () => {
  // Elementos
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");
  const showRegisterBtn = document.getElementById("show-register-btn");
  const showLoginBtn = document.getElementById("show-login-btn");
  const infoSignupBox = document.querySelector(".info-section .signup-box");
  const infoLoginBox = document.querySelector(".info-section .login-box");

  // Função para alternar visibilidade da senha
  const togglePasswordVisibility = (inputElement, toggleElement) => {
    const isPassword = inputElement.type === "password";
    inputElement.type = isPassword ? "text" : "password";
    toggleElement.classList.toggle("fa-eye-slash", !isPassword);
    toggleElement.classList.toggle("fa-eye", isPassword);
  };

  // Login - Toggle de senha
  const loginPasswordInput = document.querySelector(
    "#login-form input[name='senha']"
  );
  const loginTogglePassword = document.querySelector(
    "#login-form .password-toggle"
  );
  if (loginTogglePassword) {
    loginTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(loginPasswordInput, loginTogglePassword);
    });
  }

  // Cadastro - Toggle de senha
  const registerPasswordInput = document.querySelector(
    "#register-form input[name='senha']"
  );
  const registerTogglePassword = document.querySelector(
    "#register-form .password-toggle"
  );
  if (registerTogglePassword) {
    registerTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(registerPasswordInput, registerTogglePassword);
    });
  }

  // Cadastro - Toggle de confirmação de senha (adicione o ícone no HTML)
  const confirmPasswordInput = document.getElementById(
    "register-confirm-password"
  );
  const confirmTogglePassword = document.querySelector(
    "#register-confirm-password + .password-toggle"
  );
  if (confirmTogglePassword) {
    confirmTogglePassword.addEventListener("click", () => {
      togglePasswordVisibility(confirmPasswordInput, confirmTogglePassword);
    });
  }

  // Alternar entre formulários
  if (showRegisterBtn) {
    showRegisterBtn.addEventListener("click", (e) => {
      e.preventDefault();
      loginForm.classList.add("hidden");
      registerForm.classList.remove("hidden");
      infoSignupBox.classList.add("hidden");
      infoLoginBox.classList.remove("hidden");
    });
  }

  if (showLoginBtn) {
    showLoginBtn.addEventListener("click", (e) => {
      e.preventDefault();
      registerForm.classList.add("hidden");
      loginForm.classList.remove("hidden");
      infoLoginBox.classList.add("hidden");
      infoSignupBox.classList.remove("hidden");
    });
  }

  // Validação de formulário de cadastro
  document
    .querySelector("#register-form form")
    .addEventListener("submit", function (e) {
      const senha = document.querySelector(
        "#register-form input[name='senha']"
      ).value;
      const confirmacao = document.getElementById(
        "register-confirm-password"
      ).value;

      if (senha !== confirmacao) {
        e.preventDefault();
        alert("As senhas não coincidem!");
      }
    });
});
