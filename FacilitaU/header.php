<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Define o charset e configura a responsividade -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título da aba, usando variável se definida -->
    <title><?php echo isset($page_title) ? $page_title : 'Facilita U'; ?></title>

    <!-- CSS do cabeçalho e FontAwesome para ícones -->
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <!-- Cabeçalho principal -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo com link para página inicial -->
            <a href="index.php" class="logo-link">
                <i class="fas fa-university logo-icon"></i>
                <h1 class="logo-text">Facilita U</h1>
            </a>

            <!-- Botão de voltar à página anterior -->
            <button onclick="window.history.back();" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </button>

            <!-- Informações do usuário logado -->
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="user-info">
                    <div class="user-details">
                        <!-- Exibe o nome do usuário -->
                        <div class="user-name">
                            <?php echo htmlspecialchars($_SESSION['nome']); ?>
                        </div>

                        <!-- (Opcional) Exibe o tipo de usuário, se disponível -->
                        <?php if (isset($_SESSION['tipo'])): ?>
                            <div class="user-role">
                                <?php echo ucfirst($_SESSION['tipo']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Botão para logout -->
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Início do conteúdo principal -->
    <main class="main-content">
