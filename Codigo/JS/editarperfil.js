/**
 * FUNÇÃO QUE FORMATA O NÚMERO DE TELEFONE AUTOMATICAMENTE
 * 
 * Quando você digita no campo de telefone, esta função:
 * 1. Remove tudo que não for número
 * 2. Coloca no formato (XX) 9XXXX-XXXX
 * 
 * Exemplo: Se digitar "11999998888" vira "(11) 99999-8888"
 * 
 * @param {Event} event - O evento de digitação no campo
 */
function mascaraTelefone(event) {
  // Pega o campo de telefone que está sendo digitado
  let input = event.target;
  
  // Passo 1: Remove letras, espaços e símbolos, deixando só números
  input.value = input.value.replace(/\D/g, "");
  
  // Passo 2: Aplica a formatação (XX) 9XXXX-XXXX
  input.value = input.value.replace(/(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
}

/**
 * FUNÇÃO QUE VERIFICA SE O TELEFONE ESTÁ NO FORMATO CERTO
 * 
 * Verifica se o telefone está exatamente assim: (XX) 9XXXX-XXXX
 * Onde X são números. Exemplo válido: (11) 98765-4321
 * 
 * @param {string} telefone - O número de telefone formatado
 * @returns {boolean} Retorna VERDADEIRO se estiver no formato certo
 */
function validarTelefoneJS(telefone) {
  // A fórmula que define como deve ser o telefone
  const regex = /^\(\d{2}\) 9\d{4}-\d{4}$/;
  return regex.test(telefone); // Testa se o telefone bate com a fórmula
}

/**
 * FUNÇÃO PRINCIPAL QUE VERIFICA O FORMULÁRIO DE EDIÇÃO DE PERFIL
 * 
 * Essa função é chamada quando você clica em "Salvar Alterações".
 * Ela verifica:
 * 1. Se o telefone está no formato correto
 * 2. Se a idade é de pelo menos 16 anos
 * 
 * Se algo estiver errado, mostra um alerta explicando o problema.
 * 
 * @returns {boolean} Retorna VERDADEIRO se tudo estiver correto
 */
function validarFormulario() {
  // Pega o telefone digitado
  const telefone = document.getElementById("telefone").value;
  
  // Pega a data de nascimento e converte para o formato de data
  const dataNascimento = new Date(document.getElementById("data_nascimento").value);

  // Verificação do telefone
  if (!validarTelefoneJS(telefone)) {
    alert("Telefone inválido. Use o formato (XX) 9XXXX-XXXX.");
    return false; // Impede o envio do formulário
  }

  // Verificação da idade (pelo menos 16 anos)
  const hoje = new Date(); // Data de hoje
  const idadeMinima = new Date(); // Vamos calcular a data mínima
  
  // Subtrai 16 anos da data atual
  idadeMinima.setFullYear(hoje.getFullYear() - 16);
  
  // Se a data de nascimento for depois da data mínima (ou seja, tem menos de 16 anos)
  if (dataNascimento > idadeMinima) {
    alert("Você deve ter pelo menos 16 anos.");
    return false; // Impede o envio do formulário
  }

  // Se passar por todas as verificações, permite o envio
  return true;
}