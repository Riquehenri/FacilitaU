<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Incluir header e configuração do banco
include 'header.php';
include 'config.php';

$usuario_id = $_SESSION['usuario_id'];
$tipo_usuario = $_SESSION['tipo'];
$page_title = "Calendário - Facilita U";

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

// Buscar eventos do mês para exibição no calendário
function buscarEventosMes($conn, $usuario_id, $tipo_usuario, $mes, $ano) {
    $eventos = [];
    
    // Para estudantes, buscar seus planejamentos
    if ($tipo_usuario === 'estudante') {
        $sql_plan = "SELECT *, 'planejamento' as tipo_evento FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE";
        $stmt_plan = $conn->prepare($sql_plan);
        if ($stmt_plan) {
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
    }
    
    // Buscar avisos do mês (todos veem)
    $sql_avisos = "SELECT a.*, u.nome as autor_nome, u.tipo as autor_tipo, 'aviso' as tipo_evento
                   FROM Avisos a 
                   JOIN Usuarios u ON a.usuario_id = u.usuario_id 
                   WHERE a.ativo = TRUE";
    $stmt_avisos = $conn->prepare($sql_avisos);
    if ($stmt_avisos) {
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

    <!-- Variáveis PHP para JavaScript -->
    <script>
        const tipoUsuario = '<?php echo $tipo_usuario; ?>';
        const ajaxUrl = 'calendario-ajax.php'; // URL para o arquivo de processamento AJAX
        
        // Debug: verificar se as variáveis estão sendo passadas corretamente
        console.log('Tipo de usuário:', tipoUsuario);
        console.log('URL para AJAX:', ajaxUrl);
        console.log('Página carregada com sucesso');
    </script>
    
    <script src="JS/calendario.js"></script>
</body>
</html>