

---

# FacilitaU

Bem-vindo ao **FacilitaU**, um sistema web desenvolvido para auxiliar estudantes universitários no gerenciamento de suas atividades acadêmicas. Nossa plataforma permite que os estudantes utilizem uma assistente virtual para tirar dúvidas sobre processos universitários, documentação e atividades futuras, além de gerenciar sua vida cotidiana, cadastrando tarefas, eventos e organizando-os em uma agenda.

![Status do Projeto](https://img.shields.io/badge/Status-Em%20Desenvolvimento-yellow) ![Sprint](https://img.shields.io/badge/Sprint-1-blue)

## 🌟 Principais Funcionalidades

### Sprint 1 (Concluída)
- **Registro e Autenticação:**
  - Cadastro de usuários (estudantes, professores e coordenadores).
  - Login com validação de e-mail e senha.
  - Recuperação de senha (atualização de senha via e-mail).
- **Planejamento de Estudos:**
  - Estudantes podem criar e listar planejamentos de estudo personalizados (dia da semana, horário, atividade).
- **Gestão de Avisos e Oportunidades:**
  - Professores e coordenadores podem cadastrar avisos e oportunidades.
  - Estudantes podem listar avisos publicados e recebem notificações automáticas.

### Funcionalidades Futuras
- **Assistente Virtual:** Responder dúvidas acadêmicas, consultar documentos, cadastrar eventos e tarefas.
- **Gerenciamento de Agenda e Tarefas:** Cadastrar eventos e tarefas, enviar lembretes e notificações.
- **Filtros e Pesquisas:** Buscar tarefas, eventos ou avisos por data ou categoria.

## 🛠️ Tecnologias Utilizadas
- **Frontend:** HTML, CSS
- **Backend:** PHP (puro, sem frameworks)
- **Banco de Dados:** MySQL (via XAMPP)
- **Ambiente de Desenvolvimento:** XAMPP, Notepad++ ou qualquer editor de texto
- **Autenticação:** Implementada com sessões PHP

## 📂 Estrutura do Projeto
O projeto está organizado no diretório `facilitau/FacilitaU/Codigo`. A estrutura de arquivos é a seguinte:

```
facilitau/
└── FacilitaU/
    └── Codigo/
        ├── config.php                  # Conexão com o banco de dados MySQL
        ├── header.php                  # Cabeçalho comum para todas as páginas
        ├── index.php                   # Página inicial (redireciona para login)
        ├── cadastro_usuario.php        # Página para cadastrar usuários
        ├── login_usuario.php           # Página de login
        ├── menu_estudante.php          # Menu principal para estudantes
        ├── menu_professor.php          # Menu principal para professores
        ├── menu_coordenador.php        # Menu principal para coordenadores
        ├── planejamento_estudos.php    # Página para cadastrar/listar planejamentos
        ├── cadastrar_aviso.php         # Página para cadastrar avisos
        ├── listar_avisos.php           # Página para listar avisos
        ├── logout.php                  # Script para logout
```

## 📋 Banco de Dados
O projeto utiliza um banco de dados MySQL chamado `facilitau_db`. As principais tabelas para a Sprint 1 são:

- **Usuarios:** Armazena informações de usuários (estudantes, professores, coordenadores).
  - Atributos: `usuario_id` (PK), `email`, `senha`, `tipo`, `nome`, `data_criacao`.
- **Planejamento_Estudos:** Armazena os planejamentos de estudos dos estudantes.
  - Atributos: `planejamento_id` (PK), `usuario_id` (FK), `dia_semana`, `horario_inicio`, `horario_fim`, `atividade`.
- **Avisos:** Armazena avisos e oportunidades publicados por professores e coordenadores.
  - Atributos: `aviso_id` (PK), `usuario_id` (FK), `tipo_aviso`, `titulo`, `descricao`, `data_publicacao`.
- **Notificacoes:** Registra notificações automáticas para estudantes (ex.: novos avisos).
  - Atributos: `notificacao_id` (PK), `usuario_id` (FK), `tipo_notificacao`, `mensagem`, `data_notificacao`, `enviada`, `aviso_id` (FK).

### Views e Procedures
- **Views:** `PlanejamentoPorEstudante`, `AvisosComAutor`, `NotificacoesPendentes`.
- **Procedures:** `InserirNotificacaoAviso`, `AtualizarSenhaUsuario`, `ExcluirUsuario`.

### Configuração do Banco
1. Abra o phpMyAdmin no XAMPP (`http://localhost/phpmyadmin`).
2. Crie um banco de dados chamado `facilitau_db`.
3. Execute o script SQL fornecido (se disponível) ou crie as tabelas manualmente.

## 🚀 Como Executar o Projeto
1. **Pré-requisitos:**
   - Instale o XAMPP no seu computador.
   - Certifique-se de que o Apache e o MySQL estão rodando.

2. **Clone o Repositório:**
   ```bash
   git clone https://github.com/seu-usuario/FacilitaU.git
   ```

3. **Organize os Arquivos:**
   - Coloque os arquivos do projeto em `C:\xampp\htdocs\facilitau\FacilitaU\Codigo` (ou ajuste o caminho conforme seu ambiente).

4. **Configure o Banco de Dados:**
   - Importe o script SQL para o banco `facilitau_db` no phpMyAdmin.

5. **Acesse o Projeto:**
   - Abra o navegador e vá para:
     ```
     http://localhost/facilitau/FacilitaU/Codigo
     ```
   - Use as credenciais de teste para fazer login:
     - Estudante: `estudante1@facilitau.com` / Senha: `senha123`
     - Professor: `professor1@facilitau.com` / Senha: `senha123`
     - Coordenador: `coordenador1@facilitau.com` / Senha: `senha123`

## 🖥️ Como Contribuir
1. Faça um fork deste repositório.
2. Clone o repositório para sua máquina local:
   ```bash
   git clone https://github.com/seu-usuario/FacilitaU.git
   ```
3. Crie uma branch para suas alterações:
   ```bash
   git checkout -b minha-contribuicao
   ```
4. Faça suas alterações no código.
5. Teste localmente com o XAMPP.
6. Envie um commit:
   ```bash
   git add .
   git commit -m "Minha contribuição"
   ```
7. Faça o push da sua branch:
   ```bash
   git push origin minha-contribuicao
   ```
8. Envie um pull request para revisão.

## 📌 Próximos Passos
- Implementar a assistente virtual para responder dúvidas e gerenciar tarefas.
- Adicionar cadastro e listagem de tarefas/eventos.
- Exibir notificações pendentes automaticamente.
- Melhorar a interface com validações visuais (ex.: mensagens de erro mais detalhadas).
- Adicionar suporte a filtros e pesquisas (ex.: buscar tarefas por data).

## 👥 Integrantes
- [Éden Samuel Bozza Hernandes](https://github.com/Eden-code01)
- [Fernando Lopes Duarte](https://github.com/Fernando-Lopes1)
- [Henrique Ricardo](https://github.com/Riquehenri)
- [Felipe Carneiro](https://github.com/FelipeCarneiroRibeiro)
- [Hugo Takeda](https://github.com/hugotakeda)

## 📜 Licença
Este projeto é licenciado sob a [MIT License](LICENSE).
```

---

