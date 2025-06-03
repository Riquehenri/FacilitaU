<?php
// ========================================
// SISTEMA DE PERFIL DE USUÁRIO - FACILITA U
// ========================================
// Este arquivo é responsável por:
// 1. Verificar se o usuário está logado
// 2. Buscar dados do usuário no banco de dados
// 3. Formatar e exibir as informações de forma organizada
// 4. Aplicar estilos visuais baseados no tipo de usuário
// 5. Fornecer link para edição do perfil

// ========================================
// INICIALIZAÇÃO DA SESSÃO
// ========================================
// session_start() = inicia ou retoma uma sessão PHP
// Sessão = forma de manter dados do usuário entre diferentes páginas
// Sem isso, não conseguimos saber quem está logado
session_start();

// Define o título que aparece na aba do navegador
$page_title = "Meu Perfil";

// ========================================
// INCLUSÃO DE ARQUIVOS NECESSÁRIOS
// ========================================
include 'config.php';  // Arquivo com configurações do banco de dados (host, usuário, senha, etc.)
include 'header.php';  // Cabeçalho padrão do site (menu, CSS global, etc.)

// ========================================
// VERIFICAÇÃO DE AUTENTICAÇÃO
// ========================================
// Verifica se existe o ID do usuário na sessão
// $_SESSION = array global que mantém dados entre páginas
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, redireciona para página de login
    header("Location: login_usuario.php");
    exit(); // Para a execução do script (importante após redirecionamento)
}

// Obtém o ID do usuário logado da sessão
$usuario_id = $_SESSION['usuario_id'];

// ========================================
// CONSULTA AO BANCO DE DADOS
// ========================================
// SQL com LEFT JOIN para buscar dados do usuário E do curso
// LEFT JOIN = traz dados da tabela principal (Usuarios) mesmo se não houver correspondência na segunda tabela (Cursos)
// Isso é importante porque nem todo usuário pode ter um curso associado

$sql = "SELECT u.*, c.nome as nome_curso FROM Usuarios u 
        LEFT JOIN Cursos c ON u.curso_id = c.curso_id 
        WHERE u.usuario_id = ?";

// ========================================
// EXPLICAÇÃO DO SQL:
// ========================================
/*
SELECT u.*, c.nome as nome_curso
- u.* = todos os campos da tabela Usuarios (u é um alias/apelido)
- c.nome as nome_curso = campo 'nome' da tabela Cursos, renomeado para 'nome_curso'

FROM Usuarios u
- Tabela principal: Usuarios com alias 'u'

LEFT JOIN Cursos c ON u.curso_id = c.curso_id
- LEFT JOIN = junção à esquerda
- Traz TODOS os registros de Usuarios, mesmo se não tiver curso
- c = alias para tabela Cursos
- ON = condição de junção (qual campo conecta as tabelas)

WHERE u.usuario_id = ?
- Filtro para buscar apenas o usuário específico
- ? = placeholder para prepared statement (segurança)
*/

// ========================================
// PREPARED STATEMENT (CONSULTA SEGURA)
// ========================================
// Prepared Statement = forma segura de executar SQL
// Previne SQL Injection (ataque onde hacker injeta código malicioso)
$stmt = $conn->prepare($sql);

// bind_param = associa valores aos placeholders (?)
// "i" = integer (tipo do parâmetro)
// $usuario_id = valor que substitui o ?
$stmt->bind_param("i", $usuario_id);

// Executa a consulta
$stmt->execute();

// Obtém o resultado da consulta
$result = $stmt->get_result();

// ========================================
// VERIFICAÇÃO DE RESULTADO
// ========================================
// Verifica se encontrou o usuário
if ($result->num_rows == 0) {
    // die() = para a execução e exibe mensagem de erro
    // Usado quando há erro crítico que impede o funcionamento
    die("Usuário não encontrado.");
}

// ========================================
// OBTENÇÃO DOS DADOS
// ========================================
// fetch_assoc() = retorna próxima linha como array associativo
// Array associativo = array onde as chaves são os nomes dos campos
// Exemplo: ['nome' => 'João Silva', 'email' => 'joao@email.com']
$usuario = $result->fetch_assoc();

// ========================================
// LIMPEZA DE RECURSOS
// ========================================
// Boa prática: sempre fechar statements e conexões
$stmt->close(); // Libera recursos do prepared statement
$conn->close(); // Fecha conexão com banco de dados

// ========================================
// FORMATAÇÃO DE DADOS PARA EXIBIÇÃO
// ========================================

// FORMATAÇÃO DE DATA DE NASCIMENTO
// Operador ternário: condição ? valor_se_verdadeiro : valor_se_falso
// strtotime() = converte string de data em timestamp
// date() = formata timestamp para string legível
$data_nascimento_formatada = $usuario['data_nascimento'] ? 
    date('d/m/Y', strtotime($usuario['data_nascimento'])) : 
    'Não informada';

// ========================================
// EXPLICAÇÃO DA FORMATAÇÃO DE DATA:
// ========================================
/*
$usuario['data_nascimento'] = verifica se existe data (não é NULL ou vazio)

Se EXISTE:
- strtotime($usuario['data_nascimento']) = converte "1990-05-15" em timestamp
- date('d/m/Y', timestamp) = converte timestamp em "15/05/1990"

Se NÃO EXISTE:
- Retorna string "Não informada"
*/

// FORMATAÇÃO DE TELEFONE
// preg_replace() = substitui padrões usando expressões regulares
// Padrão: (\d{2})(\d{5})(\d{4}) = captura 2 dígitos, 5 dígitos, 4 dígitos
// Substituição: ($1) $2-$3 = formato (XX) XXXXX-XXXX
$telefone_formatado = $usuario['telefone'] ? 
    preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $usuario['telefone']) : 
    'Não informado';

// ========================================
// EXPLICAÇÃO DA FORMATAÇÃO DE TELEFONE:
// ========================================
/*
Entrada: "11987654321"
Padrão: (\d{2})(\d{5})(\d{4})
- (\d{2}) = captura "11" como grupo 1
- (\d{5}) = captura "98765" como grupo 2  
- (\d{4}) = captura "4321" como grupo 3

Substituição: ($1) $2-$3
- $1 = primeiro grupo capturado = "11"
- $2 = segundo grupo capturado = "98765"
- $3 = terceiro grupo capturado = "4321"

Resultado: "(11) 98765-4321"
*/

// ========================================
// CONFIGURAÇÃO VISUAL BASEADA NO TIPO DE USUÁRIO
// ========================================
// Switch = estrutura condicional para múltiplas opções
// Define ícone e cor específicos para cada tipo de usuário
switch ($usuario['tipo']) {
    case 'coordenador':
        $icone = 'fa-user-shield'; // Ícone FontAwesome (escudo = autoridade)
        $cor = '#4a90e2';          // Azul (cor associada à liderança)
        break;
    case 'professor':
        $icone = 'fa-user-tie';    // Ícone FontAwesome (gravata = profissional)
        $cor = '#2ecc71';          // Verde (cor associada ao ensino)
        break;
    default:
        // Caso padrão (estudante ou qualquer outro tipo)
        $icone = 'fa-user-graduate'; // Ícone FontAwesome (capelo = estudante)
        $cor = '#e74c3c';            // Vermelho (cor associada ao aprendizado)
}

// ========================================
// PONTO DE EXPANSÃO: MAIS TIPOS DE USUÁRIO
// ========================================
/*
Você pode adicionar mais tipos:

case 'administrador':
    $icone = 'fa-user-cog';
    $cor = '#9b59b6';  // Roxo
    break;
case 'secretario':
    $icone = 'fa-user-edit';
    $cor = '#f39c12';  // Laranja
    break;
case 'monitor':
    $icone = 'fa-user-friends';
    $cor = '#1abc9c';  // Turquesa
    break;
*/
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- ========================================
         CONFIGURAÇÕES DO CABEÇALHO HTML
         ======================================== -->
    <meta charset="UTF-8" /> <!-- Codificação para suporte a acentos -->
    <meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- Responsividade mobile -->
    <title><?php echo $page_title; ?></title> <!-- Título dinâmico da página -->

    <!-- ========================================
         INCLUSÃO DE RECURSOS EXTERNOS
         ======================================== -->
    <!-- FontAwesome = biblioteca de ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    
    <!-- CSS personalizado do perfil -->
    <link rel="stylesheet" href="CSS/perfil.css" />

    <style>
        /* ========================================
           ESTILOS DINÂMICOS BASEADOS NO TIPO DE USUÁRIO
           ======================================== */
        /* CSS inline é usado aqui porque a cor vem do PHP */
        /* Não é possível usar CSS externo para valores dinâmicos */
        
        .profile-icon {
            color: <?php echo $cor; ?>; /* Cor do ícone baseada no tipo */
        }
        
        .profile-type {
            background-color: <?php echo $cor; ?>; /* Fundo da badge do tipo */
            color: white;                           /* Texto branco para contraste */
            padding: 5px 10px;                     /* Espaçamento interno */
            border-radius: 15px;                   /* Bordas arredondadas */
            display: inline-block;                 /* Comportamento de bloco inline */
            margin-bottom: 15px;                   /* Margem inferior */
        }
        
        /* ========================================
           ESTILOS PARA SEÇÃO DE DETALHES
           ======================================== */
        .profile-details {
            margin-top: 20px;           /* Margem superior */
            border-top: 1px solid #eee; /* Linha separadora sutil */
            padding-top: 20px;          /* Espaçamento após a linha */
        }
        
        /* Flexbox para alinhar label e valor lado a lado */
        .detail-item {
            display: flex;      /* Layout flexível */
            margin-bottom: 10px; /* Espaço entre itens */
        }
        
        /* Estilo para rótulos (labels) */
        .detail-label {
            font-weight: bold; /* Texto em negrito */
            width: 150px;      /* Largura fixa para alinhamento */
            color: #555;       /* Cor cinza escuro */
        }
        
        /* Estilo para valores */
        .detail-value {
            flex: 1; /* Ocupa espaço restante */
        }
        
        /* ========================================
           BOTÃO DE EDITAR PERFIL
           ======================================== */
        .edit-profile-btn {
            display: inline-block;                  /* Comportamento de bloco inline */
            margin-top: 20px;                      /* Margem superior */
            padding: 10px 20px;                    /* Espaçamento interno */
            background-color: <?php echo $cor; ?>; /* Cor de fundo dinâmica */
            color: white;                          /* Texto branco */
            text-decoration: none;                 /* Remove sublinhado do link */
            border-radius: 5px;                    /* Bordas arredondadas */
            transition: background-color 0.3s;     /* Transição suave para hover */
        }

        /* Efeito hover do botão */
        .edit-profile-btn:hover {
            /* filter: brightness() = ajusta brilho da cor */
            /* 85% = escurece a cor em 15% */
            filter: brightness(85%);
        }
        
        /* ========================================
           PONTO DE EXPANSÃO: MAIS ESTILOS
           ======================================== */
        /*
        Você pode adicionar:
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid <?php echo $cor; ?>;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        */
    </style>
</head>
<body>
    <!-- ========================================
         CARTÃO PRINCIPAL DO PERFIL
         ======================================== -->
    <div class="profile-card">
        
        <!-- ========================================
             ÍCONE DO PERFIL
             ======================================== -->
        <!-- Ícone com cor dinâmica baseada no tipo de usuário -->
        <div class="profile-icon">
            <!-- Classes FontAwesome: fas = solid, fa-* = nome do ícone -->
            <i class="fas <?php echo $icone; ?>"></i>
        </div>
        
        <!-- ========================================
             INFORMAÇÕES PRINCIPAIS
             ======================================== -->
        <!-- Nome do usuário -->
        <!-- htmlspecialchars() = previne XSS (Cross-Site Scripting) -->
        <!-- Converte caracteres especiais em entidades HTML -->
        <h1 class="profile-name"><?php echo htmlspecialchars($usuario['nome']); ?></h1>
        
        <!-- Email do usuário -->
        <div class="profile-email"><?php echo htmlspecialchars($usuario['email']); ?></div>
        
        <!-- Tipo do usuário com estilo dinâmico -->
        <!-- ucfirst() = primeira letra maiúscula -->
        <div class="profile-type"><?php echo ucfirst($usuario['tipo']); ?></div>
        
        <!-- ========================================
             INFORMAÇÕES BÁSICAS
             ======================================== -->
        <div class="profile-info">
            <!-- ID do usuário (útil para suporte técnico) -->
            <div class="info-item">
                <span class="info-label">ID do Usuário</span>
                <span class="info-value"><?php echo $usuario_id; ?></span>
            </div>
            
            <!-- Tipo de conta (repetido para ênfase) -->
            <div class="info-item">
                <span class="info-label">Tipo de Conta</span>
                <span class="info-value"><?php echo ucfirst($usuario['tipo']); ?></span>
            </div>
        </div>
        
        <!-- ========================================
             SEÇÃO DE DETALHES PESSOAIS
             ======================================== -->
        <div class="profile-details">
            <h3>Informações Pessoais</h3>
            
            <!-- Data de nascimento formatada -->
            <div class="detail-item">
                <span class="detail-label">Data de Nascimento:</span>
                <span class="detail-value"><?php echo $data_nascimento_formatada; ?></span>
            </div>
            
            <!-- Telefone formatado -->
            <div class="detail-item">
                <span class="detail-label">Telefone:</span>
                <span class="detail-value"><?php echo $telefone_formatado; ?></span>
            </div>

            <!-- ========================================
                 EXIBIÇÃO CONDICIONAL DO CURSO
                 ======================================== -->
            <!-- Só mostra se o usuário tem curso associado -->
            <?php if ($usuario['curso_id']): ?>
            <div class="detail-item">
                <span class="detail-label">Curso:</span>
                <!-- nome_curso vem do LEFT JOIN com tabela Cursos -->
                <span class="detail-value"><?php echo htmlspecialchars($usuario['nome_curso']); ?></span>
            </div>
            <?php endif; ?>
            
            <!-- ========================================
                 PONTO DE EXPANSÃO: MAIS CAMPOS
                 ======================================== -->
            <!--
            Você pode adicionar mais campos aqui:
            
            <?php if ($usuario['cpf']): ?>
            <div class="detail-item">
                <span class="detail-label">CPF:</span>
                <span class="detail-value"><?php echo formatarCPF($usuario['cpf']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($usuario['endereco']): ?>
            <div class="detail-item">
                <span class="detail-label">Endereço:</span>
                <span class="detail-value"><?php echo htmlspecialchars($usuario['endereco']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($usuario['data_cadastro']): ?>
            <div class="detail-item">
                <span class="detail-label">Membro desde:</span>
                <span class="detail-value"><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($usuario['ultimo_acesso']): ?>
            <div class="detail-item">
                <span class="detail-label">Último acesso:</span>
                <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])); ?></span>
            </div>
            <?php endif; ?>
            -->
        </div>
        
        <!-- ========================================
             BOTÃO DE EDIÇÃO
             ======================================== -->
        <!-- Link estilizado como botão para editar perfil -->
        <a href="editar_perfil.php" class="edit-profile-btn">
            <i class="fas fa-edit"></i> Editar Perfil
        </a>
        
        <!-- ========================================
             PONTO DE EXPANSÃO: MAIS AÇÕES
             ======================================== -->
        <!--
        Você pode adicionar mais botões:
        
        <div class="profile-actions">
            <a href="alterar_senha.php" class="action-btn">
                <i class="fas fa-key"></i> Alterar Senha
            </a>
            
            <a href="configuracoes.php" class="action-btn">
                <i class="fas fa-cog"></i> Configurações
            </a>
            
            <a href="historico.php" class="action-btn">
                <i class="fas fa-history"></i> Histórico
            </a>
            
            <?php if ($usuario['tipo'] === 'estudante'): ?>
            <a href="meus_planejamentos.php" class="action-btn">
                <i class="fas fa-calendar"></i> Meus Planejamentos
            </a>
            <?php endif; ?>
        </div>
        -->
    </div>

    <!-- ========================================
         SCRIPTS JAVASCRIPT
         ======================================== -->
    <!-- Script de acessibilidade (tradutor de libras) -->
    <script src="JS/Vlibras.js"></script>
    
    <!-- ========================================
         PONTO DE EXPANSÃO: MAIS FUNCIONALIDADES
         ======================================== -->
    <!--
    Você pode adicionar:
    
    <script>
    // Função para copiar ID do usuário
    function copiarID() {
        navigator.clipboard.writeText('<?php echo $usuario_id; ?>');
        alert('ID copiado para a área de transferência!');
    }
    
    // Função para compartilhar perfil
    function compartilharPerfil() {
        if (navigator.share) {
            navigator.share({
                title: 'Perfil de <?php echo $usuario['nome']; ?>',
                text: 'Confira o perfil no FacilitaU',
                url: window.location.href
            });
        }
    }
    
    // Função para baixar dados do perfil
    function baixarDados() {
        const dados = {
            nome: '<?php echo $usuario['nome']; ?>',
            email: '<?php echo $usuario['email']; ?>',
            tipo: '<?php echo $usuario['tipo']; ?>',
            telefone: '<?php echo $telefone_formatado; ?>',
            nascimento: '<?php echo $data_nascimento_formatada; ?>'
        };
        
        const blob = new Blob([JSON.stringify(dados, null, 2)], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'meu_perfil.json';
        a.click();
    }
    </script>
    -->
</body>
</html>

<?php
// ========================================
// FUNÇÕES AUXILIARES PARA EXPANSÃO
// ========================================

/*
// Função para formatar CPF
function formatarCPF($cpf) {
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
}

// Função para formatar CEP
function formatarCEP($cep) {
    return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
}

// Função para calcular idade
function calcularIdade($data_nascimento) {
    $nascimento = new DateTime($data_nascimento);
    $hoje = new DateTime();
    return $hoje->diff($nascimento)->y;
}

// Função para obter estatísticas do usuário
function obterEstatisticasUsuario($conn, $usuario_id, $tipo) {
    $stats = [];
    
    if ($tipo === 'estudante') {
        // Contar planejamentos
        $sql = "SELECT COUNT(*) as total FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stats['planejamentos'] = $stmt->get_result()->fetch_assoc()['total'];
        
        // Contar horas de estudo
        $sql = "SELECT SUM(TIMESTAMPDIFF(MINUTE, horario_inicio, horario_fim)) as minutos 
                FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $minutos = $stmt->get_result()->fetch_assoc()['minutos'];
        $stats['horas_estudo'] = round($minutos / 60, 1);
        
    } elseif ($tipo === 'professor') {
        // Contar avisos criados
        $sql = "SELECT COUNT(*) as total FROM Avisos WHERE usuario_id = ? AND ativo = TRUE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stats['avisos_criados'] = $stmt->get_result()->fetch_assoc()['total'];
    }
    
    return $stats;
}

// Exemplo de uso das estatísticas:
// $stats = obterEstatisticasUsuario($conn, $usuario_id, $usuario['tipo']);
// 
// No HTML:
// <div class="profile-stats">
//     <?php if ($usuario['tipo'] === 'estudante'): ?>
//         <div class="stat-item">
//             <h4><?php echo $stats['planejamentos']; ?></h4>
//             <p>Planejamentos</p>
//         </div>
//         <div class="stat-item">
//             <h4><?php echo $stats['horas_estudo']; ?>h</h4>
//             <p>Horas de Estudo</p>
//         </div>
//     <?php endif; ?>
// </div>
*/

// ========================================
// PONTOS DE EXPANSÃO PARA SUA PROVA:
// ========================================

/*
1. ADICIONAR FOTO DE PERFIL:
   - Campo 'foto' na tabela Usuarios
   - Upload de imagem
   - Redimensionamento automático
   - Foto padrão baseada no tipo

2. SISTEMA DE BADGES/CONQUISTAS:
   - Tabela de conquistas
   - Sistema de pontuação
   - Badges visuais no perfil

3. HISTÓRICO DE ATIVIDADES:
   - Log de ações do usuário
   - Timeline de atividades
   - Estatísticas de uso

4. CONFIGURAÇÕES DE PRIVACIDADE:
   - Controle de visibilidade
   - Preferências de notificação
   - Configurações de conta

5. INTEGRAÇÃO SOCIAL:
   - Compartilhamento de perfil
   - Conexões com outros usuários
   - Feed de atividades

EXEMPLO PRÁTICO - ADICIONAR CAMPO BIOGRAFIA:

1. No banco: ALTER TABLE Usuarios ADD COLUMN biografia TEXT;

2. No perfil:
<div class="detail-item">
    <span class="detail-label">Biografia:</span>
    <span class="detail-value">
        <?php echo $usuario['biografia'] ? htmlspecialchars($usuario['biografia']) : 'Não informada'; ?>
    </span>
</div>

3. Na edição: <textarea name="biografia"><?php echo htmlspecialchars($usuario['biografia']); ?></textarea>

4. No processamento: $biografia = $_POST['biografia'];
*/
?>
