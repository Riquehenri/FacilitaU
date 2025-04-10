<?php
    session_start();
    //print_r($_SESSION);
    if ((!isset($_SESSION['email']) == true) and (!isset($_SESSION['senha']) == true))
    {
        unset($_SESSION['email']);
        unset($_SESSION['senha']);
        header('Location: login.php');
    }
    $logado = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilita U - Assistente</title>
    <link rel="stylesheet" href="CSS/ia.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .logout-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            z-index: 1000; /* Adicionado para garantir que fique sobre outros elementos */
        }
    </style>
</head>
<body>  <form action="sair.php" method="post">
        <button type="submit" class="logout-button">Sair</button>
    </form>

    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-university logo"></i> <h2>Facilita U</h2>
            </div>
            <button class="new-chat-btn">
                <i class="fas fa-plus"></i> Novo Chat
            </button>
            <nav class="chat-history">
                <p class="history-title">Hoje</p>
                <ul>
                    <li><a href="#">Organização Curricular</a></li>
                    <li><a href="#">Dúvidas sobre Matrícula</a></li>
                </ul>
                <p class="history-title">Últimos 7 Dias</p>
                <ul>
                    <li><a href="#">Horário das Aulas</a></li>
                    <li><a href="#">Informações de Contato</a></li>
                </ul>
                 <p class="history-title">Últimos 30 Dias</p>
                 <ul>
                    <li><a href="#">Procedimentos Biblioteca</a></li>
                    <li><a href="#">Requerimento Online</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                 <button class="sidebar-btn">
                     <i class="fas fa-download"></i> Baixar App <span class="new-badge">NOVO</span>
                 </button>
                 <button class="sidebar-btn profile-btn">
                     <i class="fas fa-user"></i> Meu Perfil
                 </button>
            </div>
        </aside>

        <main class="chat-area">
            <div class="welcome-message" id="welcome-message">
                 <i class="fas fa-graduation-cap welcome-icon"></i> <h1>Olá! Sou o assistente da Facilita U.</h1>
                 <p>Como posso te ajudar hoje?</p>
            </div>

            <div class="messages-container" id="messages-container">
                </div>

            <form class="message-form" id="message-form">
                <div class="input-wrapper">
                    <button type="button" class="input-btn" title="Opções Adicionais">
                        <i class="fas fa-paperclip"></i> </button>
                    <input type="text" id="message-input" placeholder="Digite sua mensagem para Facilita U..." autocomplete="off">
                     <button type="button" class="input-btn" title="Pesquisar">
                         <i class="fas fa-search"></i> </button>
                    <button type="button" class="input-btn" title="Gravar Áudio">
                         <i class="fas fa-microphone"></i> </button>
                </div>
                <button type="submit" class="send-btn" title="Enviar Mensagem">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
             <footer class="ai-footer">
                AI-generated for reference only. </footer>
        </main>
    </div>

    <script src="JS/ia.js"></script>
</body> </html>