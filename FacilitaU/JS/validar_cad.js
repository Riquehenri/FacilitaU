// Aplica máscara de telefone automaticamente
function mascaraTelefone(event) {
  let input = event.target;
  input.value = input.value
    .replace(/\D/g, "") // Remove não dígitos
    .replace(/(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
}

// Validação de e-mail institucional (exemplo simples: termina com ".edu.br")
function validarEmail(email) {
  const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.edu\.br$/;
  return regex.test(email);
}

// Validação de senha: mínimo 8 caracteres, ao menos uma letra, um número e um caractere especial
function validarSenha(senha) {
  const regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  return regex.test(senha);
}

// Validação de telefone: formato (XX) 9XXXX-XXXX
function validarTelefoneJS(telefone) {
  const regex = /^\(\d{2}\) 9\d{4}-\d{4}$/;
  return regex.test(telefone);
}

// Função geral para validar o formulário antes do envio
function validarFormulario() {
  const email = document.getElementById("email").value;
  const senha = document.getElementById("senha").value;
  const confirmarSenha = document.getElementById("confirmar_senha").value;
  const telefone = document.getElementById("telefone").value;

  if (!validarEmail(email)) {
    alert(
      "Por favor, informe um e-mail institucional válido (ex: nome@faculdade.edu.br)."
    );
    return false;
  }

  if (!validarSenha(senha)) {
    alert(
      "A senha deve conter pelo menos 8 caracteres, incluindo uma letra, um número e um caractere especial."
    );
    return false;
  }

  if (senha !== confirmarSenha) {
    alert("As senhas não coincidem.");
    return false;
  }

  if (!validarTelefoneJS(telefone)) {
    alert("Telefone inválido. Use o formato (XX) 9XXXX-XXXX.");
    return false;
  }

  return true;
}
