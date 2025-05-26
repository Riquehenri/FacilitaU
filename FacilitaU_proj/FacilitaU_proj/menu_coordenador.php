<?php
session_start();
$page_title = "Menu Coordenador - Facilita U";


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
    <link rel="stylesheet" href="CSS/ia.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/configuracao.css">
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
            z-index: 1000;
        }
        .coordenador-options {
            background-color:rgba(0,0,0,0.1);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .coordenador-options h3 {
            margin-top: 0;
            color:rgb(251, 252, 255);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding-bottom: 10px;
        }
        .coordenador-options ul {
            list-style-type: none;
            padding: 0;
        }
        .coordenador-options li {
            margin-bottom: 10px;
        }
        .coordenador-options a {
            display: block;
            padding: 8px 15px;
            background-color: rgba(0,0,0,0.1);
            border-radius: 5px;
            text-decoration: none;
            color:rgb(255, 255, 255);
            transition: all 0.3s;
        }
        .coordenador-options a:hover {
            background-color:rgb(0, 21, 43);
            transform: translateX(5px);
        }
        .coordenador-options a i {
            margin-right: 8px;
            color:rgb(235, 242, 255);
        }
    </style>
</head>
<body>
    <form action="logout.php" method="post">
        <button type="submit" class="logout-button">Sair</button>
    </form>

    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-university logo"></i> <h2>Facilita U</h2>
            </div>
            
            <div class="coordenador-options">
                <h3>Menu do Coordenador</h3>
                <ul>
                    <li><a href="cadastrar_aviso.php"><i class="fas fa-bullhorn"></i> Cadastrar Aviso</a></li>
                
                </ul>
            </div>
            
            <button class="new-chat-btn">
                <i class="fas fa-plus"></i> Novo Chat
            </button>
            
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

        <main class="chat-area">
            <div class="welcome-message" id="welcome-message">
                <i class="fas fa-graduation-cap welcome-icon"></i> 
                <h1>Olá, Coordenador <?php echo $_SESSION['nome']; ?>!</h1>
                <p>Como posso te ajudar na gestão do curso hoje?</p>
            </div>

            <div class="messages-container" id="messages-container">
                <!-- Mensagens serão exibidas aqui -->
            </div>

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

    <script src="JS/ia.js"></script>
    <script src="JS/Vlibras.js"></script>
    <script src="JS/Configuracao.js"></script>

</body>
</html>