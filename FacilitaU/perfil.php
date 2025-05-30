<?php
session_start();

$page_title = "Meu Perfil";

// Inclui arquivo de configuração (conexão ao banco) e cabeçalho da página
include 'config.php';
include 'header.php';

// Verifica se o usuário está logado, caso contrário redireciona para a página de login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Consulta para buscar informações do usuário e nome do curso (caso exista) usando LEFT JOIN
$sql = "SELECT u.*, c.nome as nome_curso FROM Usuarios u 
        LEFT JOIN Cursos c ON u.curso_id = c.curso_id 
        WHERE u.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Caso o usuário não seja encontrado, exibe mensagem e encerra o script
if ($result->num_rows == 0) {
    die("Usuário não encontrado.");
}

// Armazena as informações do usuário em um array associativo
$usuario = $result->fetch_assoc();

// Fecha statement e conexão com o banco
$stmt->close();
$conn->close();

// Formata a data de nascimento para o formato dd/mm/aaaa ou exibe mensagem padrão caso não tenha
$data_nascimento_formatada = $usuario['data_nascimento'] ? date('d/m/Y', strtotime($usuario['data_nascimento'])) : 'Não informada';

// Formata telefone para padrão (XX) XXXXX-XXXX ou exibe mensagem padrão caso não tenha telefone
$telefone_formatado = $usuario['telefone'] ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $usuario['telefone']) : 'Não informado';

// Define ícone e cor para exibição, baseado no tipo do usuário
switch ($usuario['tipo']) {
    case 'coordenador':
        $icone = 'fa-user-shield'; // Ícone do coordenador
        $cor = '#4a90e2';          // Azul
        break;
    case 'professor':
        $icone = 'fa-user-tie';    // Ícone do professor
        $cor = '#2ecc71';          // Verde
        break;
    default:
        $icone = 'fa-user-graduate'; // Ícone do estudante (ou outro tipo)
        $cor = '#e74c3c';            // Vermelho
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo $page_title; ?></title>

    <!-- Link para o Font Awesome para usar ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <!-- Link para arquivo CSS personalizado -->
    <link rel="stylesheet" href="CSS/perfil.css" />

    <style>
        /* Estilos inline para aplicar a cor dinâmica baseada no tipo do usuário */
        .profile-icon {
            color: <?php echo $cor; ?>;
        }
        
        .profile-type {
            background-color: <?php echo $cor; ?>;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        /* Seção com detalhes pessoais */
        .profile-details {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        /* Itens de detalhe: flexbox para alinhar label e valor */
        .detail-item {
            display: flex;
            margin-bottom: 10px;
        }
        
        /* Estilo para o rótulo da informação */
        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        
        /* Estilo para o valor da informação */
        .detail-value {
            flex: 1;
        }
        
        /* Botão para editar perfil, usa a cor do tipo para manter a identidade visual */
        .edit-profile-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: <?php echo $cor; ?>;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        /* Hover do botão de editar perfil */
        .edit-profile-btn:hover {
            /* darken() não funciona no CSS puro, seria melhor usar rgba ou outra cor */
            filter: brightness(85%);
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <!-- Ícone do perfil com cor dinâmica -->
        <div class="profile-icon">
            <i class="fas <?php echo $icone; ?>"></i>
        </div>
        
        <!-- Nome do usuário -->
        <h1 class="profile-name"><?php echo htmlspecialchars($usuario['nome']); ?></h1>
        
        <!-- Email do usuário -->
        <div class="profile-email"><?php echo htmlspecialchars($usuario['email']); ?></div>
        
        <!-- Tipo do usuário (estilizado com cor e fundo) -->
        <div class="profile-type"><?php echo ucfirst($usuario['tipo']); ?></div>
        
        <!-- Informações básicas -->
        <div class="profile-info">
            <div class="info-item">
                <span class="info-label">ID do Usuário</span>
                <span class="info-value"><?php echo $usuario_id; ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Tipo de Conta</span>
                <span class="info-value"><?php echo ucfirst($usuario['tipo']); ?></span>
            </div>
        </div>
        
        <!-- Seção com informações pessoais adicionais -->
        <div class="profile-details">
            <h3>Informações Pessoais</h3>
            
            <div class="detail-item">
                <span class="detail-label">Data de Nascimento:</span>
                <span class="detail-value"><?php echo $data_nascimento_formatada; ?></span>
            </div>
            
            <div class="detail-item">
                <span class="detail-label">Telefone:</span>
                <span class="detail-value"><?php echo $telefone_formatado; ?></span>
            </div>


            
            <!-- Exibe curso somente se o usuário estiver matriculado em algum -->
            <?php if ($usuario['curso_id']): ?>
            <div class="detail-item">
                <span class="detail-label">Curso:</span>
                <span class="detail-value"><?php echo htmlspecialchars($usuario['nome_curso']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Botão para redirecionar para edição do perfil -->
        <a href="editar_perfil.php" class="edit-profile-btn">
            <i class="fas fa-edit"></i> Editar Perfil
        </a>
    </div>

    <!-- Script para acessibilidade ou outras funcionalidades -->
    <script src="JS/Vlibras.js"></script>
</body>
</html>
