// Aplica a máscara de telefone: (XX) 9XXXX-XXXX
function mascaraTelefone(event) {
  let input = event.target;
  input.value = input.value
    .replace(/\D/g, "") // Remove não dígitos
    .replace(/(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
}
// Valida o formato (XX) 9XXXX-XXXX
function validarTelefoneJS(telefone) {
  const regex = /^\(\d{2}\) 9\d{4}-\d{4}$/;
  return regex.test(telefone);
}
// Validação do formulário
function validarFormulario() {
  const telefone = document.getElementById("telefone").value;
  const dataNascimento = new Date(
    document.getElementById("data_nascimento").value
  );
  // Telefone
  if (!validarTelefoneJS(telefone)) {
    alert("Telefone inválido. Use o formato (XX) 9XXXX-XXXX.");
    return false;
  }
  // Idade mínima
  const hoje = new Date();
  const idadeMinima = new Date();
  idadeMinima.setFullYear(hoje.getFullYear() - 16);
  if (dataNascimento > idadeMinima) {
    alert("Você deve ter pelo menos 16 anos.");
    return false;
  }
  return true;
}
