// Função para adicionar o plugin VLibras na página
function adicionarVLibras() {
  const vlibrasContainer = document.createElement("div");
  vlibrasContainer.setAttribute("vw", "");
  vlibrasContainer.classList.add("enabled");
  const accessButton = document.createElement("div");
  accessButton.setAttribute("vw-access-button", "");
  accessButton.classList.add("active");
  const pluginWrapper = document.createElement("div");
  pluginWrapper.setAttribute("vw-plugin-wrapper", "");
  const pluginTopWrapper = document.createElement("div");
  pluginTopWrapper.classList.add("vw-plugin-top-wrapper");
  pluginWrapper.appendChild(pluginTopWrapper);
  vlibrasContainer.appendChild(accessButton);
  vlibrasContainer.appendChild(pluginWrapper);
  document.body.appendChild(vlibrasContainer);
  const vlibrasScript = document.createElement("script");
  vlibrasScript.src = "https://vlibras.gov.br/app/vlibras-plugin.js";
  vlibrasScript.onload = function () {
    new window.VLibras.Widget("https://vlibras.gov.br/app");
  };
  document.body.appendChild(vlibrasScript);
}
document.addEventListener("DOMContentLoaded", adicionarVLibras);
