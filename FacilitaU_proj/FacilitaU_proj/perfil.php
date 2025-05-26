<?php
session_start();

$page_title = "Meu Perfil";
include 'config.php';
include 'header.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT u.*, c.nome as nome_curso FROM Usuarios u 
        LEFT JOIN Cursos c ON u.curso_id = c.curso_id 
        WHERE u.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Usuário não encontrado.");
}

$usuario = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Formatando a data de nascimento para exibição
$data_nascimento_formatada = $usuario['data_nascimento'] ? date('d/m/Y', strtotime($usuario['data_nascimento'])) : 'Não informada';

// Formatando o telefone para exibição
$telefone_formatado = $usuario['telefone'] ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $usuario['telefone']) : 'Não informado';

switch ($usuario['tipo']) {
    case 'coordenador':
        $icone = 'fa-user-shield';
        $cor = '#4a90e2'; 
        break;
    case 'professor':
        $icone = 'fa-user-tie';
        $cor = '#2ecc71'; 
        break;
    default: 
        $icone = 'fa-user-graduate';
        $cor = '#e74c3c'; 
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/perfil.css">
    <style>
        /* estilo que precisa ser mantido no PHP*/ 
        .profile-icon {
            color: <?php echo $cor; ?>;
        }
        
        .profile-type {
            background-color: <?php echo $cor; ?>;
        }
        
        /* Novos estilos para informações adicionais */
        .profile-details {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .detail-item {
            display: flex;
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
        }
        
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
    </style>
</head>
<body>
    <div class="profile-card">
        <div class="profile-icon">
            <i class="fas <?php echo $icone; ?>"></i>
        </div>
        
        <h1 class="profile-name"><?php echo htmlspecialchars($usuario['nome']); ?></h1>
        <div class="profile-email"><?php echo htmlspecialchars($usuario['email']); ?></div>
        <div class="profile-type"><?php echo ucfirst($usuario['tipo']); ?></div>
        
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
        
        <!-- Novas informações adicionadas -->
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
            
            <?php if($usuario['curso_id']): ?>
            <div class="detail-item">
                <span class="detail-label">Curso:</span>
                <span class="detail-value"><?php echo htmlspecialchars($usuario['nome_curso']); ?></span>
            </div>
            <?php endif; ?>
            
        </div>
        
        <a href="editar_perfil.php" class="edit-profile-btn">
            <i class="fas fa-edit"></i> Editar Perfil
        </a>
    </div>
    <script src="JS/Vlibras.js"></script>
</body>
</html>

