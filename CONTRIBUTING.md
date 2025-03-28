Vou criar um arquivo `CONTRIBUTING.md` para o projeto "FacilitaU" no GitHub. Este documento, conhecido como "Contributing Standards" ou "Guia de Contribuição", define as diretrizes para que outras pessoas possam contribuir com o projeto de forma clara e organizada. Ele geralmente inclui informações sobre como configurar o ambiente, o processo de envio de contribuições, padrões de código e como reportar problemas.

---

# Contribuindo com o FacilitaU

Obrigado por seu interesse em contribuir com o **FacilitaU**! Este projeto é um sistema web desenvolvido para auxiliar estudantes universitários no gerenciamento de suas atividades acadêmicas, e sua ajuda é muito bem-vinda. Este guia explica como você pode contribuir de forma eficaz e colaborar com a comunidade.

## 📜 Código de Conduta

Antes de começar, leia nosso [Código de Conduta](CODE_OF_CONDUCT.md). Ao contribuir com o FacilitaU, você concorda em seguir essas diretrizes para garantir um ambiente respeitoso e inclusivo para todos.

## 🚀 Como Contribuir

### 1. Configurando o Ambiente de Desenvolvimento

Para contribuir com o projeto, você precisará configurar o ambiente localmente. Siga os passos abaixo:

#### Pré-requisitos
- **XAMPP**: Certifique-se de que o [XAMPP](https://www.apachefriends.org/) está instalado, pois o projeto usa o Apache e o MySQL.
- **Editor de Texto**: Recomendamos o Notepad++ ou outro editor de sua preferência (ex.: VS Code).
- **Git**: Instale o Git para clonar o repositório e gerenciar suas contribuições.

#### Passos para Configuração
1. **Clone o Repositório**:
   ```
   git clone https://github.com/seu-usuario/facilitau.git
   ```
   Substitua `seu-usuario` pelo nome do usuário ou organização que hospeda o repositório.

2. **Configure o XAMPP**:
   - Copie a pasta `facilitau` para o diretório `htdocs` do XAMPP (ex.: `C:\xampp\htdocs\facilitau`).
   - Inicie o Apache e o MySQL no XAMPP Control Panel.
   - Certifique-se de que o banco de dados `facilitau_db` está criado no MySQL e contém as tabelas necessárias (`Faculdade`, `Estudante`, `Professor`, `Comunicado`, `Tarefa_Evento`, etc.). Você pode criar o banco de dados manualmente no phpMyAdmin ou importar um script SQL, se disponível.

3. **Teste o Projeto**:
   - Abra o navegador e acesse `http://localhost/facilitau`.
   - Verifique se o projeto está funcionando corretamente navegando pelas funcionalidades (ex.: listar faculdades, cadastrar estudantes).

### 2. Reportando Problemas

Se você encontrar um bug ou tiver uma sugestão de melhoria, abra uma **issue** no GitHub:

1. Vá para a aba "Issues" no repositório do FacilitaU.
2. Clique em "New Issue".
3. Escolha o tipo de issue (ex.: "Bug Report" ou "Feature Request").
4. Descreva o problema ou sugestão com o máximo de detalhes possível, incluindo:
   - Passos para reproduzir o problema (se for um bug).
   - Comportamento esperado e comportamento atual.
   - Capturas de tela, se aplicável.
5. Envie a issue e aguarde feedback dos mantenedores.

### 3. Enviando Contribuições

Para enviar uma contribuição (ex.: correção de bug, nova funcionalidade), siga o processo abaixo:

#### Passo 1: Faça um Fork do Repositório
- Clique no botão "Fork" no topo da página do repositório no GitHub para criar uma cópia do projeto no seu perfil.

#### Passo 2: Crie uma Branch
- Clone o repositório forkado para sua máquina local:
  ```
  git clone https://github.com/seu-usuario/facilitau.git
  cd facilitau
  ```
- Crie uma branch para sua contribuição:
  ```
  git checkout -b nome-da-sua-branch
  ```
  Use um nome descritivo, como `corrige-bug-listagem-faculdades` ou `adiciona-autenticacao`.

#### Passo 3: Faça Suas Alterações
- Abra os arquivos no Notepad++ (ou outro editor) e implemente suas alterações.
- Siga os padrões de código do projeto (veja a seção "Padrões de Código" abaixo).
- Teste suas alterações localmente acessando `http://localhost/facilitau` e verificando se tudo funciona como esperado.

#### Passo 4: Commit e Push
- Adicione suas alterações ao Git:
  ```
  git add .
  ```
- Faça um commit com uma mensagem clara e descritiva:
  ```
  git commit -m "Corrige bug na listagem de faculdades"
  ```
- Envie sua branch para o repositório forkado:
  ```
  git push origin nome-da-sua-branch
  ```

#### Passo 5: Abra um Pull Request
- Vá para o repositório original no GitHub.
- Clique em "Pull Requests" e depois em "New Pull Request".
- Selecione sua branch (`nome-da-sua-branch`) e a branch `main` do repositório original.
- Descreva suas alterações no pull request, incluindo:
  - O que foi alterado.
  - Por que a alteração foi feita.
  - Referências a issues relacionadas (ex.: "Fecha #12").
- Envie o pull request e aguarde revisão dos mantenedores.

## 📝 Padrões de Código

Para manter a consistência no projeto, siga estas diretrizes ao contribuir:

- **Estilo de Código**:
  - Use indentação com 4 espaços (não use tabs).
  - Nomeie variáveis e arquivos de forma clara e descritiva (ex.: `listar_tarefas_eventos.php`).
  - Comente o código quando necessário, especialmente em trechos complexos.
- **PHP**:
  - Sempre inclua verificações de erro (ex.: verificar se uma consulta SQL foi bem-sucedida).
  - Use prepared statements para consultas SQL quando possível, para evitar SQL injection.
  - Feche as conexões com o banco de dados ao final de cada script (`$conn->close()`).
- **HTML/CSS**:
  - Mantenha o HTML semântico e acessível.
  - Use o arquivo `style.css` para estilização, evitando CSS inline.
- **Commits**:
  - Escreva mensagens de commit claras e no formato: "Verbo + descrição" (ex.: "Adiciona página de cadastro de prazos").
  - Faça commits pequenos e focados em uma única alteração.

## 🛠️ Áreas para Contribuição

Aqui estão algumas áreas onde você pode contribuir:

- **Correção de Bugs**: Veja as issues abertas com a label "bug" e ajude a resolvê-las.
- **Novas Funcionalidades**: Implemente funcionalidades sugeridas, como autenticação ou cadastro de prazos institucionais.
- **Melhorias na Interface**: Adicione validações, melhore o design ou adicione feedback visual (ex.: mensagens de erro mais claras).
- **Documentação**: Melhore o `README.md`, adicione comentários ao código ou crie documentação adicional.

## ❓ Dúvidas?

Se tiver dúvidas sobre como contribuir, abra uma issue com a label "question" ou entre em contato com os mantenedores pelo e-mail [insira seu e-mail de contato].

Agradecemos por sua colaboração no FacilitaU! 🎉

