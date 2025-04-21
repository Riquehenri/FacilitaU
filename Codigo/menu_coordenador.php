<?php
// Inicia o sistema de sessões para manter o estado do usuário
session_start();

// Define o título da página que aparecerá na aba do navegador
$page_title = "Menu Coordenador - Facilita U";

// Verifica se o usuário está logado e se é do tipo 'coordenador'
// Se não estiver logado ou não for coordenador, redireciona para a página inicial
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'coordenador') {
    header("Location: index.php");
    exit(); // Termina a execução do script
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Configurações básicas do documento HTML -->
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Configuração para dispositivos móveis -->
    
    <!-- Título dinâmico da página -->
    <title><?php echo $page_title; ?></title>
    
    <!-- Inclusão de folhas de estilo -->
    <link rel="stylesheet" href="CSS/ia.css"> <!-- Estilo principal da interface -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"> <!-- Ícones Font Awesome -->
    
    <!-- Estilos CSS internos (específicos para esta página) -->
    <style>
        /* Estilo do botão de logout */
        .logout-button {
            position: absolute; /* Posicionamento fixo */
            top: 10px; right: 10px; /* Distância do canto superior direito */
            background-color: red; /* Cor de fundo vermelha */
            color: white; /* Texto branco */
            border: none; /* Sem borda */
            padding: 10px 20px; /* Espaçamento interno */
            cursor: pointer; /* Cursor em forma de mão */
            font-size: 16px; /* Tamanho da fonte */
            border-radius: 5px; /* Bordas arredondadas */
            z-index: 1000; /* Garante que fique acima de outros elementos */
        }
        
        /* Estilo do painel de opções do coordenador */
        .coordenador-options {
            background-color: #f8f9fa; /* Cor de fundo clara */
            padding: 20px; /* Espaçamento interno */
            border-radius: 8px; /* Bordas arredondadas */
            margin: 20px 0; /* Margem superior e inferior */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Sombra sutil */
        }
        
        /* Estilo do título do painel */
        .coordenador-options h3 {
            margin-top: 0; /* Remove margem superior padrão */
            color: #3b5998; /* Cor azul para o título */
            border-bottom: 1px solid #ddd; /* Linha divisória */
            padding-bottom: 10px; /* Espaçamento abaixo do título */
        }
        
        /* Estilo da lista de opções */
        .coordenador-options ul {
            list-style-type: none; /* Remove marcadores de lista */
            padding: 0; /* Remove padding padrão */
        }
        
        /* Estilo dos itens da lista */
        .coordenador-options li {
            margin-bottom: 10px; /* Espaçamento entre itens */
        }
        
        /* Estilo dos links de opções */
        .coordenador-options a {
            display: block; /* Faz ocupar toda a linha */
            padding: 8px 15px; /* Espaçamento interno */
            background-color: #e9ecef; /* Cor de fundo */
            border-radius: 5px; /* Bordas arredondadas */
            text-decoration: none; /* Remove sublinhado */
            color: #212529; /* Cor do texto */
            transition: all 0.3s; /* Efeito de transição suave */
        }
        
        /* Efeito hover nos links */
        .coordenador-options a:hover {
            background-color: #dee2e6; /* Muda cor de fundo */
            transform: translateX(5px); /* Desloca levemente para direita */
        }
        
        /* Estilo dos ícones nos links */
        .coordenador-options a i {
            margin-right: 8px; /* Espaçamento à direita do ícone */
            color: #3b5998; /* Cor azul para os ícones */
        }
    </style>
</head>
<body>
    <!-- Formulário/botão de logout -->
    <form action="logout.php" method="post">
        <button type="submit" class="logout-button">Sair</button>
    </form>

    <!-- Container principal da aplicação -->
    <div class="app-container">
        <!-- Barra lateral (sidebar) -->
        <aside class="sidebar">
            <!-- Cabeçalho da sidebar -->
            <div class="sidebar-header">
                <i class="fas fa-university logo"></i> <!-- Ícone da universidade -->
                <h2>Facilita U</h2> <!-- Nome do sistema -->
            </div>
            
            <!-- Menu de opções do coordenador -->
            <div class="coordenador-options">
                <h3>Menu do Coordenador</h3>
                <ul>
                    <!-- Link para cadastrar avisos -->
                    <li><a href="cadastrar_aviso.php"><i class="fas fa-bullhorn"></i> Cadastrar Aviso</a></li>
                </ul>
            </div>
            
            <!-- Botão para iniciar novo chat -->
            <button class="new-chat-btn">
                <i class="fas fa-plus"></i> Novo Chat
            </button>
            
            <!-- Rodapé da sidebar -->
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
            <!-- Mensagem de boas-vindas -->
            <div class="welcome-message" id="welcome-message">
                <i class="fas fa-graduation-cap welcome-icon"></i> <!-- Ícone de formatura -->
                <h1>Olá, Coordenador <?php echo $_SESSION['nome']; ?>!</h1> <!-- Saudação personalizada -->
                <p>Como posso te ajudar na gestão do curso hoje?</p> <!-- Mensagem de ajuda -->
            </div>

            <!-- Container onde as mensagens serão exibidas -->
            <div class="messages-container" id="messages-container">
                <!-- As mensagens serão inseridas aqui dinamicamente via JavaScript -->
            </div>

            <!-- Formulário para enviar mensagens -->
            <form class="message-form" id="message-form">
                <div class="input-wrapper">
                    <!-- Botões de ação -->
                    <button type="button" class="input-btn" title="Opções Adicionais">
                        <i class="fas fa-paperclip"></i> <!-- Ícone de anexo -->
                    </button>
                    
                    <!-- Campo de input para mensagem -->
                    <input type="text" id="message-input" placeholder="Digite sua mensagem para Facilita U..." autocomplete="off">
                    
                    <!-- Botões adicionais -->
                    <button type="button" class="input-btn" title="Pesquisar">
                        <i class="fas fa-search"></i> <!-- Ícone de pesquisa -->
                    </button>
                    <button type="button" class="input-btn" title="Gravar Áudio">
                        <i class="fas fa-microphone"></i> <!-- Ícone de microfone -->
                    </button>
                </div>
                
                <!-- Botão de enviar -->
                <button type="submit" class="send-btn" title="Enviar Mensagem">
                    <i class="fas fa-paper-plane"></i> <!-- Ícone de avião de papel -->
                </button>
            </form>
            
            <!-- Rodapé da área de chat -->
            <footer class="ai-footer">
                AI-generated for reference only. <!-- Aviso sobre conteúdo gerado por IA -->
            </footer>
        </main>
    </div>

    <!-- Inclusão do arquivo JavaScript -->
    <script src="JS/ia.js"></script>
</body>
</html>