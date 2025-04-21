<?php 
// Inclui o arquivo de cabeçalho uma única vez para evitar duplicações
include_once 'header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Configuração básica do documento HTML -->
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres como UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configuração para dispositivos móveis -->
    
    <!-- Título da página exibido na aba do navegador -->
    <title>Login - Facilita U</title>
    
    <!-- Inclusão de bibliotecas externas -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"> <!-- Biblioteca de ícones Font Awesome -->
    <link rel="stylesheet" href="css/index.css"> <!-- Folha de estilos principal -->
</head>
<body>
    <!-- Container principal que envolve todo o conteúdo -->
    <div class="container">
        <!-- Seção esquerda - Apresentação e informações -->
        <section class="info-section">
            <div class="info-content">
                <!-- Logo e nome da aplicação -->
                <div class="logo">
                    <i class="fas fa-university logo-icon"></i> <!-- Ícone de universidade -->
                    <span class="logo-text">Facilita U</span> <!-- Nome do sistema -->
                </div>
                
                <!-- Mensagem de boas-vindas -->
                <h2>Bem-vindo ao Sistema Acadêmico</h2>
                <p>Gerencie suas atividades acadêmicas de forma simples e eficiente. Acesso rápido a informações, avisos e ferramentas de gestão.</p>
                
                <!-- Lista de features/benefícios -->
                <div class="features">
                    <!-- Primeiro benefício -->
                    <div class="feature">
                        <i class="fas fa-check-circle feature-icon"></i> <!-- Ícone de check -->
                        <span>Acesso para estudantes, professores e coordenadores</span> <!-- Descrição -->
                    </div>
                    <!-- Segundo benefício -->
                    <div class="feature">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Planejamento de estudos integrado</span>
                    </div>
                    <!-- Terceiro benefício -->
                    <div class="feature">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Comunicação direta com a instituição</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Seção direita - Formulário de acesso -->
        <section class="form-section">
            <!-- Título da seção -->
            <h2>Entre no Sistema</h2>
            <p>Selecione abaixo como deseja acessar o FacilitaU</p>
            
            <!-- Opções de autenticação -->
            <div class="auth-options">
                <!-- Botão de Login -->
                <a href="login_usuario.php" class="auth-option login">
                    <i class="fas fa-sign-in-alt"></i> <!-- Ícone de login -->
                    Login <!-- Texto do botão -->
                </a>
                <!-- Botão de Cadastro -->
                <a href="cadastro_usuario.php" class="auth-option register">
                    <i class="fas fa-user-plus"></i> <!-- Ícone de cadastro -->
                    Cadastrar <!-- Texto do botão -->
                </a>
            </div>
            
            <!-- Mensagem para novos usuários -->
            <div class="features">
                <p><strong>Novo por aqui?</strong></p>
                <p>Crie uma conta para ter acesso a todos os recursos da plataforma e comece a gerenciar sua vida acadêmica de forma mais eficiente.</p>
            </div>
        </section>
    </div>
</body>
</html>