<?php
// ========================================
// SISTEMA DE PLANEJAMENTO DE ESTUDOS - FACILITA U
// ========================================
// Este arquivo é um sistema CRUD completo (Create, Read, Update, Delete)
// Permite que estudantes criem, visualizem, editem e excluam seus planejamentos de estudo
// 
// FLUXO PRINCIPAL:
// 1. Usuário preenche formulário → 2. PHP processa dados → 3. Salva no banco → 4. Exibe na tabela

// ========================================
// INICIALIZAÇÃO E CONFIGURAÇÕES
// ========================================
// session_start() = inicia sistema de sessões (mantém usuário logado entre páginas)
session_start();

// Título que aparece na aba do navegador
$page_title = "Planejamento de Estudos";

// ========================================
// INCLUSÃO DE ARQUIVOS NECESSÁRIOS
// ========================================
include 'config.php';  // Configurações do banco de dados (host, usuário, senha, nome do banco)
include 'header.php';  // Cabeçalho padrão do site (menu, CSS, estrutura HTML)

// ========================================
// CONTROLE DE ACESSO (SEGURANÇA)
// ========================================
// Verifica se o usuário está logado E se é do tipo 'estudante'
// $_SESSION = array global que mantém dados do usuário entre páginas
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] != 'estudante') {
    // Se não estiver logado OU não for estudante, redireciona para página inicial
    header("Location: index.php");
    exit(); // Para a execução do script (importante após redirecionamento)
}

// Obtém o ID do usuário logado (será usado em todas as operações do banco)
$usuario_id = $_SESSION['usuario_id'];

// ========================================
// OPERAÇÃO 1: EXCLUSÃO DE PLANEJAMENTO (DELETE)
// ========================================
// Esta seção executa quando o usuário clica no link "Excluir"
// O ID do planejamento vem pela URL: planejamento_estudos.php?excluir=123

if (isset($_GET['excluir'])) {
    // $_GET = array com dados da URL
    // isset() = verifica se a variável existe e não é null
    $id = $_GET['excluir']; // ID do planejamento a ser excluído
    
    // ========================================
    // SOFT DELETE (EXCLUSÃO SUAVE)
    // ========================================
    // Ao invés de deletar fisicamente (DELETE FROM), marca como inativo
    // Vantagens: permite recuperação, mantém histórico, compatível com calendário
    
    $sql = "UPDATE Planejamento_Estudos SET ativo = FALSE WHERE planejamento_id = ? AND usuario_id = ?";
    
    // ========================================
    // PREPARED STATEMENT (CONSULTA SEGURA)
    // ========================================
    // Forma segura de executar SQL, previne SQL Injection
    $stmt = $conn->prepare($sql);
    
    // bind_param = associa valores aos placeholders (?)
    // "ii" = dois parâmetros do tipo integer
    $stmt->bind_param("ii", $id, $usuario_id);
    
    // Executa a consulta e verifica se deu certo
    if ($stmt->execute()) {
        // ========================================
        // SISTEMA DE MENSAGENS (FEEDBACK PARA USUÁRIO)
        // ========================================
        // Armazena mensagem na sessão para exibir após redirecionamento
        $_SESSION['mensagem'] = "Planejamento excluído com sucesso!";
        $_SESSION['tipo_mensagem'] = "success"; // Tipo: success, error, warning, info
    } else {
        // Se deu erro, armazena mensagem de erro
        $_SESSION['mensagem'] = "Erro ao excluir: " . $conn->error;
        $_SESSION['tipo_mensagem'] = "error";
    }
    
    $stmt->close(); // Libera recursos do prepared statement
    
    // ========================================
    // PADRÃO PRG (POST-REDIRECT-GET)
    // ========================================
    // Redireciona para evitar reenvio acidental do formulário
    header("Location: planejamento_estudos.php");
    exit();
}

// ========================================
// OPERAÇÃO 2: INSERÇÃO E ATUALIZAÇÃO (CREATE/UPDATE)
// ========================================
// Esta seção executa quando o formulário é enviado (método POST)
// $_SERVER['REQUEST_METHOD'] = método HTTP usado (GET, POST, PUT, DELETE)

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ========================================
    // COLETA DE DADOS DO FORMULÁRIO
    // ========================================
    // $_POST = array com todos os dados enviados pelo formulário
    // Cada campo do formulário (name="campo") vira uma chave no array
    
    $dia_semana = $_POST['dia_semana'];           // Ex: "segunda", "terca", etc.
    $horario_inicio = $_POST['horario_inicio'];   // Ex: "14:00"
    $horario_fim = $_POST['horario_fim'];         // Ex: "16:00"
    $atividade = $_POST['atividade'];             // Ex: "Estudar Matemática - Álgebra"
    
    // ========================================
    // NOVOS CAMPOS PARA INTEGRAÇÃO COM CALENDÁRIO
    // ========================================
    // Operador ?? = null coalescing (se não existir, usa valor padrão)
    $data_inicial = $_POST['data_inicial'] ?? date('Y-m-d');        // Data de início do planejamento
    $tipo_recorrencia = $_POST['tipo_recorrencia'] ?? 'semanal';    // Como o evento se repete
    
    // ========================================
    // VALIDAÇÃO DE DADOS (REGRAS DE NEGÓCIO)
    // ========================================
    // strtotime() = converte string de tempo em timestamp para comparação
    // Verifica se horário de fim é posterior ao de início
    if (strtotime($horario_fim) <= strtotime($horario_inicio)) {
        // Se horário inválido, armazena erro e redireciona
        $_SESSION['mensagem'] = "O horário de término deve ser após o horário de início!";
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: planejamento_estudos.php");
        exit();
    }
    
    // ========================================
    // DECISÃO: EDITAR OU CRIAR NOVO?
    // ========================================
    // Se existe 'editar_id' no POST, é uma edição; senão, é criação
    
    if (isset($_POST['editar_id'])) {
        // ========================================
        // OPERAÇÃO DE ATUALIZAÇÃO (UPDATE)
        // ========================================
        $id = $_POST['editar_id']; // ID do planejamento sendo editado
        
        // SQL para atualizar registro existente
        $sql = "UPDATE Planejamento_Estudos SET dia_semana = ?, horario_inicio = ?, horario_fim = ?, atividade = ?, data_inicial = ?, tipo_recorrencia = ? 
                WHERE planejamento_id = ? AND usuario_id = ?";
        
        $stmt = $conn->prepare($sql);
        
        // bind_param com 8 parâmetros:
        // "ssssssii" = 6 strings + 2 integers
        $stmt->bind_param("ssssssii", $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia, $id, $usuario_id);
        
        if ($stmt->execute()) {
            // ========================================
            // MATCH EXPRESSION (PHP 8+)
            // ========================================
            // Versão moderna e mais limpa do switch/case
            $mensagem_recorrencia = match($tipo_recorrencia) {
                'nao' => '',
                'diario' => ' (repetição diária)',
                'semanal' => ' (repetição semanal)',
                'mensal' => ' (repetição mensal)',
                'anual' => ' (repetição anual)',
                default => ''
            };
            
            $_SESSION['mensagem'] = "Planejamento atualizado com sucesso!" . $mensagem_recorrencia;
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
        // SQL para inserir novo registro
        $sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)";
        
        $stmt = $conn->prepare($sql);
        
        // bind_param com 7 parâmetros (ativo=TRUE é fixo no SQL)
        // "issssss" = 1 integer + 6 strings
        $stmt->bind_param("issssss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia);
        
        if ($stmt->execute()) {
            // Mesma lógica de mensagem da atualização
            $mensagem_recorrencia = match($tipo_recorrencia) {
                'nao' => '',
                'diario' => ' (repetição diária)',
                'semanal' => ' (repetição semanal)',
                'mensal' => ' (repetição mensal)',
                'anual' => ' (repetição anual)',
                default => ''
            };
            
            $_SESSION['mensagem'] = "Planejamento cadastrado com sucesso!" . $mensagem_recorrencia;
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "error";
        }
        $stmt->close();
    }
    
    // Redireciona após processamento (padrão PRG)
    header("Location: planejamento_estudos.php");
    exit();
}

// ========================================
// OPERAÇÃO 3: BUSCAR DADOS PARA EDIÇÃO
// ========================================
// Esta seção executa quando o usuário clica em "Editar"
// O ID vem pela URL: planejamento_estudos.php?editar=123

$editar_id = null;      // ID do planejamento sendo editado
$editar_dados = null;   // Dados do planejamento para preencher o formulário

if (isset($_GET['editar'])) {
    $editar_id = $_GET['editar'];
    
    // Busca dados do planejamento específico
    $sql = "SELECT * FROM Planejamento_Estudos WHERE planejamento_id = ? AND usuario_id = ? AND ativo = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $editar_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // fetch_assoc() = retorna linha como array associativo
    // ['planejamento_id' => 1, 'atividade' => 'Estudar Math', ...]
    $editar_dados = $result->fetch_assoc();
    $stmt->close();
}

// ========================================
// OPERAÇÃO 4: LISTAR TODOS OS PLANEJAMENTOS (READ)
// ========================================
// Busca todos os planejamentos ativos do usuário para exibir na tabela

// SQL com ordenação personalizada usando FIELD()
// FIELD() = define ordem específica para os dias da semana
$sql = "SELECT * FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE ORDER BY 
        FIELD(dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'), 
        horario_inicio";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result(); // Resultado que será usado na tabela HTML

// ========================================
// EXIBIÇÃO DE MENSAGENS DE FEEDBACK
// ========================================
// Verifica se há mensagem armazenada na sessão e exibe
if (isset($_SESSION['mensagem'])) {
    // Exibe mensagem com classe CSS baseada no tipo
    echo "<p class='" . $_SESSION['tipo_mensagem'] . "'>" . $_SESSION['mensagem'] . "</p>";
    
    // Remove mensagem da sessão (exibe apenas uma vez)
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>

<!-- ========================================
     INCLUSÃO DE ARQUIVOS CSS
     ======================================== -->
<!-- CSS específico para a página de planejamento -->
<link rel="stylesheet" href="CSS/planejar_estudos.css">
<link rel="stylesheet" href="CSS/editarPlanejamento.css">

<script>
// ========================================
// JAVASCRIPT: VALIDAÇÃO NO FRONTEND
// ========================================
// Validação adicional no lado do cliente (navegador)
// Importante: NUNCA confie apenas na validação frontend!

function validarHorarios() {
    // Obtém valores dos campos de horário
    const inicio = document.getElementById('horario_inicio').value;
    const fim = document.getElementById('horario_fim').value;
    
    // Verifica se horário de fim é menor ou igual ao de início
    if (fim && inicio && fim <= inicio) {
        alert('O horário de término deve ser após o horário de início!');
        return false; // Cancela envio do formulário
    }
    return true; // Permite envio do formulário
}

// ========================================
// JAVASCRIPT: MELHORIAS DE UX (EXPERIÊNCIA DO USUÁRIO)
// ========================================
// DOMContentLoaded = executa quando HTML foi completamente carregado
document.addEventListener('DOMContentLoaded', () => {
    // Obtém elementos do DOM
    const inicio = document.getElementById('horario_inicio');
    const fim = document.getElementById('horario_fim');
    const selectRecorrencia = document.getElementById('tipo_recorrencia');
    const infoTexto = document.getElementById('info-texto-recorrencia');
    
    // ========================================
    // FUNCIONALIDADE: HORÁRIO MÍNIMO DINÂMICO
    // ========================================
    // Quando usuário seleciona horário de início, atualiza mínimo do horário fim
    if(inicio && fim){
        inicio.addEventListener('change', function() {
            fim.min = this.value; // Define horário mínimo do fim
        });
    }
    
    // ========================================
    // FUNCIONALIDADE: TEXTOS INFORMATIVOS DINÂMICOS
    // ========================================
    // Objeto com explicações para cada tipo de recorrência
    const textosRecorrencia = {
        'nao': 'O planejamento será criado apenas para o dia selecionado.',
        'diario': 'O planejamento aparecerá todos os dias a partir da data selecionada.',
        'semanal': 'O planejamento aparecerá toda semana no mesmo dia da semana.',
        'mensal': 'O planejamento aparecerá todo mês no mesmo dia do mês.',
        'anual': 'O planejamento aparecerá todo ano na mesma data.'
    };
    
    // Atualiza texto quando usuário muda tipo de recorrência
    if (selectRecorrencia && infoTexto) {
        selectRecorrencia.addEventListener('change', function() {
            infoTexto.textContent = textosRecorrencia[this.value];
        });
        
        // Define texto inicial baseado na opção selecionada
        infoTexto.textContent = textosRecorrencia[selectRecorrencia.value];
    }
});
</script>

<!-- ========================================
     INTERFACE HTML: CABEÇALHOS
     ======================================== -->
<h2>Planejamento de Estudos</h2>

<!-- Título dinâmico baseado na operação (criar ou editar) -->
<h3><?php echo isset($editar_id) ? "Editar Planejamento" : "Cadastrar Novo Planejamento"; ?></h3>

<!-- ========================================
     FORMULÁRIO PRINCIPAL
     ======================================== -->
<!-- 
action = para onde os dados serão enviados (mesmo arquivo)
method = como enviar (POST = seguro para dados sensíveis)
onsubmit = função JavaScript executada antes do envio
-->
<form method="POST" action="planejamento_estudos.php" onsubmit="return validarHorarios()">
    
    <!-- ========================================
         CAMPO OCULTO PARA EDIÇÃO
         ======================================== -->
    <!-- Se está editando, inclui ID oculto para identificar o registro -->
    <?php if (isset($editar_id)): ?>
        <input type="hidden" name="editar_id" value="<?php echo $editar_id; ?>">
    <?php endif; ?>
    
    <!-- ========================================
         CAMPO: DIA DA SEMANA
         ======================================== -->
    <label for="dia_semana">Dia da Semana:</label>
    <select name="dia_semana" id="dia_semana" required>
        <option value="">Selecione</option>
        <?php
        // ========================================
        // GERAÇÃO DINÂMICA DE OPÇÕES
        // ========================================
        // Array associativo: chave = valor do banco, valor = texto amigável
        $dias_semana = [
            'segunda' => 'Segunda-feira',
            'terca' => 'Terça-feira',
            'quarta' => 'Quarta-feira',
            'quinta' => 'Quinta-feira',
            'sexta' => 'Sexta-feira',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo'
        ];
        
        // Loop para gerar cada <option>
        foreach ($dias_semana as $valor => $nome) {
            // Verifica se esta opção deve estar selecionada (modo edição)
            $selected = (isset($editar_dados) && $editar_dados['dia_semana'] == $valor) ? 'selected' : '';
            echo "<option value='$valor' $selected>$nome</option>";
        }
        ?>
    </select>

    <!-- ========================================
         CAMPO: HORÁRIO DE INÍCIO
         ======================================== -->
    <label for="horario_inicio">Horário de Início:</label>
    <!-- 
    type="time" = seletor de horário nativo do navegador
    value = valor pré-preenchido (para edição)
    -->
    <input type="time" name="horario_inicio" id="horario_inicio" 
           value="<?php echo isset($editar_dados) ? $editar_dados['horario_inicio'] : ''; ?>" required>

    <!-- ========================================
         CAMPO: HORÁRIO DE FIM
         ======================================== -->
    <label for="horario_fim">Horário de Fim:</label>
    <input type="time" name="horario_fim" id="horario_fim" 
           value="<?php echo isset($editar_dados) ? $editar_dados['horario_fim'] : ''; ?>" required
           min="<?php echo isset($editar_dados) ? $editar_dados['horario_inicio'] : ''; ?>">

    <!-- ========================================
         CAMPO: ATIVIDADE
         ======================================== -->
    <label for="atividade">Atividade:</label>
    <!-- htmlspecialchars() = previne XSS (Cross-Site Scripting) -->
    <input type="text" name="atividade" id="atividade" 
           value="<?php echo isset($editar_dados) ? htmlspecialchars($editar_dados['atividade']) : ''; ?>" required>

    <!-- ========================================
         NOVOS CAMPOS PARA INTEGRAÇÃO COM CALENDÁRIO
         ======================================== -->
    
    <!-- CAMPO: DATA DE INÍCIO -->
    <label for="data_inicial">Data de Início:</label>
    <input type="date" name="data_inicial" id="data_inicial" 
           value="<?php echo isset($editar_dados) ? ($editar_dados['data_inicial'] ?? date('Y-m-d')) : date('Y-m-d'); ?>" required>

    <!-- CAMPO: TIPO DE RECORRÊNCIA -->
    <label for="tipo_recorrencia">Repetir Planejamento:</label>
    <select name="tipo_recorrencia" id="tipo_recorrencia">
        <!-- Cada opção verifica se deve estar selecionada -->
        <option value="nao" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'nao') ? 'selected' : ''; ?>>Não repetir (apenas neste dia)</option>
        <option value="diario" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'diario') ? 'selected' : ''; ?>>Repetir diariamente</option>
        <!-- Semanal é padrão para novos planejamentos -->
        <option value="semanal" <?php echo (!isset($editar_dados) || $editar_dados['tipo_recorrencia'] == 'semanal') ? 'selected' : ''; ?>>Repetir semanalmente</option>
        <option value="mensal" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'mensal') ? 'selected' : ''; ?>>Repetir mensalmente</option>
        <option value="anual" <?php echo (isset($editar_dados) && $editar_dados['tipo_recorrencia'] == 'anual') ? 'selected' : ''; ?>>Repetir anualmente</option>
    </select>

    <!-- ========================================
         ÁREA INFORMATIVA
         ======================================== -->
    <!-- Explica ao usuário o que cada tipo de recorrência significa -->
    <div class="info-recorrencia" style="margin: 10px 0; padding: 10px; background: #f0f8ff; border-left: 4px solid #007bff; font-size: 14px;">
        <strong>ℹ️ Informação:</strong>
        <span id="info-texto-recorrencia">O planejamento aparecerá toda semana no mesmo dia da semana.</span>
    </div>

    <!-- ========================================
         BOTÕES DE AÇÃO
         ======================================== -->
    <!-- Texto do botão muda baseado na operação -->
    <button type="submit"><?php echo isset($editar_id) ? "Atualizar" : "Cadastrar"; ?></button>
    
    <!-- Botão cancelar só aparece no modo edição -->
    <?php if (isset($editar_id)): ?>
        <a href="planejamento_estudos.php" class="cancel-button">Cancelar</a>
    <?php endif; ?>
</form>

<!-- ========================================
     SEÇÃO DE LISTAGEM
     ======================================== -->
<h3>Seu Planejamento</h3>

<?php
// ========================================
// EXIBIÇÃO DOS DADOS EM TABELA
// ========================================
// Verifica se há planejamentos cadastrados
if ($result->num_rows > 0) {
    // Inicia tabela HTML
    echo "<table>";
    echo "<tr><th>Dia da Semana</th><th>Horário Início</th><th>Horário Fim</th><th>Atividade</th><th>Data Início</th><th>Repetição</th><th>Ações</th></tr>";
    
    // ========================================
    // LOOP ATRAVÉS DOS RESULTADOS
    // ========================================
    // fetch_assoc() retorna próxima linha como array associativo
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        
        // ========================================
        // FORMATAÇÃO DE DADOS PARA EXIBIÇÃO
        // ========================================
        
        // Converte código do dia para nome amigável
        echo "<td>" . $dias_semana[$row['dia_semana']] . "</td>";
        
        // substr() = pega apenas HH:MM (remove segundos)
        echo "<td>" . substr($row['horario_inicio'], 0, 5) . "</td>";
        echo "<td>" . substr($row['horario_fim'], 0, 5) . "</td>";
        
        // htmlspecialchars() = segurança contra XSS
        echo "<td>" . htmlspecialchars($row['atividade']) . "</td>";
        
        // Formata data para padrão brasileiro (dd/mm/aaaa)
        echo "<td>" . date('d/m/Y', strtotime($row['data_inicial'] ?? date('Y-m-d'))) . "</td>";
        
        // ========================================
        // CONVERSÃO DE CÓDIGO PARA TEXTO LEGÍVEL
        // ========================================
        // Match expression para converter tipo de recorrência
        $recorrencia_texto = match($row['tipo_recorrencia'] ?? 'semanal') {
            'nao' => 'Não repete',
            'diario' => 'Diário',
            'semanal' => 'Semanal',
            'mensal' => 'Mensal',
            'anual' => 'Anual',
            default => 'Semanal'
        };
        echo "<td>" . $recorrencia_texto . "</td>";
        
        // ========================================
        // AÇÕES (EDITAR E EXCLUIR)
        // ========================================
        echo "<td class='actions'>";
        
        // Link para editar (passa ID pela URL)
        echo "<a href='planejamento_estudos.php?editar=" . $row['planejamento_id'] . "' class='edit-btn'>Editar</a> ";
        
        // Link para excluir com confirmação JavaScript
        echo "<a href='planejamento_estudos.php?excluir=" . $row['planejamento_id'] . "' class='delete-btn' onclick='return confirm(\"Tem certeza que deseja excluir este planejamento?\")'>Excluir</a>";
        
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    // Mensagem quando não há dados
    echo "<p>Nenhum planejamento cadastrado.</p>";
}

// ========================================
// LIMPEZA DE RECURSOS
// ========================================
// Boa prática: sempre fechar statements e conexões
$stmt->close();
$conn->close();
?>

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
ALTER TABLE Planejamento_Estudos ADD COLUMN prioridade ENUM('baixa', 'media', 'alta') DEFAULT 'media';

2. NO FORMULÁRIO HTML:
<label for="prioridade">Prioridade:</label>
<select name="prioridade" id="prioridade">
    <option value="baixa" <?php echo (isset($editar_dados) && $editar_dados['prioridade'] == 'baixa') ? 'selected' : ''; ?>>Baixa</option>
    <option value="media" <?php echo (!isset($editar_dados) || $editar_dados['prioridade'] == 'media') ? 'selected' : ''; ?>>Média</option>
    <option value="alta" <?php echo (isset($editar_dados) && $editar_dados['prioridade'] == 'alta') ? 'selected' : ''; ?>>Alta</option>
</select>

3. NO PHP (coleta):
$prioridade = $_POST['prioridade'];

4. NO SQL DE INSERÇÃO:
$sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia, prioridade, ativo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE)";
$stmt->bind_param("isssssss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia, $prioridade);

5. NO SQL DE ATUALIZAÇÃO:
$sql = "UPDATE Planejamento_Estudos SET dia_semana = ?, horario_inicio = ?, horario_fim = ?, atividade = ?, data_inicial = ?, tipo_recorrencia = ?, prioridade = ? 
        WHERE planejamento_id = ? AND usuario_id = ?";
$stmt->bind_param("sssssssii", $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia, $prioridade, $id, $usuario_id);

6. NA TABELA DE EXIBIÇÃO:
echo "<th>Prioridade</th>"; // No cabeçalho

// No corpo da tabela:
$prioridade_texto = match($row['prioridade']) {
    'baixa' => '🟢 Baixa',
    'media' => '🟡 Média', 
    'alta' => '🔴 Alta',
    default => 'Média'
};
echo "<td>" . $prioridade_texto . "</td>";

OUTROS CAMPOS ÚTEIS PARA PROVA:

1. CAMPO DESCRIÇÃO:
- Tipo: TEXT
- HTML: <textarea name="descricao" rows="3"><?php echo htmlspecialchars($editar_dados['descricao'] ?? ''); ?></textarea>

2. CAMPO STATUS:
- Tipo: ENUM('pendente', 'em_andamento', 'concluido')
- Permite marcar progresso do planejamento

3. CAMPO CATEGORIA:
- Tipo: VARCHAR(50)
- Ex: "Matemática", "História", "Programação"

4. CAMPO COR:
- Tipo: VARCHAR(7) (para hex color)
- HTML: <input type="color" name="cor" value="<?php echo $editar_dados['cor'] ?? '#007bff'; ?>">

5. CAMPO NOTIFICACAO:
- Tipo: BOOLEAN
- HTML: <input type="checkbox" name="notificacao" value="1" <?php echo ($editar_dados['notificacao'] ?? false) ? 'checked' : ''; ?>>

COMO EXIBIR ESTATÍSTICAS:

// Contar total de planejamentos
$sql_stats = "SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN prioridade = 'alta' THEN 1 END) as alta_prioridade,
    SUM(TIMESTAMPDIFF(MINUTE, horario_inicio, horario_fim)) as minutos_totais
FROM Planejamento_Estudos 
WHERE usuario_id = ? AND ativo = TRUE";

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $usuario_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

echo "<div class='estatisticas'>";
echo "<h4>Suas Estatísticas</h4>";
echo "<p>Total de planejamentos: " . $stats['total'] . "</p>";
echo "<p>Alta prioridade: " . $stats['alta_prioridade'] . "</p>";
echo "<p>Horas semanais: " . round($stats['minutos_totais'] / 60, 1) . "h</p>";
echo "</div>";

VALIDAÇÕES ADICIONAIS:

// Verificar conflito de horários
function verificarConflito($conn, $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $excluir_id = null) {
    $sql = "SELECT COUNT(*) as conflitos FROM Planejamento_Estudos 
            WHERE usuario_id = ? AND dia_semana = ? AND ativo = TRUE
            AND (
                (horario_inicio <= ? AND horario_fim > ?) OR
                (horario_inicio < ? AND horario_fim >= ?) OR
                (horario_inicio >= ? AND horario_fim <= ?)
            )";
    
    if ($excluir_id) {
        $sql .= " AND planejamento_id != ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($excluir_id) {
        $stmt->bind_param("issssssssi", $usuario_id, $dia_semana, $horario_inicio, $horario_inicio, $horario_fim, $horario_fim, $horario_inicio, $horario_fim, $excluir_id);
    } else {
        $stmt->bind_param("issssssss", $usuario_id, $dia_semana, $horario_inicio, $horario_inicio, $horario_fim, $horario_fim, $horario_inicio, $horario_fim);
    }
    
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['conflitos'] > 0;
}

// Usar antes de inserir/atualizar:
if (verificarConflito($conn, $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $editar_id ?? null)) {
    $_SESSION['mensagem'] = "Conflito de horário detectado!";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: planejamento_estudos.php");
    exit();
}
*/
?>
