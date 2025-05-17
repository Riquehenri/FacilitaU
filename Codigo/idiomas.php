<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['idioma'])) {
    $_SESSION['idioma'] = 'pt';
}
$idioma = $_SESSION['idioma'];

function traduzir($chave) {
    $traducao = [
        'pt' => [
            "verificar_2fa" => "Verificação de Dois Fatores",
            "codigo_2fa" => "Código 2FA",
            "verificar" => "Verificar",
            "codigo_enviado" => "Enviamos um código para o seu e-mail. Insira o código abaixo:",
            "codigo_expirado" => "Código expirado. Um novo código foi enviado.",
            "codigo_invalido" => "Código inválido.",
            "erro_enviar_2fa" => "Erro ao enviar o código 2FA.",
            "menu_estudante" => "Menu do Estudante",
            "menu_professor" => "Menu do Professor",
            "menu_coordenador" => "Menu do Coordenador",
            "escolha_opcao" => "Escolha uma opção:",
            "sair" => "Sair",
            "tema_claro" => "Claro",
            "tema_escuro" => "Escuro",
            "cadastro_usuario" => "Cadastro de Usuário",
            "nome" => "Nome",
            "email" => "E-mail",
            "senha" => "Senha",
            "tipo" => "Tipo",
            "estudante" => "Estudante",
            "professor" => "Professor",
            "coordenador" => "Coordenador",
            "cadastrar" => "Cadastrar",
            "login_usuario" => "Login de Usuário",
            "logar" => "Logar",
            "usuario_cadastrado_sucesso" => "Usuário cadastrado com sucesso!",
            "erro_cadastrar_usuario" => "Erro ao cadastrar usuário.",
            "email_ja_cadastrado" => "Este e-mail já está cadastrado."
        ],
        'en' => [
            "verificar_2fa" => "Two-Factor Authentication",
            "codigo_2fa" => "2FA Code",
            "verificar" => "Verify",
            "codigo_enviado" => "We sent a code to your email. Enter the code below:",
            "codigo_expirado" => "Code expired. A new code has been sent.",
            "codigo_invalido" => "Invalid code.",
            "erro_enviar_2fa" => "Error sending the 2FA code.",
            "menu_estudante" => "Student Menu",
            "menu_professor" => "Professor Menu",
            "menu_coordenador" => "Coordinator Menu",
            "escolha_opcao" => "Choose an option:",
            "sair" => "Logout",
            "tema_claro" => "Light",
            "tema_escuro" => "Dark",
            "cadastro_usuario" => "User Registration",
            "nome" => "Name",
            "email" => "Email",
            "senha" => "Password",
            "tipo" => "Type",
            "estudante" => "Student",
            "professor" => "Professor",
            "coordenador" => "Coordinator",
            "cadastrar" => "Register",
            "login_usuario" => "User Login",
            "logar" => "Login",
            "usuario_cadastrado_sucesso" => "User registered successfully!",
            "erro_cadastrar_usuario" => "Error registering user.",
            "email_ja_cadastrado" => "This email is already registered."
        ]
    ];
    return $traducao[$_SESSION['idioma']][$chave] ?? $chave;
}
?>