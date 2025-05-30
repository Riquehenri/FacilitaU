<?php
session_start();
$page_title = "Calendário - Facilita U";

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Inclui configuração do banco
include 'config.php';
include 'header.php';

$usuario_id = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo'];

// Configurações de data
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

// Validação de mês
if ($mes < 1) {
    $mes = 12;
    $ano--;
} elseif ($mes > 12) {
    $mes = 1;
    $ano++;
}

// Informações do mês
$primeiro_dia = mktime(0, 0, 0, $mes, 1, $ano);
$dias_no_mes = date('t', $primeiro_dia);
$dia_semana_inicio = date('w', $primeiro_dia);

// Mês anterior e próximo
$mes_anterior = $mes - 1;
$ano_anterior = $ano;
if ($mes_anterior < 1) {
    $mes_anterior = 12;
    $ano_anterior--;
}

$mes_proximo = $mes + 1;
$ano_proximo = $ano;
if ($mes_proximo > 12) {
    $mes_proximo = 1;
    $ano_proximo++;
}

$hoje = date('Y-n-j');
$meses_pt = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// Função para calcular se um evento recorrente deve aparecer em uma data específica
function eventoAparecemData($data_inicial, $tipo_recorrencia, $data_verificar) {
    $data_inicial_obj = new DateTime($data_inicial);
    $data_verificar_obj = new DateTime($data_verificar);
    
    // Se a data de verificação é anterior à data inicial, não aparece
    if ($data_verificar_obj < $data_inicial_obj) {
        return false;
    }
    
    switch ($tipo_recorrencia) {
        case 'nao':
            // Apenas na data inicial
            return $data_inicial === $data_verificar;
            
        case 'diario':
            // Todos os dias a partir da data inicial
            return true;
            
        case 'semanal':
            // Mesmo dia da semana
            return $data_inicial_obj->format('w') === $data_verificar_obj->format('w');
            
        case 'mensal':
            // Mesmo dia do mês
            return $data_inicial_obj->format('j') === $data_verificar_obj->format('j');
            
        case 'anual':
            // Mesma data (dia e mês)
            return $data_inicial_obj->format('m-d') === $data_verificar_obj->format('m-d');
            
        default:
            return false;
    }
}

// Processar requisições AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $acao = $input['acao'] ?? '';
    
    try {
        switch ($acao) {
            case 'criar_planejamento':
                // Apenas estudantes podem criar planejamentos pessoais
                if ($tipo_usuario !== 'estudante') {
                    echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                    exit;
                }
                
                $atividade = $input['atividade'];
                $horario_inicio = $input['horario_inicio'];
                $duracao = $input['duracao'] ?? 60;
                $horario_fim = date('H:i:s', strtotime($horario_inicio . ' + ' . $duracao . ' minutes'));
                $data_inicial = $input['data'];
                $tipo_recorrencia = $input['repetir'] ?? 'nao';
                
                // Converter data para dia da semana para compatibilidade
                $data_obj = new DateTime($data_inicial);
                $dias_semana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
                $dia_semana = $dias_semana[$data_obj->format('w')];
                
                // Verificar se já existe um planejamento similar
                $sql_check = "SELECT COUNT(*) as count FROM Planejamento_Estudos 
                              WHERE usuario_id = ? AND atividade = ? AND horario_inicio = ? 
                              AND data_inicial = ? AND tipo_recorrencia = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("issss", $usuario_id, $atividade, $horario_inicio, $data_inicial, $tipo_recorrencia);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $exists = $result_check->fetch_assoc()['count'] > 0;
                
                if ($exists) {
                    echo json_encode(['success' => false, 'message' => 'Já existe um planejamento similar!']);
                    exit;
                }
                
                $sql = "INSERT INTO Planejamento_Estudos (usuario_id, dia_semana, horario_inicio, horario_fim, atividade, data_inicial, tipo_recorrencia) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssss", $usuario_id, $dia_semana, $horario_inicio, $horario_fim, $atividade, $data_inicial, $tipo_recorrencia);
                
                if ($stmt->execute()) {
                    $mensagem = match($tipo_recorrencia) {
                        'nao' => 'Planejamento criado para o dia selecionado!',
                        'diario' => 'Planejamento criado com repetição diária!',
                        'semanal' => 'Planejamento criado com repetição semanal!',
                        'mensal' => 'Planejamento criado com repetição mensal!',
                        'anual' => 'Planejamento criado com repetição anual!',
                        default => 'Planejamento criado!'
                    };
                    echo json_encode(['success' => true, 'message' => $mensagem]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao criar planejamento']);
                }
                break;
                
            case 'criar_aviso':
                // Apenas professores e coordenadores podem criar avisos
                if (!in_array($tipo_usuario, ['professor', 'coordenador'])) {
                    echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                    exit;
                }
                
                $titulo = $input['titulo'];
                $descricao = $input['descricao'];
                $tipo_aviso = $input['tipo_aviso'];
                $data_inicial = $input['data'];
                $tipo_recorrencia = $input['repetir'] ?? 'nao';
                
                // Verificar se já existe um aviso similar
                $sql_check = "SELECT COUNT(*) as count FROM Avisos 
                              WHERE usuario_id = ? AND titulo = ? AND data_inicial = ? AND tipo_recorrencia = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("isss", $usuario_id, $titulo, $data_inicial, $tipo_recorrencia);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $exists = $result_check->fetch_assoc()['count'] > 0;
                
                if ($exists) {
                    echo json_encode(['success' => false, 'message' => 'Já existe um aviso similar!']);
                    exit;
                }
                
                $sql = "INSERT INTO Avisos (usuario_id, tipo_aviso, titulo, descricao, data_publicacao, data_inicial, tipo_recorrencia) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssss", $usuario_id, $tipo_aviso, $titulo, $descricao, $data_inicial, $data_inicial, $tipo_recorrencia);
                
                if ($stmt->execute()) {
                    // Criar notificação apenas para a data inicial
                    $aviso_id = $conn->insert_id;
                    $sql_notif = "INSERT INTO Notificacoes (usuario_id, tipo_notificacao, mensagem, data_notificacao, aviso_id)
                                  SELECT u.usuario_id, 'aviso', CONCAT('Novo aviso: ', ?), ?, ?
                                  FROM Usuarios u WHERE u.tipo = 'estudante'";
                    $stmt_notif = $conn->prepare($sql_notif);
                    $stmt_notif->bind_param("ssi", $titulo, $data_inicial, $aviso_id);
                    $stmt_notif->execute();
                    
                    $mensagem = match($tipo_recorrencia) {
                        'nao' => 'Aviso criado para o dia selecionado!',
                        'semanal' => 'Aviso criado com repetição semanal!',
                        'mensal' => 'Aviso criado com repetição mensal!',
                        'anual' => 'Aviso criado com repetição anual!',
                        default => 'Aviso criado!'
                    };
                    echo json_encode(['success' => true, 'message' => $mensagem]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao criar aviso']);
                }
                break;
                
            case 'buscar_eventos':
                $data = $input['data'];
                $eventos = [];
                
                // Buscar planejamentos do usuário (apenas para estudantes)
                if ($tipo_usuario === 'estudante') {
                    $sql_plan = "SELECT * FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE";
                    $stmt_plan = $conn->prepare($sql_plan);
                    $stmt_plan->bind_param("i", $usuario_id);
                    $stmt_plan->execute();
                    $result_plan = $stmt_plan->get_result();
                    
                    while ($row = $result_plan->fetch_assoc()) {
                        // Verificar se o evento deve aparecer nesta data
                        if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data)) {
                            $eventos[] = [
                                'id' => $row['planejamento_id'],
                                'tipo' => 'planejamento',
                                'titulo' => $row['atividade'],
                                'horario_inicio' => $row['horario_inicio'],
                                'horario_fim' => $row['horario_fim'],
                                'tipo_recorrencia' => $row['tipo_recorrencia'],
                                'pode_editar' => true
                            ];
                        }
                    }
                }
                
                // Buscar avisos (todos os usuários veem)
                $sql_avisos = "SELECT a.*, u.nome as autor_nome, u.tipo as autor_tipo 
                              FROM Avisos a 
                              JOIN Usuarios u ON a.usuario_id = u.usuario_id 
                              WHERE a.ativo = TRUE";
                $stmt_avisos = $conn->prepare($sql_avisos);
                $stmt_avisos->execute();
                $result_avisos = $stmt_avisos->get_result();
                
                while ($row = $result_avisos->fetch_assoc()) {
                    // Verificar se o aviso deve aparecer nesta data
                    if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data)) {
                        $eventos[] = [
                            'id' => $row['aviso_id'],
                            'tipo' => 'aviso_' . $row['autor_tipo'],
                            'titulo' => $row['titulo'],
                            'descricao' => $row['descricao'],
                            'autor' => $row['autor_nome'],
                            'tipo_aviso' => $row['tipo_aviso'],
                            'tipo_recorrencia' => $row['tipo_recorrencia'],
                            'pode_editar' => ($row['usuario_id'] == $usuario_id && in_array($tipo_usuario, ['professor', 'coordenador']))
                        ];
                    }
                }
                
                echo json_encode(['success' => true, 'eventos' => $eventos]);
                break;
                
            case 'remover_planejamento':
                if ($tipo_usuario !== 'estudante') {
                    echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                    exit;
                }
                
                $sql = "UPDATE Planejamento_Estudos SET ativo = FALSE WHERE planejamento_id = ? AND usuario_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $input['id'], $usuario_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Planejamento removido!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao remover']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit;
}

// Buscar eventos do mês para exibição no calendário
function buscarEventosMes($conn, $usuario_id, $tipo_usuario, $mes, $ano) {
    $eventos = [];
    
    // Para estudantes, buscar seus planejamentos
    if ($tipo_usuario === 'estudante') {
        $sql_plan = "SELECT *, 'planejamento' as tipo_evento FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE";
        $stmt_plan = $conn->prepare($sql_plan);
        $stmt_plan->bind_param("i", $usuario_id);
        $stmt_plan->execute();
        $result_plan = $stmt_plan->get_result();
        
        while ($row = $result_plan->fetch_assoc()) {
            // Verificar cada dia do mês para ver se o evento deve aparecer
            for ($dia = 1; $dia <= date('t', mktime(0, 0, 0, $mes, 1, $ano)); $dia++) {
                $data_verificar = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                
                if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data_verificar)) {
                    if (!isset($eventos[$data_verificar])) {
                        $eventos[$data_verificar] = [];
                    }
                    $eventos[$data_verificar][] = $row;
                }
            }
        }
    }
    
    // Buscar avisos do mês (todos veem)
    $sql_avisos = "SELECT a.*, u.nome as autor_nome, u.tipo as autor_tipo, 'aviso' as tipo_evento
                   FROM Avisos a 
                   JOIN Usuarios u ON a.usuario_id = u.usuario_id 
                   WHERE a.ativo = TRUE";
    $stmt_avisos = $conn->prepare($sql_avisos);
    $stmt_avisos->execute();
    $result_avisos = $stmt_avisos->get_result();
    
    while ($row = $result_avisos->fetch_assoc()) {
        // Verificar cada dia do mês para ver se o aviso deve aparecer
        for ($dia = 1; $dia <= date('t', mktime(0, 0, 0, $mes, 1, $ano)); $dia++) {
            $data_verificar = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
            
            if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data_verificar)) {
                if (!isset($eventos[$data_verificar])) {
                    $eventos[$data_verificar] = [];
                }
                $eventos[$data_verificar][] = $row;
            }
        }
    }
    
    return $eventos;
}

$eventos = buscarEventosMes($conn, $usuario_id, $tipo_usuario, $mes, $ano);

function contarEventos($data, $eventos) {
    return isset($eventos[$data]) ? count($eventos[$data]) : 0;
}

function getTiposEventos($data, $eventos) {
    if (!isset($eventos[$data])) return [];
    $tipos = [];
    foreach ($eventos[$data] as $evento) {
        $tipo = $evento['tipo_evento'] === 'aviso' ? 'aviso_' . $evento['autor_tipo'] : $evento['tipo_evento'];
        if (!in_array($tipo, $tipos)) {
            $tipos[] = $tipo;
        }
    }
    return $tipos;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/calendario.css">
    
</head>
<body>
   

    <div class="container">
        <div class="calendario-container">
            <div class="calendario-header">
                <div class="navegacao">
                    <button class="btn-nav" onclick="navegarMes(<?php echo $mes_anterior; ?>, <?php echo $ano_anterior; ?>)">
                        &#8249;
                    </button>
                    <button class="btn-nav" onclick="navegarMes(<?php echo $mes_proximo; ?>, <?php echo $ano_proximo; ?>)">
                        &#8250;
                    </button>
                </div>
                <div class="mes-ano">
                    <?php echo $meses_pt[$mes] . ' ' . $ano; ?>
                </div>
            </div>

            <div class="calendario-grid">
                <div class="dias-semana">
                    <div class="dia-semana">Dom</div>
                    <div class="dia-semana">Seg</div>
                    <div class="dia-semana">Ter</div>
                    <div class="dia-semana">Qua</div>
                    <div class="dia-semana">Qui</div>
                    <div class="dia-semana">Sex</div>
                    <div class="dia-semana">Sáb</div>
                </div>

                <div class="dias-mes">
                    <?php
                    // Espaços vazios antes do primeiro dia
                    for ($i = 0; $i < $dia_semana_inicio; $i++) {
                        echo '<div class="dia"></div>';
                    }

                    // Dias do mês
                    for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
                        $data_completa = $ano . '-' . sprintf('%02d', $mes) . '-' . sprintf('%02d', $dia);
                        $data_simples = $ano . '-' . $mes . '-' . $dia;
                        $classes = ['dia'];
                        
                        // Verificar se é hoje
                        if ($data_simples === $hoje) {
                            $classes[] = 'hoje';
                        }
                        
                        // Verificar se tem eventos
                        $num_eventos = contarEventos($data_completa, $eventos);
                        $tipos_eventos = getTiposEventos($data_completa, $eventos);
                        
                        if ($num_eventos > 0) {
                            $classes[] = 'com-eventos';
                        }
                        
                        echo '<div class="' . implode(' ', $classes) . '" onclick="selecionarDia(' . $dia . ', ' . $mes . ', ' . $ano . ')" data-dia="' . $dia . '" data-data="' . $data_completa . '">';
                        echo $dia;
                        
                        if ($num_eventos > 0) {
                            echo '<div class="contador-eventos">' . $num_eventos . '</div>';
                            echo '<div class="indicadores-tipo">';
                            foreach ($tipos_eventos as $tipo) {
                                echo '<div class="indicador ' . $tipo . '"></div>';
                            }
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="painel-lateral">
            <div class="painel-header">
                <h3>📚 Calendário de Estudos</h3>
            </div>
            <div class="painel-content">
                <div id="alert-container"></div>
                
                <div id="info-inicial">
                    <p style="text-align: center; color: #666; margin-top: 50px;">
                        Clique em um dia do calendário para ver os eventos e 
                        <?php echo $tipo_usuario === 'estudante' ? 'adicionar planejamentos' : 'criar avisos'; ?>.
                    </p>
                </div>

                <div id="dia-detalhado" class="dia-detalhado">
                    <h4 id="titulo-dia"></h4>
                    
                    <?php if ($tipo_usuario === 'estudante'): ?>
                        <button class="btn-adicionar" onclick="abrirModal('planejamento')">
                            ➕ Adicionar Planejamento
                        </button>
                    <?php else: ?>
                        <button class="btn-adicionar" onclick="abrirModal('aviso')">
                            ➕ Criar Aviso
                        </button>
                    <?php endif; ?>
                    
                    <div id="eventos-container" class="eventos-lista"></div>
                </div>

                <div class="legenda">
                    <h5 style="margin-bottom: 10px;">Legenda:</h5>
                    <?php if ($tipo_usuario === 'estudante'): ?>
                        <div class="legenda-item">
                            <div class="legenda-cor" style="background: #4ecdc4;"></div>
                            <span>Meu Planejamento</span>
                        </div>
                    <?php endif; ?>
                    <div class="legenda-item">
                        <div class="legenda-cor" style="background: #ff9f43;"></div>
                        <span>Aviso de Professor</span>
                    </div>
                    <div class="legenda-item">
                        <div class="legenda-cor" style="background: #ee5a52;"></div>
                        <span>Aviso de Coordenador</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para planejamento (estudantes) -->
    <div id="modal-planejamento" class="modal">
        <div class="modal-content">
            <h3>Adicionar Planejamento de Estudos</h3>
            <form id="form-planejamento">
                <div class="form-group">
                    <label for="atividade">Atividade:</label>
                    <input type="text" id="atividade" required placeholder="Ex: Estudar Matemática - Álgebra">
                </div>
                <div class="form-group">
                    <label for="horario-inicio">Horário de Início:</label>
                    <input type="time" id="horario-inicio" required>
                </div>
                <div class="form-group">
                    <label for="duracao">Duração (minutos):</label>
                    <input type="number" id="duracao" min="15" max="480" step="15" value="60">
                </div>
                
                <div class="recorrencia-section">
                    <h4><i class="fas fa-repeat"></i> Repetir Evento</h4>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="nao-repetir" name="repetir" value="nao" checked>
                            <label for="nao-repetir">Não repetir (apenas neste dia)</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="repetir-diario" name="repetir" value="diario">
                            <label for="repetir-diario">Repetir diariamente</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="repetir-semanal" name="repetir" value="semanal">
                            <label for="repetir-semanal">Repetir semanalmente</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="repetir-mensal" name="repetir" value="mensal">
                            <label for="repetir-mensal">Repetir mensalmente</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="repetir-anual" name="repetir" value="anual">
                            <label for="repetir-anual">Repetir anualmente</label>
                        </div>
                    </div>
                    <div class="info-recorrencia">
                        <i class="fas fa-info-circle"></i> 
                        <span id="info-texto">O evento será criado apenas para o dia selecionado.</span>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para aviso (professores/coordenadores) -->
    <div id="modal-aviso" class="modal">
        <div class="modal-content">
            <h3>Criar Aviso</h3>
            <form id="form-aviso">
                <div class="form-group">
                    <label for="tipo-aviso">Tipo de Aviso:</label>
                    <select id="tipo-aviso" required>
                        <option value="aviso">Aviso Geral</option>
                        <option value="oportunidade">Oportunidade</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="titulo-aviso">Título:</label>
                    <input type="text" id="titulo-aviso" required placeholder="Ex: Prova de Matemática">
                </div>
                <div class="form-group">
                    <label for="descricao-aviso">Descrição:</label>
                    <textarea id="descricao-aviso" rows="4" required placeholder="Detalhes do aviso..."></textarea>
                </div>
                
                <div class="recorrencia-section">
                    <h4><i class="fas fa-repeat"></i> Repetir Aviso</h4>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="aviso-nao-repetir" name="repetir-aviso" value="nao" checked>
                            <label for="aviso-nao-repetir">Não repetir (apenas neste dia)</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="aviso-repetir-semanal" name="repetir-aviso" value="semanal">
                            <label for="aviso-repetir-semanal">Repetir semanalmente</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="aviso-repetir-mensal" name="repetir-aviso" value="mensal">
                            <label for="aviso-repetir-mensal">Repetir mensalmente</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="aviso-repetir-anual" name="repetir-aviso" value="anual">
                            <label for="aviso-repetir-anual">Repetir anualmente</label>
                        </div>
                    </div>
                    <div class="info-recorrencia">
                        <i class="fas fa-info-circle"></i> 
                        <span id="info-texto-aviso">O aviso será criado apenas para o dia selecionado.</span>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Aviso</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let diaSelecionado = null;
        let dataSelecionada = null;
        const tipoUsuario = '<?php echo $tipo_usuario; ?>';

        // Textos informativos para recorrência
        const textosRecorrencia = {
            'nao': 'O evento será criado apenas para o dia selecionado.',
            'diario': 'O evento aparecerá todos os dias a partir da data selecionada.',
            'semanal': 'O evento aparecerá toda semana no mesmo dia da semana.',
            'mensal': 'O evento aparecerá todo mês no mesmo dia do mês.',
            'anual': 'O evento aparecerá todo ano na mesma data.'
        };

        const textosRecorrenciaAviso = {
            'nao': 'O aviso será criado apenas para o dia selecionado.',
            'semanal': 'O aviso aparecerá toda semana no mesmo dia da semana.',
            'mensal': 'O aviso aparecerá todo mês no mesmo dia do mês.',
            'anual': 'O aviso aparecerá todo ano na mesma data.'
        };

        // Event listeners para mudança de recorrência
        document.addEventListener('DOMContentLoaded', function() {
            // Para planejamentos
            document.querySelectorAll('input[name="repetir"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('info-texto').textContent = textosRecorrencia[this.value];
                });
            });

            // Para avisos
            document.querySelectorAll('input[name="repetir-aviso"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('info-texto-aviso').textContent = textosRecorrenciaAviso[this.value];
                });
            });
        });

        function navegarMes(mes, ano) {
            window.location.href = `?mes=${mes}&ano=${ano}`;
        }

        function selecionarDia(dia, mes, ano) {
            // Remove seleção anterior
            if (diaSelecionado) {
                diaSelecionado.classList.remove('selecionado');
            }

            // Seleciona novo dia
            const elementoDia = event.target;
            elementoDia.classList.add('selecionado');
            diaSelecionado = elementoDia;
            dataSelecionada = elementoDia.dataset.data;

            // Mostra detalhes do dia
            mostrarDetalhesDia(dia, mes, ano, dataSelecionada);
        }

        function mostrarDetalhesDia(dia, mes, ano, data) {
            const meses = [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ];

            document.getElementById('info-inicial').style.display = 'none';
            document.getElementById('dia-detalhado').classList.add('ativo');
            document.getElementById('titulo-dia').textContent = `${dia} de ${meses[mes - 1]} de ${ano}`;

            // Buscar eventos do dia
            buscarEventos(data);
        }

        async function buscarEventos(data) {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        acao: 'buscar_eventos',
                        data: data
                    })
                });

                const resultado = await response.json();
                
                if (resultado.success) {
                    exibirEventos(resultado.eventos);
                } else {
                    mostrarAlerta('Erro ao carregar eventos: ' + resultado.message, 'error');
                }
            } catch (error) {
                mostrarAlerta('Erro de conexão: ' + error.message, 'error');
            }
        }

        function exibirEventos(eventos) {
            const container = document.getElementById('eventos-container');
            container.innerHTML = '';

            if (eventos.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #666; margin: 20px 0;">Nenhum evento neste dia.</p>';
                return;
            }

            eventos.forEach(evento => {
                const eventoDiv = document.createElement('div');
                eventoDiv.className = `evento-item ${evento.tipo}`;
                
                let conteudo = `<div class="evento-titulo">${evento.titulo}</div>`;
                
                if (evento.tipo === 'planejamento') {
                    conteudo += `<div class="evento-info">
                        ${evento.horario_inicio} - ${evento.horario_fim}
                    </div>`;
                    
                    // Mostrar tipo de recorrência
                    if (evento.tipo_recorrencia && evento.tipo_recorrencia !== 'nao') {
                        const tiposTexto = {
                            'diario': '🔄 Diário',
                            'semanal': '📅 Semanal', 
                            'mensal': '📆 Mensal',
                            'anual': '🗓️ Anual'
                        };
                        conteudo += `<div class="evento-recorrencia">${tiposTexto[evento.tipo_recorrencia] || ''}</div>`;
                    }
                    
                    if (evento.pode_editar) {
                        conteudo += `<button class="btn-remover" onclick="removerPlanejamento(${evento.id})">✕</button>`;
                    }
                } else {
                    conteudo += `<div class="evento-info">
                        ${evento.descricao}<br>
                        <strong>Por:</strong> ${evento.autor} (${evento.tipo_aviso})
                    </div>`;
                    
                    // Mostrar tipo de recorrência para avisos
                    if (evento.tipo_recorrencia && evento.tipo_recorrencia !== 'nao') {
                        const tiposTexto = {
                            'semanal': '📅 Semanal', 
                            'mensal': '📆 Mensal',
                            'anual': '🗓️ Anual'
                        };
                        conteudo += `<div class="evento-recorrencia">${tiposTexto[evento.tipo_recorrencia] || ''}</div>`;
                    }
                }
                
                eventoDiv.innerHTML = conteudo;
                container.appendChild(eventoDiv);
            });
        }

        function abrirModal(tipo) {
            if (tipo === 'planejamento') {
                document.getElementById('modal-planejamento').style.display = 'block';
            } else {
                document.getElementById('modal-aviso').style.display = 'block';
            }
        }

        function fecharModal() {
            document.getElementById('modal-planejamento').style.display = 'none';
            document.getElementById('modal-aviso').style.display = 'none';
            document.getElementById('form-planejamento').reset();
            document.getElementById('form-aviso').reset();
            
            // Resetar textos informativos
            document.getElementById('info-texto').textContent = textosRecorrencia['nao'];
            document.getElementById('info-texto-aviso').textContent = textosRecorrenciaAviso['nao'];
        }

        async function removerPlanejamento(id) {
            if (confirm('Tem certeza que deseja remover este planejamento? (Isso removerá toda a série de repetições)')) {
                try {
                    const response = await fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            acao: 'remover_planejamento',
                            id: id
                        })
                    });

                    const resultado = await response.json();
                    
                    if (resultado.success) {
                        mostrarAlerta(resultado.message, 'success');
                        buscarEventos(dataSelecionada);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mostrarAlerta('Erro: ' + resultado.message, 'error');
                    }
                } catch (error) {
                    mostrarAlerta('Erro de conexão: ' + error.message, 'error');
                }
            }
        }

        // Submissão do formulário de planejamento
        document.getElementById('form-planejamento').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const repetir = document.querySelector('input[name="repetir"]:checked').value;
            
            const dados = {
                acao: 'criar_planejamento',
                data: dataSelecionada,
                atividade: document.getElementById('atividade').value,
                horario_inicio: document.getElementById('horario-inicio').value,
                duracao: document.getElementById('duracao').value,
                repetir: repetir
            };

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dados)
                });

                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarAlerta(resultado.message, 'success');
                    fecharModal();
                    buscarEventos(dataSelecionada);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    mostrarAlerta('Erro: ' + resultado.message, 'error');
                }
            } catch (error) {
                mostrarAlerta('Erro de conexão: ' + error.message, 'error');
            }
        });

        // Submissão do formulário de aviso
        document.getElementById('form-aviso').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const repetir = document.querySelector('input[name="repetir-aviso"]:checked').value;
            
            const dados = {
                acao: 'criar_aviso',
                data: dataSelecionada,
                tipo_aviso: document.getElementById('tipo-aviso').value,
                titulo: document.getElementById('titulo-aviso').value,
                descricao: document.getElementById('descricao-aviso').value,
                repetir: repetir
            };

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dados)
                });

                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarAlerta(resultado.message, 'success');
                    fecharModal();
                    buscarEventos(dataSelecionada);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    mostrarAlerta('Erro: ' + resultado.message, 'error');
                }
            } catch (error) {
                mostrarAlerta('Erro de conexão: ' + error.message, 'error');
            }
        });

        function mostrarAlerta(mensagem, tipo) {
            const container = document.getElementById('alert-container');
            const alerta = document.createElement('div');
            alerta.className = `alert ${tipo}`;
            alerta.textContent = mensagem;
            alerta.style.display = 'block';
            
            container.innerHTML = '';
            container.appendChild(alerta);
            
            setTimeout(() => {
                alerta.style.display = 'none';
            }, 5000);
        }

        // Fechar modal clicando fora
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                fecharModal();
            }
        });

        // Navegação por teclado
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                document.querySelector('.navegacao .btn-nav:first-child').click();
            } else if (e.key === 'ArrowRight') {
                document.querySelector('.navegacao .btn-nav:last-child').click();
            } else if (e.key === 'Escape') {
                fecharModal();
            }
        });
    </script>
</body>
</html>