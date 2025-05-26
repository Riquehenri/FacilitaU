// Validação do formulário no cliente
function validarFormulario() {
  // Validação da data de nascimento
  const dataNascimento = new Date(
    document.getElementById("data_nascimento").value
  );
  const dataMinima = new Date();
  dataMinima.setFullYear(dataMinima.getFullYear() - 16);

  if (dataNascimento > dataMinima) {
    alert("Você deve ter pelo menos 16 anos para se cadastrar.");
    return false;
  }

  // Validação do telefone
  const telefone = document.getElementById("telefone").value.replace(/\D/g, "");
  if (telefone.length != 11 || telefone[2] != "9") {
    alert(
      "Telefone inválido. Informe um número com DDD e 9 dígitos (ex: 11987654321)"
    );
    return false;
  }

  // Validação de senha
  const senha = document.getElementById("senha").value;
  const confirmarSenha = document.getElementById("confirmar_senha").value;

  if (senha !== confirmarSenha) {
    alert("As senhas não coincidem!");
    return false;
  }

  return true;
}

// Máscara para o telefone
function mascaraTelefone(event) {
  let telefone = event.target.value.replace(/\D/g, "");
  telefone = telefone.replace(/^(\d{2})(\d)/g, "($1) $2");
  telefone = telefone.replace(/(\d)(\d{4})$/, "$1-$2");
  event.target.value = telefone;
}
