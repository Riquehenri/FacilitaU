# Facilita[U]

Bem-vindo ao **Facilita[U]**, um sistema web desenvolvido para auxiliar estudantes universitários no gerenciamento de suas atividades acadêmicas. Nossa plataforma permite que o estudante utlize 
uma assistente virtual para lhe auxiliar a tirar duvidas em relação a processos universitários, documentação, atividades futuras e tambem no gerenciamento de sua vida cotidiana sendo possivel solicitar
a assistente para que cadastre tarefas e eventos futuros e organize-os em uma agenda.

## 🌟  Principais Funcionalidades 
- **Assistente Virtual**: Responde dúvidas academicas; consulta documentos necessarios para entrega; Auxilia na organização academiaca; cadastra eventos e tarefas a pedido do estudante.
- **Gerenciamento de Agenda e Tarefas**: Estudantes podem cadastrar eventos e tarefas; possibilidade de receber lembretes e notificações; planejamento de estudos personalizados.
- **Publicação de Avisos e Oportunidades**: Professores e coordenadores podem publicar avisos,eventos e oportunidades de emprego; Estudantes recebem notificações sobre novas publiçações


## 🛠️ Tecnologias Utilizadas
- **Frontend**: HTML, CSS
- **Backend**: PHP (puro, sem frameworks)
- **Banco de Dados**: MySQL (usando o XAMPP)
- **Ambiente de Desenvolvimento**: XAMPP, VScode
- **Autenticação**: Não implementada (pode ser adicionada com sessões PHP)

## 📂 Estrutura do Projeto
O projeto está organizado no diretório `C:\xampp\htdocs\facilitau` (ou no diretório correspondente do seu ambiente local). A estrutura de arquivos é a seguinte:

```
facilitau/
├── config.php              # Conexão com o banco de dados MySQL
├── index.php               # Página inicial com menu de navegação
├── faculdades.php          # Página para listar faculdades
├── estudantes.php          # Página para cadastrar estudantes
├── comunicados.php         # Página para listar comunicados por faculdade
├── cadastrar_comunicado.php # Página para cadastrar comunicados
├── cadastrar_tarefa_evento.php # Página para cadastrar tarefas/eventos
├── listar_tarefas_eventos.php # Página para listar tarefas/eventos por estudante
└── style.css               # Estilização básica da interface
```


## 📋 Banco de Dados
O projeto utiliza um banco de dados MySQL chamado `facilitau_db`. As principais tabelas são:
- `Usuários`: Armazena informações sobre os usuários do sistema, podendo ser estudantes, professores ou coodenador acadêmico.
- `Documentos`: Armazena arquivos relacionados aos estudantes ou professores, como certificados, tarefas entregues, ou outros documentos importantes.
- `Planejamento_Estudos`: Armazena informações sobre o planejamento de estudos dos estudantes, podendo incluir dados como cronogramas, matérias a estudar, e prazos.
- `Avisos`: Armazena notificações ou comunicados importantes enviados aos usuários.
- `Tarefas_Evento`: Armazena informações sobre eventos ou tarefas programadas, como prazos de entrega de trabalhos, datas de exames, ou eventos acadêmicos.
- `Notificações`: Registra todas as notificações que os usuários recebem no sistema, tais notificações podem ser geradas por eventos, atualizações ou interações do sistema.
- `Interações_Assistente`: Armazena as interações dos usuários com o assistente virtual, que pode ser utilizado para responder dúvidas, fornecer informações ou ajudar com tarefas.

Para configurar o banco de dados, importe o script SQL (se disponível) ou crie as tabelas manualmente no phpMyAdmin.

## 🖥️ Como Contribuir
1. Faça um fork deste repositório.
2. Clone o repositório para sua máquina local:
   ```
   git clone https://github.com/seu-usuario/facilitau.git
   ```
3. Faça suas alterações no código.
4. Teste localmente com o XAMPP.
5. Envie um pull request com suas contribuições.

## 📌 Próximos Passos
- Implementar autenticação para diferenciar estudantes, professores e coordenadores.
- Adicionar funcionalidade para cadastrar e listar prazos institucionais.
- Melhorar a interface com mais validações e feedback visual (ex.: mensagens de erro mais detalhadas).
- Adicionar suporte a filtros e pesquisas (ex.: buscar tarefas por data).

## 👥 Integrantes

- [Éden Samuel Bozza Hernandes](https://github.com/Eden-code01),
- [Fernando Lopes Duarte](https://github.com/Fernando-Lopes1),
- [Henrique Ricardo](https://github.com/Riquehenri),
- [Felipe Carneiro](https://github.com/FelipeCarneiroRibeiro),
- [Hugo Takeda](https://github.com/hugotakeda).

