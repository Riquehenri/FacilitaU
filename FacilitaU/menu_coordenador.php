<?php
session_start(); // Inicia a sessão para acessar variáveis de sessão
$page_title = "Menu Coordenador - Facilita U";

// Verifica se o usuário está logado e se é do tipo 'coordenador'
// Caso contrário, redireciona para a página inicial
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'coordenador') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="CSS/ia.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/configuracao.css">
    <link rel="stylesheet" href="CSS/coordenador.css">
</head>
<body>
    <!-- Botão para logout usando formulário POST -->
    <form action="logout.php" method="post">
        <button type="submit" class="logout-button">Sair</button>
    </form>

    <div class="app-container">
        <!-- Barra lateral com opções do coordenador -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-university logo"></i> <h2>Facilita U</h2>
            </div>
            
            <div class="coordenador-options">
                <h3>Menu do Coordenador</h3>
                <ul>
                    <!-- Link para cadastrar avisos -->
                    <li><a href="cadastrar_aviso.php"><i class="fas fa-bullhorn"></i> Cadastrar Aviso</a></li>
                </ul>
            </div>
            
            <!-- Botão para criar novo chat -->
            <button class="new-chat-btn">
                <i class="fas fa-plus"></i> Novo Chat
            </button>
            
            <div class="sidebar-footer">
                <!-- Botão de configurações -->
                <button class="sidebar-btn">
                    <i class="fas fa-cog"></i> Configurações
                </button>
                <!-- Botão que leva para a página de perfil do usuário -->
                <button class="sidebar-btn profile-btn">
                    <a href="perfil.php" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-user"></i> Meu Perfil</a>
                </button>
            </div>
        </aside>

        <!-- Área principal do chat -->
        <main class="chat-area">
            <div class="welcome-message" id="welcome-message">
                <i class="fas fa-graduation-cap welcome-icon"></i> 
                <!-- Saudação personalizada usando o nome do coordenador -->
                <h1>Olá, Coordenador <?php echo $_SESSION['nome']; ?>!</h1>
                <p>Como posso te ajudar na gestão do curso hoje?</p>
            </div>

            <!-- Container onde mensagens do chat aparecerão -->
            <div class="messages-container" id="messages-container">
                <!-- Mensagens serão exibidas aqui -->
            </div>

            <!-- Formulário para enviar mensagem ao chat -->
            <form class="message-form" id="message-form">
                <div class="input-wrapper">
                    <button type="button" class="input-btn" title="Opções Adicionais">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <input type="text" id="message-input" placeholder="Digite sua mensagem para Facilita U..." autocomplete="off">
                    <button type="button" class="input-btn" title="Pesquisar">
                        <i class="fas fa-search"></i>
                    </button>
                    <button type="button" class="input-btn" title="Gravar Áudio">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
                <button type="submit" class="send-btn" title="Enviar Mensagem">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <footer class="ai-footer">
                AI-generated for reference only.
            </footer>
        </main>
    </div>

    <!-- Scripts para acessibilidade, configuração e funcionalidades do chat -->
    <script src="JS/Vlibras.js"></script>
    <script src="JS/Configuracao.js"></script>
    <script src="JS/ia.js"></script>

</body>
</html>
