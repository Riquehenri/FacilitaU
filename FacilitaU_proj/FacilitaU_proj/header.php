<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Facilita U'; ?></title>
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo-link">
                <i class="fas fa-university logo-icon"></i>
                <h1 class="logo-text">Facilita U</h1>
            </a>
            
            <button onclick="window.history.back();" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </button>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
            <div class="user-info">
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['nome']); ?></div>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="main-content">