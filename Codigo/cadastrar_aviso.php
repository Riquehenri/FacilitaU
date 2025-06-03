<?php
// ========================================
// SISTEMA DE CADASTRO DE AVISOS - FACILITA U
// ========================================
// Este arquivo é responsável por um sistema de comunicação institucional
// Permite que professores e coordenadores criem avisos para estudantes
// 
// FUNCIONALIDADES PRINCIPAIS:
// 1. Criar avisos (gerais ou oportunidades de emprego)
// 2. Editar avisos existentes
// 3. Excluir avisos (soft delete)
// 4. Listar todos os avisos do usuário
// 5. Sistema de notificações automáticas
// 6. Integração com calendário (recorrência)

// ========================================
// CONFIGURAÇÕES INICIAIS DO SISTEMA
// ========================================
// session_start() = inicia sistema de sessões para manter usuário logado
session_start();

// ========================================
// CONFIGURAÇÃO DE FUSO HORÁRIO
// ========================================
// Define fuso horário do Brasil para datas/horários corretos
// Importante para sistemas que lidam com datas e notificações
date_default_timezone_set('America/Sao_Paulo');

// Título que aparece na aba do navegador
$page_title = "Cadastrar Aviso";

// ========================================
// INCLUSÃO DE ARQUIVOS NECESSÁRIOS
// ========================================
include 'config.php';  // Configurações do banco de dados
include 'header.php';  // Cabeçalho padrão do site (menu, CSS, etc.)

// ========================================
// CONTROLE DE ACESSO BASEADO EM PERMISSÕES
// ========================================
// Verifica se o usuário está logado E tem permissão para criar avisos
// Apenas coordenadores e professores podem acessar esta página

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['tipo'], ['coordenador', 'professor'])) {
    // Se não tiver permissão, redireciona para página inicial
    header("Location: index.php");
    exit(); // Para execução do script
}

// Obtém ID do usuário logado (será usado em todas as operações)
$usuario_id = $_SESSION['usuario_id'];

// ========================================
// OPERAÇÃO 1: EXCLUSÃO DE AVISO (DELETE)
// ========================================
// Esta seção executa quando o usuário clica no link "Excluir"
// URL: cadastrar_aviso.php?excluir=123

if (isset($_GET['excluir'])) {
    // $_GET = array com dados da URL
    $aviso_id = $_GET['excluir']; // ID do aviso a ser excluído
    
    // ========================================
    // SOFT DELETE (EXCLUSÃO SUAVE)
    // ========================================
    // Marca como inativo ao invés de deletar fisicamente
    // Vantagens:
    // - Permite recuperação posterior
    // - Mantém histórico para auditoria
    // - Compatível com sistema de calendário
    // - Preserva integridade referencial
    
    $sql = "UPDATE Avisos SET ativo = FALSE WHERE aviso_id = ? AND usuario_id = ?";
    
    // ========================================
    // PREPARED STATEMENT (SEGURANÇA)
    // ========================================
    // Forma segura de executar SQL, previne SQL Injection
    $stmt = $conn->prepare($sql);
    
    // bind_param = associa valores aos placeholders (?)
    // "ii" = dois parâmetros do tipo integer
    // Garante que apenas o dono do aviso pode excluí-lo
    $stmt->bind_param("ii", $aviso_id, $usuario_id);
    
    // Executa e verifica resultado
    if ($stmt->execute()) {
        // ========================================
        // SISTEMA DE FEEDBACK PARA USUÁRIO
        // ========================================
        // Armazena mensagem na sessão para exibir após redirecionamento
        $_SESSION['mensagem'] = "Aviso excluído com sucesso!";
        $_SESSION['tipo_mensagem'] = "success"; // CSS class para estilização
    } else {
        // Em caso de erro, armazena mensagem de erro
        $_SESSION['mensagem'] = "Erro ao excluir: " . $conn->error;
        $_SESSION['tipo_mensagem'] = "error";
    }
    
    $stmt->close(); // Libera recursos
    
    // ========================================
    // PADRÃO PRG (POST-REDIRECT-GET)
    // ========================================
    // Redireciona para evitar reenvio acidental da operação
    header("Location: cadastrar_aviso.php");
    exit();
}

// ========================================
// OPERAÇÃO 2: CADASTRO E EDIÇÃO DE AVISOS (CREATE/UPDATE)
// ========================================
// Esta seção executa quando o formulário é enviado

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ========================================
    // COLETA DE DADOS DO FORMULÁRIO
    // ========================================
    // $_POST = array com todos os dados enviados pelo formulário
    
    $tipo_aviso = $_POST['tipo_aviso'];           // "aviso" ou "oportunidade"
    $titulo = $_POST['titulo'];                   // Título do aviso
    $descricao = $_POST['descricao'];             // Descrição detalhada
    
    // ========================================
    // NOVOS CAMPOS PARA INTEGRAÇÃO COM CALENDÁRIO
    // ========================================
    // Operador ?? = null coalescing (valor padrão se não existir)
    $data_inicial = $_POST['data_inicial'] ?? date('Y-m-d');    // Data do aviso
    $tipo_recorrencia = $_POST['tipo_recorrencia'] ?? 'nao';    // Como se repete
    
    // ========================================
    // DECISÃO: EDITAR OU CRIAR NOVO?
    // ========================================
    // Se existe 'editar_id', é uma edição; senão, é criação
    
    if (isset($_POST['editar_id'])) {
        // ========================================
        // OPERAÇÃO DE ATUALIZAÇÃO (UPDATE)
        // ========================================
        $aviso_id = $_POST['editar_id']; // ID do aviso sendo editado
        
        // SQL para atualizar registro existente
        $sql = "UPDATE Avisos SET tipo_aviso = ?, titulo = ?, descricao = ?, data_inicial = ?, tipo_recorrencia = ? 
                WHERE aviso_id = ? AND usuario_id = ?";
        
        $stmt = $conn->prepare($sql);
        
        // bind_param com 7 parâmetros:
        // "sssssii" = 5 strings + 2 integers
        $stmt->bind_param("sssssii", $tipo_aviso, $titulo, $descricao, $data_inicial, $tipo_recorrencia, $aviso_id, $usuario_id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Aviso atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();

    } else {
        // ========================================
        // OPERAÇÃO DE INSERÇÃO (CREATE)
        // ========================================
        
        // Data de publicação = hoje
        $data_publicacao = date('Y-m-d');
        
        // SQL para inserir novo aviso
        // Inclui campos necessários para integração com calendário
        $sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao, data_inicial, tipo_recorrencia, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)";
        
        $stmt = $conn->prepare($sql);
        
        // bind_param com 7 parâmetros (ativo=TRUE é fixo no SQL)
        // "issssss" = 1 integer + 6 strings
        $stmt->bind_param("issssss", $usuario_id, $tipo_aviso, $titulo, $descricao, $data_publicacao, $data_inicial, $tipo_recorrencia);

        if ($stmt->execute()) {
            // ========================================
            // SISTEMA DE NOTIFICAÇÕES AUTOMÁTICAS
            // ========================================
            // Após criar aviso, notifica automaticamente todos os estudantes
            
            $aviso_id = $conn->insert_id; // ID do aviso recém-criado
            
            // SQL para criar notificações para todos os estudantes
            // SELECT com INSERT = cria uma notificação para cada estudante
            $sql_notif = "INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id)
                          SELECT u.usuario_id, 'aviso', CONCAT('Novo aviso: ', ?), ?, ?
                          FROM Usuarios u WHERE u.tipo = 'estudante'";
            
            $stmt_notif = $conn->prepare($sql_notif);
            if ($stmt_notif) {
                // bind_param para notificação:
                // ? = título do aviso (para mensagem)
                // ? = data da notificação
                // ? = ID do aviso (referência)
                $stmt_notif->bind_param("ssi", $titulo, $data_inicial, $aviso_id);
                $stmt_notif->execute();
                $stmt_notif->close();
            }

            // ========================================
            // MENSAGEM DINÂMICA BASEADA NA RECORRÊNCIA
            // ========================================
            // Match expression (PHP 8+) para criar mensagem personalizada
            $mensagem_recorrencia = match($tipo_recorrencia) {
                'nao' => '',
                'semanal' => ' (repetição semanal)',
                'mensal' => ' (repetição mensal)',
                'anual' => ' (repetição anual)',
                default => ''
            };

            $_SESSION['mensagem'] = "Aviso cadastrado com sucesso! Notificações enviadas aos estudantes." . $mensagem_recorrencia;
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    }

    // Redireciona após processamento (padrão PRG)
    header("Location: cadastrar_aviso.php");
    exit();
}

// ========================================
// OPERAÇÃO 3: BUSCAR DADOS PARA EDIÇÃO
// ========================================
// Esta seção executa quando o usuário clica em "Editar"
// URL: cadastrar_aviso.php?editar=123

$editar_id = null;      // ID do aviso sendo editado
$editar_dados = null;   // Dados do aviso para preencher formulário

if (isset($_GET['editar'])) {
    $editar_id = $_GET['editar'];
    
    // Busca dados do aviso específico
    // Verifica se o aviso existe, está ativo e pertence ao usuário
    $sql = "SELECT * FROM Avisos WHERE aviso_id = ? AND usuario_id = ? AND ativo = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $editar_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // fetch_assoc() = retorna linha como array associativo
        $editar_dados = $result->fetch_assoc();
    } else {
        // Se não encontrou ou não tem permissão
        $_SESSION['mensagem'] = "Aviso não encontrado ou você não tem permissão para editá-lo.";
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: cadastrar_aviso.php");
        exit();
    }
    $stmt->close();
}

// ========================================
// OPERAÇÃO 4: LISTAR AVISOS DO USUÁRIO (READ)
// ========================================
// Busca todos os avisos ativos do usuário para exibir na tabela

$sql = "SELECT * FROM Avisos WHERE usuario_id = ? AND ativo = TRUE ORDER BY data_publicacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result(); // Resultado usado na tabela HTML
?>

<!-- ========================================
     INCLUSÃO DE ARQUIVOS CSS
     ======================================== -->
<!-- CSS específico para a página de avisos -->
<link rel="stylesheet" href="CSS/cadastrar_avisos.css">
<link rel="stylesheet" href="CSS/editarCadastroPC.css">

<!-- ========================================
     INTERFACE HTML: CABEÇALHO
     ======================================== -->
<h2>Cadastrar Aviso</h2>

<!-- ========================================
     EXIBIÇÃO DE MENSAGENS DE FEEDBACK
     ======================================== -->
<!-- Verifica se há mensagem armazenada na sessão -->
<?php
if (isset($_SESSION['mensagem'])) {
    // Exibe mensagem com classe CSS baseada no tipo
    echo "<div class='" . $_SESSION['tipo_mensagem'] . "'>" . $_SESSION['mensagem'] . "</div>";
    
    // Remove mensagem da sessão (exibe apenas uma vez)
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>

<!-- ========================================
     FORMULÁRIO PRINCIPAL
     ======================================== -->
<!-- Título dinâmico baseado na operação -->
<h3><?php echo isset($editar_id) ? "Editar Aviso" : "Cadastrar Novo Aviso"; ?></h3>

<form method="POST" action="cadastrar_aviso.php">
    
    <!-- ========================================
         CAMPO OCULTO PARA EDIÇÃO
         ======================================== -->
    <!-- Se está editando, inclui ID oculto para identificar o registro -->
    <?php if (isset($editar_id)): ?>
        <input type="hidden" name="editar_id" value="<?php echo $editar_id; ?>">
    <?php endif; ?>
    
    <!-- ========================================
         CAMPO: TIPO DE AVISO
         ======================================== -->
    <label for="tipo_aviso">Tipo de Aviso:</label>
    <select name="tipo_aviso" id="tipo_aviso" required>
        <!-- Verifica qual opção deve estar selecionada (modo edição) -->
        <option value="aviso" <?php echo (isset($editar_dados) && $editar_dados['tipo_aviso'] == 'aviso') ? 'selected' : ''; ?>>Aviso Geral</option>
        <option value="oportunidade" <?php echo (isset($editar_dados) && $editar_dados['tipo_aviso'] == 'oportunidade') ? 'selected' : ''; ?>>Oportunidade de Emprego</option>
    </select>

    <!-- ========================================
         CAMPO: TÍTULO DO AVISO
         ======================================== -->
    <label for="titulo">Título:</label>
    <!-- htmlspecialchars() = previne XSS (Cross-Site Scripting) -->
    <input type="text" name="titulo" id="titulo" 
           value="<?php echo isset($editar_dados) ? htmlspecialchars($editar_dados['titulo']) : ''; ?>" required>

    <!-- ========================================
         CAMPO: DESCRIÇÃO DETALHADA
         ======================================== -->
    <label for="descricao">Descrição:</label>
    <!-- textarea para textos longos -->
    <textarea name="descricao" id="descricao" required><?php echo isset($editar_dados) ? htmlspecialchars($editar_dados['descricao']) : ''; ?></textarea>

    <!-- ========================================
         NOVOS CAMPOS PARA INTEGRAÇÃO COM CALENDÁRIO
         ======================================== -->
    
    <!-- CAMPO: DATA DO AVISO -->
    <label for="data_inicial">Data do Aviso:</label>
    <input type="date" name="data_inicial" id="data_inicial" 
           value="<?php echo isset($editar_dados) ? $editar_dados['data_inicial'] : date('Y-m-d'); ?>" required>

    <!-- CAMPO: TIPO DE RECORRÊNCIA -->
    <label for="tipo_recorrencia">Repetir Aviso:</label>
    <select name="tipo_recorrencia" id="tipo_recorrencia">
        <!-- Cada opção verifica se deve estar selecionada -->
        <option value="nao" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'nao') ? 'selected' : ''; ?>>Não repetir (apenas neste dia)</option>
        <option value="semanal" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'semanal') ? 'selected' : ''; ?>>Repetir semanalmente</option>
        <option value="mensal" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'mensal') ? 'selected' : ''; ?>>Repetir mensalmente</option>
        <option value="anual" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'anual') ? 'selected' : ''; ?>>Repetir anualmente</option>
    </select>

    <!-- ========================================
         ÁREA INFORMATIVA DINÂMICA
         ======================================== -->
    <!-- Explica ao usuário o que cada tipo de recorrência significa -->
    <div class="info-recorrencia" style="margin: 10px 0; padding: 10px; background: #f0f8ff; border-left: 4px solid #007bff; font-size: 14px;">
        <strong>ℹ️ Informação:</strong>
        <span id="info-texto-recorrencia">O aviso será criado apenas para o dia selecionado.</span>
    </div>

    <!-- ========================================
         BOTÕES DE AÇÃO
         ======================================== -->
    <div class="button-group">
        <!-- Texto do botão muda baseado na operação -->
        <button type="submit"><?php echo isset($editar_id) ? "Atualizar" : "Cadastrar Aviso"; ?></button>
        
        <!-- Botão cancelar só aparece no modo edição -->
        <?php if (isset($editar_id)): ?>
            <a href="cadastrar_aviso.php" class="cancel-button">Cancelar</a>
        <?php endif; ?>
    </div>
</form>

<!-- ========================================
     SEÇÃO DE LISTAGEM DOS AVISOS
     ======================================== -->
<h3>Seus Avisos Cadastrados</h3>

<?php
// ========================================
// EXIBIÇÃO DOS DADOS EM TABELA
// ========================================
// Verifica se há avisos cadastrados
if ($result->num_rows > 0) {
    echo "<div class='avisos-container'>";
    echo "<table>";
    
    // ========================================
    // CABEÇALHO DA TABELA
    // ========================================
    echo "<thead><tr><th>Tipo</th><th>Título</th><th>Descrição</th><th>Data do Aviso</th><th>Repetição</th><th>Data Cadastro</th><th>Ações</th></tr></thead>";
    echo "<tbody>";
    
    // ========================================
    // LOOP ATRAVÉS DOS RESULTADOS
    // ========================================
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        
        // ========================================
        // FORMATAÇÃO DE DADOS PARA EXIBIÇÃO
        // ========================================
        
        // Converte código do tipo para texto amigável
        echo "<td>" . ($row['tipo_aviso'] == 'aviso' ? 'Aviso Geral' : 'Oportunidade') . "</td>";
        
        // Título com proteção XSS
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        
        // ========================================
        // TRUNCAMENTO DE TEXTO LONGO
        // ========================================
        // Mostra apenas primeiros 100 caracteres da descrição
        // substr() = pega substring, strlen() = comprimento da string
        echo "<td>" . htmlspecialchars(substr($row['descricao'], 0, 100)) . (strlen($row['descricao']) > 100 ? '...' : '') . "</td>";
        
        // ========================================
        // FORMATAÇÃO DE DATAS
        // ========================================
        // Usa data_inicial se existir, senão usa data_publicacao
        // date() + strtotime() = converte para formato brasileiro
        echo "<td>" . date('d/m/Y', strtotime($row['data_inicial'] ?? $row['data_publicacao'])) . "</td>";
        
        // ========================================
        // CONVERSÃO DE CÓDIGO PARA TEXTO LEGÍVEL
        // ========================================
        // Match expression para converter tipo de recorrência
        $recorrencia_texto = match($row['tipo_recorrencia'] ?? 'nao') {
            'nao' => 'Não repete',
            'semanal' => 'Semanal',
            'mensal' => 'Mensal',
            'anual' => 'Anual',
            default => 'Não repete'
        };
        echo "<td>" . $recorrencia_texto . "</td>";
        
        // Data de cadastro formatada
        echo "<td>" . date('d/m/Y', strtotime($row['data_publicacao'])) . "</td>";
        
        // ========================================
        // AÇÕES (EDITAR E EXCLUIR)
        // ========================================
        echo "<td class='actions'>";
        
        // Link para editar (passa ID pela URL)
        echo "<a href='cadastrar_aviso.php?editar=" . $row['aviso_id'] . "' class='edit-btn'>Editar</a> ";
        
        // Link para excluir com confirmação JavaScript
        echo "<a href='cadastrar_aviso.php?excluir=" . $row['aviso_id'] . "' class='delete-btn' onclick='return confirm(\"Tem certeza que deseja excluir este aviso?\")'>Excluir</a>";
        
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    // Mensagem quando não há dados
    echo "<p>Nenhum aviso cadastrado.</p>";
}

// ========================================
// LIMPEZA DE RECURSOS
// ========================================
// Boa prática: sempre fechar statements e conexões
$stmt->close();
$conn->close();
?>

<script>
// ========================================
// JAVASCRIPT: INTERFACE DINÂMICA
// ========================================
// Atualiza texto informativo baseado na seleção de recorrência

document.addEventListener('DOMContentLoaded', function() {
    // Obtém elementos do DOM
    const selectRecorrencia = document.getElementById('tipo_recorrencia');
    const infoTexto = document.getElementById('info-texto-recorrencia');
    
    // ========================================
    // OBJETO COM TEXTOS EXPLICATIVOS
    // ========================================
    // Cada tipo de recorrência tem uma explicação clara
    const textosRecorrencia = {
        'nao': 'O aviso será criado apenas para o dia selecionado.',
        'semanal': 'O aviso aparecerá toda semana no mesmo dia da semana.',
        'mensal': 'O aviso aparecerá todo mês no mesmo dia do mês.',
        'anual': 'O aviso aparecerá todo ano na mesma data.'
    };
    
    // ========================================
    // EVENT LISTENER PARA MUDANÇA DE SELEÇÃO
    // ========================================
    // Quando usuário muda tipo de recorrência, atualiza texto
    selectRecorrencia.addEventListener('change', function() {
        infoTexto.textContent = textosRecorrencia[this.value];
    });
    
    // Define texto inicial baseado na opção selecionada
    infoTexto.textContent = textosRecorrencia[selectRecorrencia.value];
});
</script>

</div>

<!-- ========================================
     SCRIPT DE ACESSIBILIDADE
     ======================================== -->
<!-- VLibras = tradutor de libras para acessibilidade -->
<script src="JS/Vlibras.js"></script>

</body>
</html>

<?php
// ========================================
// PONTOS DE EXPANSÃO PARA SUA PROVA:
// ========================================

/*
EXEMPLO PRÁTICO: ADICIONAR CAMPO "PRIORIDADE"

1. NO BANCO DE DADOS:
ALTER TABLE Avisos ADD COLUMN prioridade ENUM('baixa', 'normal', 'alta', 'urgente') DEFAULT 'normal';

2. NO FORMULÁRIO HTML:
<label for="prioridade">Prioridade:</label>
<select name="prioridade" id="prioridade">
    <option value="baixa" <?php echo (isset($editar_dados) && $editar_dados['prioridade'] == 'baixa') ? 'selected' : ''; ?>>🟢 Baixa</option>
    <option value="normal" <?php echo (!isset($editar_dados) || $editar_dados['prioridade'] == 'normal') ? 'selected' : ''; ?>>🔵 Normal</option>
    <option value="alta" <?php echo (isset($editar_dados) && $editar_dados['prioridade'] == 'alta') ? 'selected' : ''; ?>>🟡 Alta</option>
    <option value="urgente" <?php echo (isset($editar_dados) && $editar_dados['prioridade'] == 'urgente') ? 'selected' : ''; ?>>🔴 Urgente</option>
</select>

3. NO PHP (coleta):
$prioridade = $_POST['prioridade'];

4. NO SQL DE INSERÇÃO:
$sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao, data_inicial, tipo_recorrencia, prioridade, ativo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE)";
$stmt->bind_param("isssssss", $usuario_id, $tipo_aviso, $titulo, $descricao, $data_publicacao, $data_inicial, $tipo_recorrencia, $prioridade);

5. NA TABELA DE EXIBIÇÃO:
$prioridade_icons = [
    'baixa' => '🟢',
    'normal' => '🔵', 
    'alta' => '🟡',
    'urgente' => '🔴'
];
echo "<td>" . $prioridade_icons[$row['prioridade']] . " " . ucfirst($row['prioridade']) . "</td>";

EXEMPLO: ADICIONAR CAMPO "PÚBLICO-ALVO"

1. NO BANCO:
ALTER TABLE Avisos ADD COLUMN publico_alvo ENUM('todos', 'estudantes', 'professores', 'curso_especifico') DEFAULT 'todos';
ALTER TABLE Avisos ADD COLUMN curso_especifico_id INT NULL;

2. NO FORMULÁRIO:
<label for="publico_alvo">Público-alvo:</label>
<select name="publico_alvo" id="publico_alvo" onchange="toggleCursoEspecifico()">
    <option value="todos">Todos os usuários</option>
    <option value="estudantes">Apenas estudantes</option>
    <option value="professores">Apenas professores</option>
    <option value="curso_especifico">Curso específico</option>
</select>

<div id="curso-especifico" style="display: none;">
    <label for="curso_especifico_id">Curso:</label>
    <select name="curso_especifico_id" id="curso_especifico_id">
        <option value="">Selecione o curso</option>
        <?php
        // Buscar cursos do banco
        $sql_cursos = "SELECT curso_id, nome FROM Cursos ORDER BY nome";
        $result_cursos = $conn->query($sql_cursos);
        while ($curso = $result_cursos->fetch_assoc()) {
            echo "<option value='" . $curso['curso_id'] . "'>" . htmlspecialchars($curso['nome']) . "</option>";
        }
        ?>
    </select>
</div>

3. NO JAVASCRIPT:
function toggleCursoEspecifico() {
    const select = document.getElementById('publico_alvo');
    const div = document.getElementById('curso-especifico');
    
    if (select.value === 'curso_especifico') {
        div.style.display = 'block';
        document.getElementById('curso_especifico_id').required = true;
    } else {
        div.style.display = 'none';
        document.getElementById('curso_especifico_id').required = false;
    }
}

4. NO SISTEMA DE NOTIFICAÇÕES:
// Modificar SQL de notificação baseado no público-alvo
$sql_notif = match($publico_alvo) {
    'todos' => "INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id)
                SELECT u.usuario_id, 'aviso', CONCAT('Novo aviso: ', ?), ?, ?
                FROM Usuarios u",
    'estudantes' => "INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id)
                     SELECT u.usuario_id, 'aviso', CONCAT('Novo aviso: ', ?), ?, ?
                     FROM Usuarios u WHERE u.tipo = 'estudante'",
    'professores' => "INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id)
                      SELECT u.usuario_id, 'aviso', CONCAT('Novo aviso: ', ?), ?, ?
                      FROM Usuarios u WHERE u.tipo = 'professor'",
    'curso_especifico' => "INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id)
                           SELECT u.usuario_id, 'aviso', CONCAT('Novo aviso: ', ?), ?, ?
                           FROM Usuarios u WHERE u.curso_id = ?",
    default => ""
};

EXEMPLO: SISTEMA DE ANEXOS

1. NO BANCO:
CREATE TABLE Anexos_Avisos (
    anexo_id INT PRIMARY KEY AUTO_INCREMENT,
    aviso_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    tamanho_arquivo INT NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aviso_id) REFERENCES Avisos(aviso_id)
);

2. NO FORMULÁRIO:
<label for="anexos">Anexos (opcional):</label>
<input type="file" name="anexos[]" id="anexos" multiple accept=".pdf,.doc,.docx,.jpg,.png">
<small>Formatos aceitos: PDF, DOC, DOCX, JPG, PNG. Máximo 5MB por arquivo.</small>

3. NO PHP (processamento de upload):
if (!empty($_FILES['anexos']['name'][0])) {
    $upload_dir = 'uploads/avisos/';
    
    foreach ($_FILES['anexos']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['anexos']['error'][$key] === UPLOAD_ERR_OK) {
            $nome_original = $_FILES['anexos']['name'][$key];
            $tamanho = $_FILES['anexos']['size'][$key];
            $tipo = $_FILES['anexos']['type'][$key];
            
            // Gerar nome único para evitar conflitos
            $nome_unico = uniqid() . '_' . $nome_original;
            $caminho_completo = $upload_dir . $nome_unico;
            
            if (move_uploaded_file($tmp_name, $caminho_completo)) {
                // Salvar no banco
                $sql_anexo = "INSERT INTO Anexos_Avisos (aviso_id, nome_arquivo, caminho_arquivo, tamanho_arquivo, tipo_arquivo) 
                              VALUES (?, ?, ?, ?, ?)";
                $stmt_anexo = $conn->prepare($sql_anexo);
                $stmt_anexo->bind_param("issis", $aviso_id, $nome_original, $caminho_completo, $tamanho, $tipo);
                $stmt_anexo->execute();
            }
        }
    }
}

EXEMPLO: SISTEMA DE COMENTÁRIOS

1. NO BANCO:
CREATE TABLE Comentarios_Avisos (
    comentario_id INT PRIMARY KEY AUTO_INCREMENT,
    aviso_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    data_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (aviso_id) REFERENCES Avisos(aviso_id),
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(usuario_id)
);

2. NA EXIBIÇÃO DO AVISO:
// Buscar comentários
$sql_comentarios = "SELECT c.*, u.nome as autor_nome 
                     FROM Comentarios_Avisos c 
                     JOIN Usuarios u ON c.usuario_id = u.usuario_id 
                     WHERE c.aviso_id = ? AND c.ativo = TRUE 
                     ORDER BY c.data_comentario DESC";

3. FORMULÁRIO DE COMENTÁRIO:
<div class="comentarios-section">
    <h4>Comentários</h4>
    
    <form method="POST" action="adicionar_comentario.php">
        <input type="hidden" name="aviso_id" value="<?php echo $aviso_id; ?>">
        <textarea name="comentario" placeholder="Adicione um comentário..." required></textarea>
        <button type="submit">Comentar</button>
    </form>
    
    <div class="lista-comentarios">
        <?php while ($comentario = $result_comentarios->fetch_assoc()): ?>
            <div class="comentario-item">
                <strong><?php echo htmlspecialchars($comentario['autor_nome']); ?></strong>
                <span class="data"><?php echo date('d/m/Y H:i', strtotime($comentario['data_comentario'])); ?></span>
                <p><?php echo htmlspecialchars($comentario['comentario']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>

ESTATÍSTICAS ÚTEIS:

// Contar avisos por tipo
$sql_stats = "SELECT 
    COUNT(*) as total_avisos,
    COUNT(CASE WHEN tipo_aviso = 'aviso' THEN 1 END) as avisos_gerais,
    COUNT(CASE WHEN tipo_aviso = 'oportunidade' THEN 1 END) as oportunidades,
    COUNT(CASE WHEN prioridade = 'urgente' THEN 1 END) as urgentes
FROM Avisos 
WHERE usuario_id = ? AND ativo = TRUE";

// Avisos mais visualizados (se tiver sistema de views)
$sql_populares = "SELECT a.titulo, COUNT(v.view_id) as visualizacoes
                   FROM Avisos a 
                   LEFT JOIN Views_Avisos v ON a.aviso_id = v.aviso_id
                   WHERE a.usuario_id = ? AND a.ativo = TRUE
                   GROUP BY a.aviso_id
                   ORDER BY visualizacoes DESC
                   LIMIT 5";
*/
?>
