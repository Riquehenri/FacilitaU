<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Configurações básicas do documento HTML -->
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres como UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configura viewport para responsividade -->
    
    <!-- Título dinâmico da página - usa $page_title se existir, caso contrário usa 'Facilita U' -->
    <title><?php echo isset($page_title) ? $page_title : 'Facilita U'; ?></title>
    
    <!-- Inclui folha de estilo do cabeçalho -->
    <link rel="stylesheet" href="CSS/header.css">
    <!-- Inclui Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <!-- Cabeçalho principal da aplicação -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo/Link para a página inicial -->
            <a href="index.php" class="logo-link">
                <!-- Ícone da universidade -->
                <i class="fas fa-university logo-icon"></i>
                <!-- Texto do logo -->
                <h1 class="logo-text">Facilita U</h1>
            </a>
            
            <!-- Botão Voltar - usa JavaScript para navegar para a página anterior -->
            <button onclick="window.history.back();" class="btn-back">
                <i class="fas fa-arrow-left"></i> <!-- Ícone de seta -->
                <span>Voltar</span> <!-- Texto do botão -->
            </button>
            
            <!-- Seção de informações do usuário (só aparece se logado) -->
            <?php if (isset($_SESSION['usuario_id'])): ?>
            <div class="user-info">
                <div class="user-details">
                    <!-- Mostra o nome do usuário (com htmlspecialchars para segurança) -->
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['nome']); ?></div>
                </div>
                <!-- Botão de logout -->
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> <!-- Ícone de sair -->
                    <span>Sair</span> <!-- Texto do botão -->
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Container principal do conteúdo (será preenchido pelas páginas que incluem este header) -->
    <main class="main-content"></main>