/**
 * FUNÇÃO QUE FORMATA O NÚMERO DE TELEFONE ENQUANTO A PESSOA DIGITA
 * 
 * Essa função é acionada toda vez que alguém digita algo no campo de telefone.
 * Ela faz duas coisas importantes:
 * 1. Remove qualquer caractere que não seja número (como letras ou símbolos)
 * 2. Coloca o telefone no formato bonito: (XX) 9XXXX-XXXX
 * 
 * Por exemplo, se você digitar "11987654321", vira "(11) 98765-4321"
 * 
 * @param {Event} event - É como o computador sabe que algo foi digitado
 */
function mascaraTelefone(event) {
  // Pega o campo onde a pessoa está digitando
  let input = event.target;
  
  // Primeiro remove tudo que não for número
  input.value = input.value.replace(/\D/g, "");
  
  // Depois formata: (XX) 9XXXX-XXXX
  input.value = input.value.replace(/(\d{2})(\d{5})(\d{4}).*/, "($1) $2-$3");
}

/**
 * FUNÇÃO QUE VERIFICA SE O E-MAIL É DA FACULDADE
 * 
 * Verifica se o e-mail termina com ".edu.br", que é usado por escolas e faculdades.
 * Exemplo de e-mail válido: "aluno@faculdade.edu.br"
 * 
 * @param {string} email - O e-mail que a pessoa digitou
 * @returns {boolean} Retorna VERDADEIRO se for um e-mail válido da faculdade
 */
function validarEmail(email) {
  // Essa é uma "expressão regular" - uma fórmula para verificar padrões em textos
  const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.edu\.br$/;
  return regex.test(email); // Testa se o e-mail bate com a fórmula
}

/**
 * FUNÇÃO QUE VERIFICA SE A SENHA É FORTE
 * 
 * Para ser considerada forte, a senha precisa ter:
 * - Pelo menos 8 caracteres
 * - Pelo menos 1 letra
 * - Pelo menos 1 número
 * - Pelo menos 1 símbolo especial (@$!%*?&)
 * 
 * Exemplo de senha válida: "Senha@123"
 * 
 * @param {string} senha - A senha que a pessoa digitou
 * @returns {boolean} Retorna VERDADEIRO se a senha for forte o suficiente
 */
function validarSenha(senha) {
  // Outra "expressão regular" que verifica todos os requisitos
  const regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  return regex.test(senha);
}

/**
 * FUNÇÃO QUE VERIFICA SE O TELEFONE ESTÁ NO FORMATO CERTO
 * 
 * O telefone deve estar exatamente assim: (XX) 9XXXX-XXXX
 * Onde X são números. Exemplo: (11) 98765-4321
 * 
 * @param {string} telefone - O telefone formatado
 * @returns {boolean} Retorna VERDADEIRO se estiver no formato correto
 */
function validarTelefoneJS(telefone) {
  const regex = /^\(\d{2}\) 9\d{4}-\d{4}$/;
  return regex.test(telefone);
}

/**
 * FUNÇÃO PRINCIPAL QUE VERIFICA TODOS OS CAMPOS DO FORMULÁRIO
 * 
 * Essa função é chamada quando a pessoa clica no botão "Cadastrar".
 * Ela verifica:
 * 1. Se o e-mail é válido
 * 2. Se a senha é forte
 * 3. Se as duas senhas digitadas são iguais
 * 4. Se o telefone está correto
 * 
 * Se algo estiver errado, mostra uma mensagem explicando o problema.
 * 
 * @returns {boolean} Retorna VERDADEIRO se tudo estiver correto
 */
function validarFormulario() {
  // Pega os valores que a pessoa digitou em cada campo
  const email = document.getElementById("email").value;
  const senha = document.getElementById("senha").value;
  const confirmarSenha = document.getElementById("confirmar_senha").value;
  const telefone = document.getElementById("telefone").value;

  // Verificação do e-mail
  if (!validarEmail(email)) {
    alert("Por favor, informe um e-mail institucional válido (ex: nome@faculdade.edu.br).");
    return false; // Impede o envio do formulário
  }

  // Verificação da senha
  if (!validarSenha(senha)) {
    alert("A senha deve conter pelo menos 8 caracteres, incluindo uma letra, um número e um caractere especial.");
    return false;
  }

  // Verifica se as duas senhas são iguais
  if (senha !== confirmarSenha) {
    alert("As senhas não coincidem.");
    return false;
  }

  // Verificação do telefone
  if (!validarTelefoneJS(telefone)) {
    alert("Telefone inválido. Use o formato (XX) 9XXXX-XXXX.");
    return false;
  }

  // Se passar por todas as verificações, permite o envio
  return true;
}