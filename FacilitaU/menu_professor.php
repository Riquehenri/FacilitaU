<?php
session_start(); // Inicia a sessão
$page_title = "Menu Professor - Facilita U";

// Verifica se o usuário está logado e é do tipo 'professor'
// Caso contrário, redireciona para a página inicial
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'professor') {
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

    <!-- Arquivos CSS para estilização da página -->
    <link rel="stylesheet" href="CSS/ia.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/configuracao.css">
    <link rel="stylesheet" href="CSS/professor.css">
</head>
<body>
    <!-- Botão para sair da sessão -->
    <form action="logout.php" method="post">
        <button type="submit" class="logout-button">Sair</button>
    </form>

    <div class="app-container">
        <!-- Sidebar lateral com menu -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-university logo"></i> <h2>Facilita U</h2>
            </div>
            
            <div class="professor-options">
                <h3>Menu do Professor</h3>
                <ul>
                    <!-- Link para cadastrar aviso -->
                    <li><a href="cadastrar_aviso.php"><i class="fas fa-bullhorn"></i> Cadastrar Aviso</a></li>
                </ul>
            </div>
            
            <!-- Botão para iniciar um novo chat -->
            <button class="new-chat-btn">
                <i class="fas fa-plus"></i> Novo Chat
            </button>
            
            <!-- Rodapé da sidebar com configurações e perfil -->
            <div class="sidebar-footer">
                <button class="sidebar-btn">
                    <i class="fas fa-cog"></i> Configurações
                </button>
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
                <!-- Saudação personalizada -->
                <h1>Olá, Professor <?php echo $_SESSION['nome']; ?>!</h1>
                <p>Sou o assistente da Facilita U. Como posso te ajudar hoje?</p>
            </div>

            <!-- Container onde as mensagens do chat serão exibidas -->
            <div class="messages-container" id="messages-container">
                <!-- Mensagens aparecerão aqui -->
            </div>

            <!-- Formulário para enviar mensagens -->
            <form class="message-form" id="message-form">
                <div class="input-wrapper">
                    <!-- Botões para anexar arquivos, pesquisar e gravar áudio -->
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
                <!-- Botão para enviar mensagem -->
                <button type="submit" class="send-btn" title="Enviar Mensagem">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>

            <!-- Rodapé -->
            <footer class="ai-footer">
                AI-generated for reference only.
            </footer>
        </main>
    </div>

    <!-- Scripts para funcionalidade do chat e acessibilidade -->
    <script src="JS/ia.js"></script>
    <script src="JS/Vlibras.js"></script>
    <script src="JS/Configuracao.js"></script>
</body>
</html>
