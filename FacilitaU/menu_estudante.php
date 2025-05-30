<?php
session_start(); // Inicia a sessão para trabalhar com variáveis de sessão
$page_title = "Menu Estudante - Facilita U";

// Verifica se o usuário está logado e se o tipo dele é 'estudante'
// Caso contrário, redireciona para a página inicial
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
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

    <!-- Arquivos CSS para estilização -->
    <link rel="stylesheet" href="CSS/ia.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/configuracao.css">
    <link rel="stylesheet" href="CSS/estudante.css">
</head>
<body>
    <!-- Formulário para logout via POST -->
    <form action="logout.php" method="post">
        <button type="submit" class="logout-button">Sair</button>
    </form>

    <div class="app-container">
        <!-- Sidebar com menu do estudante -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-university logo"></i> <h2>Facilita U</h2>
            </div>
            
            <div class="estudante-options">
                <h3>Menu do Estudante</h3>
                <ul>
                    <!-- Links para planejamento e avisos -->
                    <li><a href="planejamento_estudos.php"><i class="fas fa-calendar-alt"></i> Planejamento de Estudos</a></li>
                    <li><a href="listar_avisos.php"><i class="fas fa-bullhorn"></i> Listar Avisos</a></li>
                </ul>
            </div>
            
            <!-- Botão para iniciar um novo chat -->
            <button class="new-chat-btn">
                <i class="fas fa-plus"></i> Novo Chat
            </button>

            <div class="sidebar-footer">
                <!-- Botão de configurações -->
                <button class="sidebar-btn">
                    <i class="fas fa-cog"></i> Configurações
                </button>
                <!-- Link para o perfil do usuário -->
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
                <!-- Saudação personalizada com o nome do usuário -->
                <h1>Olá, <?php echo $_SESSION['nome']; ?>!</h1>
                <p>Sou o assistente da Facilita U. Como posso te ajudar nos estudos hoje?</p>
            </div>

            <!-- Container para exibir as mensagens do chat -->
            <div class="messages-container" id="messages-container">
                <!-- Mensagens serão exibidas aqui -->
            </div>

            <!-- Formulário para enviar mensagem no chat -->
            <form class="message-form" id="message-form">
                <div class="input-wrapper">
                    <!-- Botões para anexos, pesquisa e gravação -->
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
                <!-- Botão para enviar a mensagem -->
                <button type="submit" class="send-btn" title="Enviar Mensagem">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>

            <!-- Rodapé com aviso -->
            <footer class="ai-footer">
                AI-generated for reference only.
            </footer>
        </main>
    </div>

    <!-- Scripts JavaScript para funcionalidades do chat e acessibilidade -->
    <script src="JS/ia.js"></script>
    <script src="JS/Vlibras.js"></script>
    <script src="JS/Configuracao.js"></script>
</body>
</html>
