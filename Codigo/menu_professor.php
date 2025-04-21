<?php
// Inicia a sessão para armazenar informações do usuário
session_start();

// Define o título da página que aparecerá na aba do navegador
$page_title = "Menu Professor - Facilita U";

// Verifica se o usuário está logado e se é do tipo 'professor'
// Se não estiver logado ou não for professor, redireciona para a página inicial
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'professor') {
    header("Location: index.php");
    exit(); // Termina a execução do script
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Configurações básicas da página -->
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configuração para dispositivos móveis -->
    
    <!-- Título da página (dinâmico) -->
    <title><?php echo $page_title; ?></title>
    
    <!-- Inclui folhas de estilo -->
    <link rel="stylesheet" href="CSS/ia.css"> <!-- Estilo principal -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"> <!-- Ícones Font Awesome -->
    
    <!-- Estilos CSS específicos para esta página -->
    <style>
        /* Estilo do botão de logout */
        .logout-button {
            position: absolute; /* Posição fixa */
            top: 10px; right: 10px; /* Distância do canto */
            background-color: red; /* Cor vermelha */
            color: white; /* Texto branco */
            border: none; /* Sem borda */
            padding: 10px 20px; /* Espaçamento interno */
            cursor: pointer; /* Cursor em forma de mão */
            font-size: 16px; /* Tamanho da fonte */
            border-radius: 5px; /* Bordas arredondadas */
            z-index: 1000; /* Garante que fique acima de outros elementos */
        }
        
        /* Estilo do painel de opções do professor */
        .professor-options {
            background-color: #f8f9fa; /* Cor de fundo */
            padding: 20px; /* Espaçamento interno */
            border-radius: 8px; /* Bordas arredondadas */
            margin: 20px 0; /* Margem externa */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Sombra sutil */
        }
        
        /* Estilo do título do painel */
        .professor-options h3 {
            margin-top: 0; /* Remove margem superior */
            color: #3b5998; /* Cor azul */
            border-bottom: 1px solid #ddd; /* Linha divisória */
            padding-bottom: 10px; /* Espaçamento abaixo do título */
        }
        
        /* Estilo da lista de opções */
        .professor-options ul {
            list-style-type: none; /* Remove marcadores */
            padding: 0; /* Remove padding padrão */
        }
        
        /* Estilo dos itens da lista */
        .professor-options li {
            margin-bottom: 10px; /* Espaço entre itens */
        }
        
        /* Estilo dos links */
        .professor-options a {
            display: block; /* Ocupa toda a linha */
            padding: 8px 15px; /* Espaçamento interno */
            background-color: #e9ecef; /* Cor de fundo */
            border-radius: 5px; /* Bordas arredondadas */
            text-decoration: none; /* Remove sublinhado */
            color: #212529; /* Cor do texto */
            transition: all 0.3s; /* Efeito de transição suave */
        }
        
        /* Efeito ao passar o mouse */
        .professor-options a:hover {
            background-color: #dee2e6; /* Muda cor de fundo */
            transform: translateX(5px); /* Desloca para direita */
        }
        
        /* Estilo dos ícones */
        .professor-options a i {
            margin-right: 8px; /* Espaço à direita do ícone */
            color: #3b5998; /* Cor azul */
        }
    </style>
</head>
<body>
    <!-- Formulário para logout -->
    <form action="logout.php" method="post">
        <button type="submit" class="logout-button">Sair</button>
    </form>

    <!-- Container principal -->
    <div class="app-container">
        <!-- Barra lateral -->
        <aside class="sidebar">
            <!-- Cabeçalho da barra lateral -->
            <div class="sidebar-header">
                <i class="fas fa-university logo"></i> <!-- Ícone da universidade -->
                <h2>Facilita U</h2> <!-- Nome do sistema -->
            </div>
            
            <!-- Opções específicas para professores -->
            <div class="professor-options">
                <h3>Menu do Professor</h3>
                <ul>
                    <!-- Link para cadastrar avisos -->
                    <li><a href="cadastrar_aviso.php"><i class="fas fa-bullhorn"></i> Cadastrar Aviso</a></li>
                </ul>
            </div>
            
            <!-- Botão para novo chat -->
            <button class="new-chat-btn">
                <i class="fas fa-plus"></i> Novo Chat
            </button>
            
            <!-- Rodapé da barra lateral -->
            <div class="sidebar-footer">
                <!-- Botão para baixar app -->
                <button class="sidebar-btn">
                    <i class="fas fa-download"></i> Baixar App <span class="new-badge">NOVO</span>
                </button>
                <!-- Botão para acessar perfil -->
                <button class="sidebar-btn profile-btn">
                    <a href="perfil.php" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-user"></i> Meu Perfil</a>
                </button>
            </div>
        </aside>

        <!-- Área principal de conteúdo -->
        <main class="chat-area">
            <!-- Mensagem de boas-vindas personalizada -->
            <div class="welcome-message" id="welcome-message">
                <i class="fas fa-graduation-cap welcome-icon"></i> <!-- Ícone de formatura -->
                <h1>Olá, Professor <?php echo $_SESSION['nome']; ?>!</h1> <!-- Saudação com nome -->
                <p>Sou o assistente da Facilita U. Como posso te ajudar hoje?</p> <!-- Mensagem de ajuda -->
            </div>

            <!-- Container para as mensagens do chat -->
            <div class="messages-container" id="messages-container">
                <!-- As mensagens serão exibidas aqui via JavaScript -->
            </div>

            <!-- Formulário para enviar mensagens -->
            <form class="message-form" id="message-form">
                <div class="input-wrapper">
                    <!-- Botão para anexos -->
                    <button type="button" class="input-btn" title="Opções Adicionais">
                        <i class="fas fa-paperclip"></i> <!-- Ícone de clipe -->
                    </button>
                    
                    <!-- Campo para digitar mensagem -->
                    <input type="text" id="message-input" placeholder="Digite sua mensagem para Facilita U..." autocomplete="off">
                    
                    <!-- Botões adicionais -->
                    <button type="button" class="input-btn" title="Pesquisar">
                        <i class="fas fa-search"></i> <!-- Ícone de pesquisa -->
                    </button>
                    <button type="button" class="input-btn" title="Gravar Áudio">
                        <i class="fas fa-microphone"></i> <!-- Ícone de microfone -->
                    </button>
                </div>
                
                <!-- Botão para enviar mensagem -->
                <button type="submit" class="send-btn" title="Enviar Mensagem">
                    <i class="fas fa-paper-plane"></i> <!-- Ícone de avião de papel -->
                </button>
            </form>
            
            <!-- Rodapé -->
            <footer class="ai-footer">
                AI-generated for reference only. <!-- Aviso sobre conteúdo gerado por IA -->
            </footer>
        </main>
    </div>

    <!-- Inclui o arquivo JavaScript -->
    <script src="JS/ia.js"></script>
</body>
</html>