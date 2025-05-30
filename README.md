# FacilitaU

Bem-vindo ao **FacilitaU**, um sistema web criado para auxiliar estudantes universitários no gerenciamento de suas atividades acadêmicas e rotinas. O sistema oferece funcionalidades para estudantes, professores e coordenadores, promovendo uma comunicação eficiente e gestão inteligente das tarefas diárias, através de uma assistente virtual integrada.

![Status do Projeto](https://img.shields.io/badge/Status-Em%20Desenvolvimento-yellow) ![Sprint](https://img.shields.io/badge/Sprint-2-green)

## 🌟 Principais Funcionalidades

### Sprint 2 (Atual)

- **Assistente Virtual:**

  - Responde dúvidas frequentes acadêmicas com base em uma base de dados de perguntas e documentos.
  - Registra interações dos usuários com a assistente.

- **Gerenciamento de Tarefas e Eventos:**

  - Estudantes podem cadastrar tarefas e eventos (provas, palestras, trabalhos).
  - Sistema envia lembretes automáticos para eventos do dia seguinte.

- **Gestão de Documentos:**

  - Upload e consulta de documentos acadêmicos como contratos e regulamentos.

- **Notificações Automatizadas:**
  - Envio de notificações para avisos e lembretes de eventos/tarefas.
  - Notificações pendentes são exibidas para os usuários.

### Funcionalidades da Sprint 1

- **Autenticação de Usuários:**

  - Cadastro de estudantes, professores e coordenadores.
  - Login com validação e recuperação de senha via e-mail.

- **Planejamento de Estudos:**

  - Estudantes podem criar rotinas semanais de estudo com horários e atividades.

- **Gestão de Avisos:**
  - Professores e coordenadores publicam avisos e oportunidades.
  - Estudantes recebem notificações automáticas.

### Funcionalidades Futuras

- Filtros e busca por data, tipo ou título.
- Interface mais interativa com validações em tempo real.
- Painel administrativo para coordenadores.

## 🛠️ Tecnologias Utilizadas

- **Frontend:** HTML, CSS
- **Backend:** PHP (sem frameworks)
- **Banco de Dados:** MySQL (via XAMPP)
- **Ambiente de Desenvolvimento:** XAMPP, Visual Studio Code

## 📂 Estrutura de Diretórios

```
facilitau/
└── FacilitaU/
    └── Codigo/
        ├── config.php
        ├── index.php
        ├── login_usuario.php
        ├── cadastro_usuario.php
        ├── menu_estudante.php
        ├── menu_professor.php
        ├── menu_coordenador.php
        ├── planejamento_estudos.php
        ├── cadastrar_aviso.php
        ├── listar_avisos.php
        ├── calendario.php
        ├── editar_perfil.php
        ├── perfil.php
        ├── header.php
        ├── logout.php
        └── recuperar_senha.php
```

## 🧠 Banco de Dados `facilitau_db`

### Tabelas Novas na Sprint 2

- **Documentos**: Armazena arquivos como contratos e regulamentos.
- **Perguntas_Respostas**: Base de conhecimento para a assistente.
- **Tarefas_Eventos**: Tarefas e eventos acadêmicos por estudante.
- **Interacoes_Assistente**: Histórico de interações com a assistente.

### Views Adicionadas

- `TarefasEventosProximos`, `DocumentosPorTipo`, `InteracoesPorEstudante`, `UsuariosAtivos`, `AvisosPorTipo`

### Procedures Novas

- `InserirNotificacaoLembrete()`: Cria lembretes automáticos.
- `CadastrarTarefaEvento(...)`: Cadastra evento/tarefa e agenda lembrete.
- `RegistrarInteracaoAssistente(...)`: Salva histórico com a assistente.

## 🚀 Como Executar

1. **Pré-requisitos**:

   - XAMPP com Apache e MySQL ativos.

2. **Clonar o Projeto**:

   ```bash
   git clone https://github.com/Riquehenri/FacilitaU.git
   ```

3. **Coloque os Arquivos** em:

   ```
   C:\xampp\htdocs\facilitau\FacilitaU\Codigo
   ```

4. **Banco de Dados**:

   - Acesse `http://localhost/phpmyadmin`
   - Importe o arquivo `Modelo Físico.sql`

5. **Execute o Sistema**:

   - Acesse no navegador:
     ```
     http://localhost/facilitau/FacilitaU/Codigo
     ```

6. **Credenciais de Teste**:
   - Estudante: `estudante1@facilitau.com` / Senha: `senha123`
   - Professor: `professor1@facilitau.com` / Senha: `senha123`
   - Coordenador: `coordenador1@facilitau.com` / Senha: `senha123`

## 🤝 Contribuindo

1. Fork no GitHub e clone localmente:

   ```bash
   git clone https://github.com/Riquehenri/FacilitaU.git
   ```

2. Crie uma branch e implemente:

   ```bash
   git checkout -b minha-contribuicao
   ```

3. Commit e push:

   ```bash
   git add .
   git commit -m "Nova funcionalidade"
   git push origin minha-contribuicao
   ```

4. Abra um Pull Request no GitHub.

## 📌 Roadmap

- Adicionar filtros de busca por título, data e tipo.
- Melhorias visuais na interface do usuário.
- Painel de estatísticas para professores e coordenadores.

## 👥 Equipe

- [Éden Samuel Bozza Hernandes](https://github.com/Eden-code01)
- [Felipe Carneiro](https://github.com/FelipeCarneiroRibeiro)
- [Fernando Lopes Duarte](https://github.com/Fernando-Lopes1)
- [Henrique Ricardo](https://github.com/Riquehenri)
- [Hugo Takeda](https://github.com/hugotakeda)

---

📚 Projeto acadêmico em constante evolução — contribuições e feedbacks são bem-vindos!
