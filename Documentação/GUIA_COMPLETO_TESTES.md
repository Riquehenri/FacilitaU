# 📚 GUIA COMPLETO PARA EXECUTAR TESTES - FacilitaU

## 🎯 Visão Geral

Este guia fornece instruções **detalhadas e passo a passo** para configurar e executar todos os testes do sistema FacilitaU usando VS Code e XAMPP.

---

## 📋 Pré-requisitos

### Software Necessário

1. **XAMPP** (Apache + MySQL + PHP)
   - Download: https://www.apachefriends.org/
   - Versão recomendada: 8.2 ou superior

2. **Visual Studio Code**
   - Download: https://code.visualstudio.com/
   - Extensões recomendadas:
     - PHP Intelephense
     - PHP Debug
     - MySQL (by Jun Han)

3. **Composer** (Gerenciador de dependências PHP)
   - Download: https://getcomposer.org/download/
   - Necessário para instalar PHPUnit

---

## 🚀 PARTE 1: Configuração Inicial

### Passo 1: Instalar XAMPP

1. Baixe e instale o XAMPP
2. Inicie o **XAMPP Control Panel**
3. Inicie os serviços:
   - ✅ Apache
   - ✅ MySQL

![XAMPP Control Panel](https://via.placeholder.com/600x200?text=XAMPP+Control+Panel)

### Passo 2: Configurar o Banco de Dados

1. Abra o navegador e acesse: `http://localhost/phpmyadmin`
2. Clique em "Novo" para criar um banco de dados
3. Nome do banco: `facilitau_db`
4. Clique em "Criar"
5. Selecione o banco `facilitau_db`
6. Clique na aba "SQL"
7. Copie e cole o conteúdo do arquivo `Banco de dados/Modelo Físico.sql`
8. Clique em "Executar"

**Verificação:** Você deve ver as seguintes tabelas criadas:
- Usuarios
- Cursos
- Avisos
- Planejamento_Estudos
- Tarefas_Eventos
- Notificacoes
- Documentos
- Perguntas_Respostas
- Interacoes_Assistente

### Passo 3: Configurar o Projeto no XAMPP

1. Localize a pasta de instalação do XAMPP (geralmente `C:\xampp`)
2. Navegue até `C:\xampp\htdocs`
3. Copie a pasta do projeto `FacilitaUmain` para dentro de `htdocs`
4. O caminho final deve ser: `C:\xampp\htdocs\FacilitaUmain`

### Passo 4: Configurar Arquivo config.php

1. Abra o arquivo `Codigo/config.php` no VS Code
2. Verifique se as configurações estão corretas:

\`\`\`php
<?php
$servername = "localhost";
$username = "root";
$password = "";  // Senha padrão do XAMPP é vazia
$dbname = "facilitau_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
\`\`\`

### Passo 5: Instalar Composer

1. Baixe o instalador do Composer: https://getcomposer.org/Composer-Setup.exe
2. Execute o instalador
3. Siga as instruções (use o PHP do XAMPP: `C:\xampp\php\php.exe`)
4. Após a instalação, abra o **Prompt de Comando** (CMD)
5. Digite `composer --version` para verificar a instalação

### Passo 6: Instalar PHPUnit

1. Abra o **Terminal** no VS Code (Ctrl + `)
2. Navegue até a pasta do projeto:
   \`\`\`bash
   cd C:\xampp\htdocs\FacilitaUmain
   \`\`\`

3. Instale as dependências do Composer:
   \`\`\`bash
   composer install
   \`\`\`

4. Verifique se o PHPUnit foi instalado:
   \`\`\`bash
   vendor/bin/phpunit --version
   \`\`\`

   Você deve ver algo como: `PHPUnit 10.5.x`

---

## 🧪 PARTE 2: Executar os Testes

### Estrutura dos Testes

\`\`\`
FacilitaUmain/
├── Codigo/
│   ├── Auth.php
│   ├── EventManager.php
│   ├── TaskManager.php
│   ├── ScheduleValidator.php
│   └── NotificationManager.php
├── tests/
│   ├── AuthTest.php
│   ├── EventConflictPerformanceTest.php
│   ├── TaskManagerTest.php
│   ├── ScheduleValidatorTest.php
│   └── NotificationManagerTest.php
├── composer.json
└── phpunit.xml
\`\`\`

---

### TESTE 1: Autenticação de Usuário

**Objetivo:** Testar a função `authenticateUser($email, $senha)` com credenciais válidas.

#### Executar o Teste

1. Abra o terminal no VS Code
2. Execute:
   \`\`\`bash
   vendor/bin/phpunit tests/AuthTest.php
   \`\`\`

#### Resultado Esperado

\`\`\`
PHPUnit 10.5.x

.......                                                             7 / 7 (100%)

Time: 00:00.123, Memory: 6.00 MB

OK (7 tests, 15 assertions)
\`\`\`

#### O que está sendo testado?

- ✅ Login com credenciais válidas (user@exemplo.com / Abcd1234!)
- ✅ Login com senha incorreta
- ✅ Login com email inexistente
- ✅ Validação de campos vazios
- ✅ Validação de formato de email
- ✅ Validação de força de senha
- ✅ Funções auxiliares

---

### TESTE 2: Performance de Conflito de Eventos

**Objetivo:** 
1. Logar como estudante
2. Criar evento em horário X (10:00-11:00)
3. Tentar inserir 100 eventos no mesmo horário
4. Medir tempo de verificação de conflito
5. Verificar mensagem

#### Executar o Teste

\`\`\`bash
vendor/bin/phpunit tests/EventConflictPerformanceTest.php
\`\`\`

#### Resultado Esperado

\`\`\`
PHPUnit 10.5.x

=== RESULTADOS DO TESTE DE PERFORMANCE ===
Eventos criados: 100
Tempo total: 2345.67 ms
Tempo médio por evento: 23.46 ms

=== RESULTADOS DA VERIFICAÇÃO DE CONFLITO ===
Conflito detectado: SIM
Tempo de verificação: 156.78 ms
Eventos conflitantes: 100
Mensagem: Conflito detectado com: Evento de Teste #1, Evento de Teste #2, ...

...                                                                 3 / 3 (100%)

Time: 00:02.500, Memory: 8.00 MB

OK (3 tests, 8 assertions)
\`\`\`

#### O que está sendo testado?

- ✅ Criação de evento inicial
- ✅ Inserção em massa de 100 eventos
- ✅ Medição de tempo de criação
- ✅ Verificação de conflitos
- ✅ Performance da verificação (< 1 segundo)

---

### TESTE 3: Salvar Tarefa (saveTask)

**Objetivo:**
1. Configurar PHPUnit
2. Criar teste para função `saveTask($tarefaData)`
3. Passar dados válidos (título, descrição, data)
4. Usar mock de banco de dados
5. Executar e verificar inserção

#### Executar o Teste

\`\`\`bash
vendor/bin/phpunit tests/TaskManagerTest.php
\`\`\`

#### Resultado Esperado

\`\`\`
PHPUnit 10.5.x

.....                                                               5 / 5 (100%)

Time: 00:00.089, Memory: 6.00 MB

OK (5 tests, 12 assertions)
\`\`\`

#### O que está sendo testado?

- ✅ Salvar tarefa com dados válidos
- ✅ Falhar sem título
- ✅ Falhar sem data
- ✅ Falhar com data inválida
- ✅ Usar valores padrão quando não fornecidos

---

### TESTE 4: Validação de Horários (validateSchedule)

**Objetivo:**
1. Configurar PHPUnit
2. Criar teste para função `validateSchedule($horarios)`
3. Passar horários conflitantes (08:00-10:00, 09:00-11:00)
4. Executar teste

#### Executar o Teste

\`\`\`bash
vendor/bin/phpunit tests/ScheduleValidatorTest.php
\`\`\`

#### Resultado Esperado

\`\`\`
PHPUnit 10.5.x

......                                                              6 / 6 (100%)

Time: 00:00.045, Memory: 6.00 MB

OK (6 tests, 14 assertions)
\`\`\`

#### O que está sendo testado?

- ✅ Detectar horários conflitantes
- ✅ Validar horários sem conflito
- ✅ Detectar múltiplos conflitos
- ✅ Validar formato de horário
- ✅ Lidar com lista vazia
- ✅ Horários adjacentes (não conflitam)

---

### TESTE 5: Geração de Notificações (generateNotification)

**Objetivo:**
1. Configurar PHPUnit
2. Criar teste para função `generateNotification($type, $data)`
3. Passar tipo e dados válidos
4. Executar teste com mock

#### Executar o Teste

\`\`\`bash
vendor/bin/phpunit tests/NotificationManagerTest.php
\`\`\`

#### Resultado Esperado

\`\`\`
PHPUnit 10.5.x

.....                                                               5 / 5 (100%)

Time: 00:00.067, Memory: 6.00 MB

OK (5 tests, 11 assertions)
\`\`\`

#### O que está sendo testado?

- ✅ Gerar notificação com dados válidos
- ✅ Falhar com tipo inválido
- ✅ Falhar sem usuário
- ✅ Falhar sem mensagem
- ✅ Usar valores padrão

---

## 🎯 PARTE 3: Executar Todos os Testes de Uma Vez

### Comando Único

\`\`\`bash
vendor/bin/phpunit
\`\`\`

### Resultado Esperado

\`\`\`
PHPUnit 10.5.x

.............................                                      29 / 29 (100%)

Time: 00:03.456, Memory: 10.00 MB

OK (29 tests, 60 assertions)
\`\`\`

---

## 📊 PARTE 4: Relatórios e Cobertura

### Gerar Relatório Detalhado

\`\`\`bash
vendor/bin/phpunit --testdox
\`\`\`

### Resultado

\`\`\`
Auth
 ✔ Authenticate user com credenciais validas
 ✔ Authenticate user com senha incorreta
 ✔ Authenticate user com email inexistente
 ✔ Validate email com formato valido
 ✔ Validate email com formato invalido
 ✔ Validate password strength senha forte
 ✔ Validate password strength senha fraca

Event Conflict Performance
 ✔ Criar evento inicial
 ✔ Inserir100 eventos mesmo horario
 ✔ Verificar conflito com muitos eventos

Task Manager
 ✔ Save task com dados validos
 ✔ Save task sem titulo
 ✔ Save task sem data
 ✔ Save task com data invalida
 ✔ Save task com valores padrao

Schedule Validator
 ✔ Horarios conflitantes
 ✔ Horarios sem conflito
 ✔ Multiplos conflitos
 ✔ Formato horario invalido
 ✔ Lista vazia
 ✔ Horarios adjacentes

Notification Manager
 ✔ Generate notification com dados validos
 ✔ Generate notification com tipo invalido
 ✔ Generate notification sem usuario
 ✔ Generate notification sem mensagem
 ✔ Generate notification com valores padrao
\`\`\`

---

## 🐛 PARTE 5: Solução de Problemas

### Problema 1: "Class not found"

**Erro:**
\`\`\`
Error: Class 'EventManager' not found
\`\`\`

**Solução:**
1. Verifique se o arquivo existe em `Codigo/EventManager.php`
2. Verifique o `require_once` no teste
3. Execute `composer dump-autoload`

### Problema 2: "Connection refused"

**Erro:**
\`\`\`
mysqli::__construct(): (HY000/2002): Connection refused
\`\`\`

**Solução:**
1. Verifique se o MySQL está rodando no XAMPP
2. Verifique as credenciais em `config.php`
3. Teste a conexão: `http://localhost/phpmyadmin`

### Problema 3: "Table doesn't exist"

**Erro:**
\`\`\`
Table 'facilitau_db.Usuarios' doesn't exist
\`\`\`

**Solução:**
1. Execute o script SQL novamente no phpMyAdmin
2. Verifique se o banco `facilitau_db` foi criado
3. Verifique se todas as tabelas foram criadas

### Problema 4: PHPUnit não encontrado

**Erro:**
\`\`\`
'vendor/bin/phpunit' is not recognized
\`\`\`

**Solução:**
1. Execute `composer install` novamente
2. Verifique se a pasta `vendor` existe
3. Use o caminho completo: `./vendor/bin/phpunit`

---

## 📝 PARTE 6: Comandos Úteis

### Executar teste específico

\`\`\`bash
vendor/bin/phpunit tests/AuthTest.php --filter testAuthenticateUserComCredenciaisValidas
\`\`\`

### Executar com mais detalhes

\`\`\`bash
vendor/bin/phpunit --verbose
\`\`\`

### Executar e parar no primeiro erro

\`\`\`bash
vendor/bin/phpunit --stop-on-failure
\`\`\`

### Limpar cache do Composer

\`\`\`bash
composer clear-cache
composer dump-autoload
\`\`\`

---

## ✅ Checklist Final

Antes de executar os testes, verifique:

- [ ] XAMPP instalado e rodando (Apache + MySQL)
- [ ] Banco de dados `facilitau_db` criado
- [ ] Tabelas criadas com o script SQL
- [ ] Projeto copiado para `C:\xampp\htdocs\FacilitaUmain`
- [ ] Arquivo `config.php` configurado corretamente
- [ ] Composer instalado
- [ ] Dependências instaladas (`composer install`)
- [ ] PHPUnit funcionando (`vendor/bin/phpunit --version`)

---

## 🎓 Conclusão

Agora você tem um ambiente completo de testes configurado! Você pode:

1. ✅ Testar autenticação de usuários
2. ✅ Medir performance de conflitos de eventos
3. ✅ Testar salvamento de tarefas com mocks
4. ✅ Validar horários conflitantes
5. ✅ Testar geração de notificações

**Dica:** Execute `vendor/bin/phpunit` regularmente durante o desenvolvimento para garantir que nada quebrou!

---

## 📞 Suporte

Se encontrar problemas:

1. Verifique a seção "Solução de Problemas"
2. Consulte a documentação do PHPUnit: https://phpunit.de/
3. Verifique os logs do XAMPP em `C:\xampp\apache\logs\error.log`

**Boa sorte com seus testes! 🚀**
