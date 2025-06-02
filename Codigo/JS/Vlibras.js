/**
 * FUNÇÃO QUE ADICIONA O VLIBRAS AO SITE
 * 
 * O VLibras é um recurso importante que permite traduzir
 * o conteúdo do site para LIBRAS (Língua Brasileira de Sinais),
 * ajudando pessoas surdas ou com dificuldade auditiva.
 * 
 * Esta função cria todos os elementos necessários para o VLibras
 * funcionar e carrega o plugin oficial do governo.
 */
function adicionarVLibras() {
  // Cria o "container" principal onde o VLibras vai ficar
  const vlibrasContainer = document.createElement("div");
  vlibrasContainer.setAttribute("vw", ""); // Atributo especial para o VLibras
  vlibrasContainer.classList.add("enabled"); // Habilita o plugin
  
  // Cria o botão de acesso que aparece na tela
  const accessButton = document.createElement("div");
  accessButton.setAttribute("vw-access-button", "");
  accessButton.classList.add("active"); // Deixa ativo
  
  // Cria a área onde o tradutor vai aparecer
  const pluginWrapper = document.createElement("div");
  pluginWrapper.setAttribute("vw-plugin-wrapper", "");
  
  // Cria uma parte interna do tradutor
  const pluginTopWrapper = document.createElement("div");
  pluginTopWrapper.classList.add("vw-plugin-top-wrapper");
  
  // Monta todas as peças juntas
  pluginWrapper.appendChild(pluginTopWrapper);
  vlibrasContainer.appendChild(accessButton);
  vlibrasContainer.appendChild(pluginWrapper);
  
  // Adiciona tudo ao final da página
  document.body.appendChild(vlibrasContainer);
  
  // Cria uma tag <script> para carregar o VLibras de um site externo
  const vlibrasScript = document.createElement("script");
  vlibrasScript.src = "https://vlibras.gov.br/app/vlibras-plugin.js";
  
  /**
   * QUANDO O SCRIPT TERMINAR DE CARREGAR
   * 
   * Isso inicia o VLibras com as configurações padrão
   */
  vlibrasScript.onload = function () {
    new window.VLibras.Widget("https://vlibras.gov.br/app");
  };
  
  // Adiciona o script à página para iniciar o carregamento
  document.body.appendChild(vlibrasScript);
}

/**
 * INICIA O VLIBRAS QUANDO A PÁGINA ESTIVER PRONTA
 * 
 * Espera todos os elementos da página carregarem antes
 * de adicionar o VLibras, para não causar problemas
 */
document.addEventListener("DOMContentLoaded", adicionarVLibras);