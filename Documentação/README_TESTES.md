# Guia de Testes PHPUnit - FacilitaU

## 1. Configuração do Ambiente PHPUnit

### Pré-requisitos
- PHP 7.4 ou superior
- Composer instalado
- MySQL/MariaDB rodando (porta 3307)
- Banco de dados `facilitau_db` criado

### Instalação

1. **Instalar dependências via Composer:**
\`\`\`bash
composer install
\`\`\`

Isso instalará o PHPUnit e todas as dependências necessárias.

## 2. Estrutura de Arquivos

\`\`\`
FacilitaUmain/
├── Codigo/
│   ├── Auth.php              # Classe de autenticação (nova)
│   ├── config.php            # Configuração do banco
│   └── login_usuario.php     # Página de login
├── tests/
│   └── AuthTest.php          # Testes da classe Auth
├── composer.json             # Configuração do Composer
├── phpunit.xml               # Configuração do PHPUnit
└── README_TESTES.md          # Este arquivo
\`\`\`

## 3. Executar os Testes

### Executar todos os testes:
\`\`\`bash
./vendor/bin/phpunit
\`\`\`

### Executar com saída detalhada:
\`\`\`bash
./vendor/bin/phpunit --verbose
\`\`\`

### Executar teste específico:
\`\`\`bash
./vendor/bin/phpunit --filter testAuthenticateUserWithValidCredentials
\`\`\`

### Executar com cobertura de código (requer Xdebug):
\`\`\`bash
./vendor/bin/phpunit --coverage-html coverage
\`\`\`

## 4. Testes Implementados

### AuthTest.php

#### ✅ Teste 1: `testAuthenticateUserWithValidCredentials`
- **Objetivo:** Verificar autenticação com credenciais válidas
- **Credenciais:** user@exemplo.com / Abcd1234!
- **Resultado esperado:** Array com dados do usuário (usuario_id, nome, email, tipo)

#### ✅ Teste 2: `testAuthenticateUserWithInvalidPassword`
- **Objetivo:** Verificar rejeição de senha incorreta
- **Resultado esperado:** `false`

#### ✅ Teste 3: `testAuthenticateUserWithNonExistentEmail`
- **Objetivo:** Verificar rejeição de email inexistente
- **Resultado esperado:** `false`

#### ✅ Teste 4: `testAuthenticateUserWithEmptyEmail`
- **Objetivo:** Verificar validação de email vazio
- **Resultado esperado:** `false`

#### ✅ Teste 5: `testAuthenticateUserWithEmptyPassword`
- **Objetivo:** Verificar validação de senha vazia
- **Resultado esperado:** `false`

#### ✅ Teste 6: `testEmailExists`
- **Objetivo:** Verificar se método detecta emails existentes
- **Resultado esperado:** `true` para email existente, `false` para inexistente

#### ✅ Teste 7: `testHashPassword`
- **Objetivo:** Verificar geração de hash de senha
- **Resultado esperado:** Hash válido e verificável

## 5. Exemplo de Saída Esperada

\`\`\`
PHPUnit 9.5.x by Sebastian Bergmann and contributors.

.......                                                             7 / 7 (100%)

Time: 00:00.234, Memory: 6.00 MB

OK (7 tests, 18 assertions)
\`\`\`

## 6. Integração com login_usuario.php

Para usar a nova classe Auth no arquivo de login existente, atualize o código:

\`\`\`php
<?php
session_start();
$page_title = "Login";
include 'config.php';
include 'header.php';

// Incluir a classe Auth
require_once 'Auth.php';
$auth = new Auth($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Usar o método authenticateUser
    $usuario = $auth->authenticateUser($email, $senha);

    if ($usuario) {
        // Armazena informações do usuário na sessão
        $_SESSION['usuario_id'] = $usuario['usuario_id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['tipo'] = $usuario['tipo'];
        
        // Redireciona para o menu correspondente
        if ($usuario['tipo'] == 'estudante') {
            header("Location: menu_estudante.php");
        } elseif ($usuario['tipo'] == 'professor') {
            header("Location: menu_professor.php");
        } else {
            header("Location: menu_coordenador.php");
        }
        exit();
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
}
$conn->close();
?>
\`\`\`

## 7. Troubleshooting

### Erro: "Class 'Auth' not found"
- Verifique se executou `composer install`
- Confirme que o arquivo `Codigo/Auth.php` existe

### Erro: "Connection refused"
- Verifique se o MySQL está rodando na porta 3307
- Confirme as credenciais em `tests/AuthTest.php`

### Erro: "Table 'Usuarios' doesn't exist"
- Execute o script SQL em `Banco de dados/Modelo Físico.sql`
- Verifique se o banco `facilitau_db` foi criado

## 8. Próximos Passos

- Adicionar testes de integração para outras funcionalidades
- Implementar testes para cadastro de usuários
- Criar testes para recuperação de senha
- Adicionar testes de validação de formulários
