<?php 
include_once 'header.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Facilita U</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="container">
        <!-- Seção de Informações -->
        <section class="info-section">
            <div class="info-content">
                <div class="logo">
                    <i class="fas fa-university logo-icon"></i>
                    <span class="logo-text">Facilita U</span>
                </div>
                <h2>Bem-vindo ao Sistema Acadêmico</h2>
                <p>Gerencie suas atividades acadêmicas de forma simples e eficiente. Acesso rápido a informações, avisos e ferramentas de gestão.</p>
                
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Acesso para estudantes, professores e coordenadores</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Planejamento de estudos integrado</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Comunicação direta com a instituição</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Seção de Formulário -->
        <section class="form-section">
            <h2>Entre no Sistema</h2>
            <p>Selecione abaixo como deseja acessar o FacilitaU</p>
            
            <div class="auth-options">
                <a href="login_usuario.php" class="auth-option login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="cadastro_usuario.php" class="auth-option register">
                    <i class="fas fa-user-plus"></i> Cadastrar
                </a>
            </div>
            
            <div class="features">
                <p><strong>Novo por aqui?</strong></p>
                <p>Crie uma conta para ter acesso a todos os recursos da plataforma e comece a gerenciar sua vida acadêmica de forma mais eficiente.</p>
            </div>
        </section>
    </div>
    <script src="JS/Vlibras.js"></script>

</body>
</html>