<?php
// ========================================
// ARQUIVO PRINCIPAL DO CALENDÁRIO INTEGRADO
// ========================================
// Este arquivo é responsável por exibir a interface do calendário
// e processar a lógica de exibição dos eventos no frontend

// Iniciar sessão para manter dados do usuário logado
session_start();

// ========================================
// VERIFICAÇÃO DE AUTENTICAÇÃO
// ========================================
// Verificar se o usuário está logado antes de mostrar o calendário
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, redireciona para página de login
    header("Location: index.php");
    exit(); // Para a execução do script
}

// ========================================
// INCLUSÃO DE ARQUIVOS NECESSÁRIOS
// ========================================
// Incluir header (cabeçalho padrão do site) e configuração do banco
include 'header.php';  // Contém HTML padrão, menu, CSS
include 'config.php';  // Contém configurações de conexão com banco de dados

// ========================================
// OBTER DADOS DO USUÁRIO LOGADO
// ========================================
$usuario_id = $_SESSION['usuario_id'];     // ID único do usuário
$tipo_usuario = $_SESSION['tipo'];         // Tipo: estudante, professor, coordenador
$page_title = "Calendário - Facilita U";   // Título da página

// ========================================
// PROCESSAMENTO DE PARÂMETROS DE DATA
// ========================================
// Obter mês e ano da URL ou usar valores atuais como padrão
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');  // n = mês sem zero à esquerda
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');  // Y = ano com 4 dígitos

// ========================================
// VALIDAÇÃO E CORREÇÃO DE DATAS
// ========================================
// Validação de mês: se for inválido, ajustar e corrigir ano
if ($mes < 1) {          // Se mês menor que 1 (janeiro)
    $mes = 12;           // Vai para dezembro
    $ano--;              // Do ano anterior
} elseif ($mes > 12) {   // Se mês maior que 12 (dezembro)
    $mes = 1;            // Vai para janeiro
    $ano++;              // Do próximo ano
}

// ========================================
// CÁLCULOS DO CALENDÁRIO
// ========================================
// Obter informações necessárias para montar o calendário

// mktime() cria timestamp para o primeiro dia do mês
$primeiro_dia = mktime(0, 0, 0, $mes, 1, $ano);

// date('t') retorna número de dias no mês (28, 29, 30 ou 31)
$dias_no_mes = date('t', $primeiro_dia);

// date('w') retorna dia da semana do primeiro dia (0=domingo, 1=segunda, etc.)
$dia_semana_inicio = date('w', $primeiro_dia);

// ========================================
// CÁLCULO DE NAVEGAÇÃO (MÊS ANTERIOR/PRÓXIMO)
// ========================================
// Calcular mês anterior
$mes_anterior = $mes - 1;
$ano_anterior = $ano;
if ($mes_anterior < 1) {    // Se passou de janeiro
    $mes_anterior = 12;     // Vai para dezembro
    $ano_anterior--;        // Do ano anterior
}

// Calcular próximo mês
$mes_proximo = $mes + 1;
$ano_proximo = $ano;
if ($mes_proximo > 12) {    // Se passou de dezembro
    $mes_proximo = 1;       // Vai para janeiro
    $ano_proximo++;         // Do próximo ano
}

// ========================================
// CONFIGURAÇÕES DE EXIBIÇÃO
// ========================================
// Data de hoje para destacar no calendário
$hoje = date('Y-n-j');  // Formato: 2024-1-15 (sem zeros à esquerda)

// Array com nomes dos meses em português
$meses_pt = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// ========================================
// FUNÇÃO PRINCIPAL: CALCULAR RECORRÊNCIA DE EVENTOS
// ========================================
// Esta é a função mais importante do sistema de calendário
// Ela determina se um evento recorrente deve aparecer em uma data específica
function eventoAparecemData($data_inicial, $tipo_recorrencia, $data_verificar) {
    // Criar objetos DateTime para manipulação de datas
    $data_inicial_obj = new DateTime($data_inicial);      // Quando o evento foi criado
    $data_verificar_obj = new DateTime($data_verificar);  // Data que estamos verificando
    
    // REGRA FUNDAMENTAL: evento não pode aparecer antes de ser criado
    if ($data_verificar_obj < $data_inicial_obj) {
        return false;  // Não aparece
    }
    
    // Verificar tipo de recorrência
    switch ($tipo_recorrencia) {
        case 'nao':
            // SEM REPETIÇÃO: aparece apenas na data inicial
            // Exemplo: evento criado em 15/01 só aparece em 15/01
            return $data_inicial === $data_verificar;
            
        case 'diario':
            // REPETIÇÃO DIÁRIA: aparece todos os dias após a data inicial
            // Exemplo: evento criado em 15/01 aparece em 15/01, 16/01, 17/01...
            return true;
            
        case 'semanal':
            // REPETIÇÃO SEMANAL: aparece no mesmo dia da semana
            // Exemplo: evento criado numa segunda aparece todas as segundas
            // format('w') retorna: 0=domingo, 1=segunda, 2=terça... 6=sábado
            return $data_inicial_obj->format('w') === $data_verificar_obj->format('w');
            
        case 'mensal':
            // REPETIÇÃO MENSAL: aparece no mesmo dia do mês
            // Exemplo: evento criado no dia 15 aparece todo dia 15
            // format('j') retorna dia do mês sem zero à esquerda (1, 2... 31)
            return $data_inicial_obj->format('j') === $data_verificar_obj->format('j');
            
        case 'anual':
            // REPETIÇÃO ANUAL: aparece na mesma data todo ano
            // Exemplo: evento criado em 25/12 aparece todo 25/12
            // format('m-d') retorna mês-dia (12-25 para 25 de dezembro)
            return $data_inicial_obj->format('m-d') === $data_verificar_obj->format('m-d');
            
        default:
            // Tipo não reconhecido: não aparece
            return false;
    }
}

// ========================================
// FUNÇÃO: BUSCAR EVENTOS DO MÊS
// ========================================
// Esta função busca todos os eventos que devem aparecer no mês atual
function buscarEventosMes($conn, $usuario_id, $tipo_usuario, $mes, $ano) {
    $eventos = [];  // Array para armazenar eventos organizados por data
    
    // ========================================
    // BUSCAR PLANEJAMENTOS DE ESTUDOS (APENAS ESTUDANTES)
    // ========================================
    if ($tipo_usuario === 'estudante') {
        // SQL para buscar planejamentos do estudante logado
        $sql_plan = "SELECT *, 'planejamento' as tipo_evento FROM Planejamento_Estudos WHERE usuario_id = ? AND ativo = TRUE";
        $stmt_plan = $conn->prepare($sql_plan);
        
        if ($stmt_plan) {
            $stmt_plan->bind_param("i", $usuario_id);  // Bind do ID do usuário
            $stmt_plan->execute();
            $result_plan = $stmt_plan->get_result();
            
            // Loop através de todos os planejamentos do usuário
            while ($row = $result_plan->fetch_assoc()) {
                // Verificar cada dia do mês para ver se o evento deve aparecer
                for ($dia = 1; $dia <= date('t', mktime(0, 0, 0, $mes, 1, $ano)); $dia++) {
                    // Formatar data no padrão YYYY-MM-DD
                    $data_verificar = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                    
                    // Usar função de recorrência para verificar se aparece
                    if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data_verificar)) {
                        // Se não existe array para esta data, criar
                        if (!isset($eventos[$data_verificar])) {
                            $eventos[$data_verificar] = [];
                        }
                        // Adicionar evento à data
                        $eventos[$data_verificar][] = $row;
                    }
                }
            }
        }
    }
    
    // ========================================
    // BUSCAR AVISOS (TODOS OS USUÁRIOS VEEM)
    // ========================================
    // SQL com JOIN para pegar dados do autor do aviso
    $sql_avisos = "SELECT a.*, u.nome as autor_nome, u.tipo as autor_tipo, 'aviso' as tipo_evento
                   FROM Avisos a 
                   JOIN Usuarios u ON a.usuario_id = u.usuario_id 
                   WHERE a.ativo = TRUE";
    $stmt_avisos = $conn->prepare($sql_avisos);
    
    if ($stmt_avisos) {
        $stmt_avisos->execute();
        $result_avisos = $stmt_avisos->get_result();
        
        // Loop através de todos os avisos ativos
        while ($row = $result_avisos->fetch_assoc()) {
            // Verificar cada dia do mês para ver se o aviso deve aparecer
            for ($dia = 1; $dia <= date('t', mktime(0, 0, 0, $mes, 1, $ano)); $dia++) {
                $data_verificar = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                
                // Usar função de recorrência
                if (eventoAparecemData($row['data_inicial'], $row['tipo_recorrencia'], $data_verificar)) {
                    if (!isset($eventos[$data_verificar])) {
                        $eventos[$data_verificar] = [];
                    }
                    $eventos[$data_verificar][] = $row;
                }
            }
        }
    }
    
    return $eventos;  // Retornar array com todos os eventos organizados por data
}

// Executar busca de eventos para o mês atual
$eventos = buscarEventosMes($conn, $usuario_id, $tipo_usuario, $mes, $ano);

// ========================================
// FUNÇÕES UTILITÁRIAS PARA EXIBIÇÃO
// ========================================

// Função para contar quantos eventos tem em uma data
function contarEventos($data, $eventos) {
    return isset($eventos[$data]) ? count($eventos[$data]) : 0;
}

// Função para obter tipos de eventos em uma data (para indicadores visuais)
function getTiposEventos($data, $eventos) {
    if (!isset($eventos[$data])) return [];  // Se não tem eventos, retorna array vazio
    
    $tipos = [];
    foreach ($eventos[$data] as $evento) {
        // Determinar tipo do evento para CSS
        $tipo = $evento['tipo_evento'] === 'aviso' ? 'aviso_' . $evento['autor_tipo'] : $evento['tipo_evento'];
        
        // Adicionar tipo se ainda não estiver na lista
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
    <!-- ========================================
         CONFIGURAÇÕES DO CABEÇALHO HTML
         ======================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- CSS externo para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- CSS personalizado do calendário -->
    <link rel="stylesheet" href="CSS/calendario.css">
</head>
<body>
    <!-- ========================================
         CONTAINER PRINCIPAL DO CALENDÁRIO
         ======================================== -->
    <div class="container">
        
        <!-- ========================================
             SEÇÃO DO CALENDÁRIO (LADO ESQUERDO)
             ======================================== -->
        <div class="calendario-container">
            
            <!-- Cabeçalho com navegação e título do mês -->
            <div class="calendario-header">
                <div class="navegacao">
                    <!-- Botão para mês anterior -->
                    <button class="btn-nav" onclick="navegarMes(<?php echo $mes_anterior; ?>, <?php echo $ano_anterior; ?>)">
                        &#8249; <!-- Seta para esquerda -->
                    </button>
                    <!-- Botão para próximo mês -->
                    <button class="btn-nav" onclick="navegarMes(<?php echo $mes_proximo; ?>, <?php echo $ano_proximo; ?>)">
                        &#8250; <!-- Seta para direita -->
                    </button>
                </div>
                <!-- Título com mês e ano atual -->
                <div class="mes-ano">
                    <?php echo $meses_pt[$mes] . ' ' . $ano; ?>
                </div>
            </div>

            <!-- Grid do calendário -->
            <div class="calendario-grid">
                
                <!-- Cabeçalho com dias da semana -->
                <div class="dias-semana">
                    <div class="dia-semana">Dom</div>
                    <div class="dia-semana">Seg</div>
                    <div class="dia-semana">Ter</div>
                    <div class="dia-semana">Qua</div>
                    <div class="dia-semana">Qui</div>
                    <div class="dia-semana">Sex</div>
                    <div class="dia-semana">Sáb</div>
                </div>

                <!-- Dias do mês -->
                <div class="dias-mes">
                    <?php
                    // ========================================
                    // GERAÇÃO DOS DIAS DO CALENDÁRIO
                    // ========================================
                    
                    // Espaços vazios antes do primeiro dia
                    // Se o mês começa numa quarta (3), precisa de 3 espaços vazios
                    for ($i = 0; $i < $dia_semana_inicio; $i++) {
                        echo '<div class="dia"></div>';  // Div vazia
                    }

                    // Loop através de todos os dias do mês
                    for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
                        // Formatar datas para comparação
                        $data_completa = $ano . '-' . sprintf('%02d', $mes) . '-' . sprintf('%02d', $dia);  // 2024-01-15
                        $data_simples = $ano . '-' . $mes . '-' . $dia;  // 2024-1-15
                        
                        // Array de classes CSS para o dia
                        $classes = ['dia'];
                        
                        // Verificar se é hoje (destacar visualmente)
                        if ($data_simples === $hoje) {
                            $classes[] = 'hoje';
                        }
                        
                        // Verificar se tem eventos neste dia
                        $num_eventos = contarEventos($data_completa, $eventos);
                        $tipos_eventos = getTiposEventos($data_completa, $eventos);
                        
                        if ($num_eventos > 0) {
                            $classes[] = 'com-eventos';  // Classe para dias com eventos
                        }
                        
                        // Gerar HTML do dia
                        echo '<div class="' . implode(' ', $classes) . '" onclick="selecionarDia(' . $dia . ', ' . $mes . ', ' . $ano . ')" data-dia="' . $dia . '" data-data="' . $data_completa . '">';
                        echo $dia;  // Número do dia
                        
                        // Se tem eventos, mostrar contador e indicadores
                        if ($num_eventos > 0) {
                            echo '<div class="contador-eventos">' . $num_eventos . '</div>';
                            echo '<div class="indicadores-tipo">';
                            foreach ($tipos_eventos as $tipo) {
                                echo '<div class="indicador ' . $tipo . '"></div>';  // Indicador colorido por tipo
                            }
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- ========================================
             PAINEL LATERAL (LADO DIREITO)
             ======================================== -->
        <div class="painel-lateral">
            <div class="painel-header">
                <h3>📚 Calendário de Estudos</h3>
            </div>
            <div class="painel-content">
                <!-- Container para alertas/mensagens -->
                <div id="alert-container"></div>
                
                <!-- Informação inicial (antes de selecionar um dia) -->
                <div id="info-inicial">
                    <p style="text-align: center; color: #666; margin-top: 50px;">
                        Clique em um dia do calendário para ver os eventos e 
                        <?php echo $tipo_usuario === 'estudante' ? 'adicionar planejamentos' : 'criar avisos'; ?>.
                    </p>
                </div>

                <!-- Detalhes do dia selecionado (inicialmente oculto) -->
                <div id="dia-detalhado" class="dia-detalhado">
                    <h4 id="titulo-dia"></h4>
                    
                    <!-- Botão para adicionar evento (varia por tipo de usuário) -->
                    <?php if ($tipo_usuario === 'estudante'): ?>
                        <button class="btn-adicionar" onclick="abrirModal('planejamento')">
                            ➕ Adicionar Planejamento
                        </button>
                    <?php else: ?>
                        <button class="btn-adicionar" onclick="abrirModal('aviso')">
                            ➕ Criar Aviso
                        </button>
                    <?php endif; ?>
                    
                    <!-- Container onde os eventos do dia serão exibidos -->
                    <div id="eventos-container" class="eventos-lista"></div>
                </div>

                <!-- Legenda de cores -->
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

    <!-- ========================================
         MODAL PARA PLANEJAMENTO (ESTUDANTES)
         ======================================== -->
    <div id="modal-planejamento" class="modal">
        <div class="modal-content">
            <h3>Adicionar Planejamento de Estudos</h3>
            <form id="form-planejamento">
                <!-- Campo para nome da atividade -->
                <div class="form-group">
                    <label for="atividade">Atividade:</label>
                    <input type="text" id="atividade" required placeholder="Ex: Estudar Matemática - Álgebra">
                </div>
                
                <!-- Campo para horário de início -->
                <div class="form-group">
                    <label for="horario-inicio">Horário de Início:</label>
                    <input type="time" id="horario-inicio" required>
                </div>
                
                <!-- Campo para duração em minutos -->
                <div class="form-group">
                    <label for="duracao">Duração (minutos):</label>
                    <input type="number" id="duracao" min="15" max="480" step="15" value="60">
                </div>
                
                <!-- Seção de recorrência -->
                <div class="recorrencia-section">
                    <h4><i class="fas fa-repeat"></i> Repetir Evento</h4>
                    <div class="radio-group">
                        <!-- Opções de repetição -->
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
                    <!-- Texto informativo sobre a recorrência selecionada -->
                    <div class="info-recorrencia">
                        <i class="fas fa-info-circle"></i> 
                        <span id="info-texto">O evento será criado apenas para o dia selecionado.</span>
                    </div>
                </div>
                
                <!-- Botões de ação -->
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================
         MODAL PARA AVISO (PROFESSORES/COORDENADORES)
         ======================================== -->
    <div id="modal-aviso" class="modal">
        <div class="modal-content">
            <h3>Criar Aviso</h3>
            <form id="form-aviso">
                <!-- Campo para tipo de aviso -->
                <div class="form-group">
                    <label for="tipo-aviso">Tipo de Aviso:</label>
                    <select id="tipo-aviso" required>
                        <option value="aviso">Aviso Geral</option>
                        <option value="oportunidade">Oportunidade</option>
                    </select>
                </div>
                
                <!-- Campo para título -->
                <div class="form-group">
                    <label for="titulo-aviso">Título:</label>
                    <input type="text" id="titulo-aviso" required placeholder="Ex: Prova de Matemática">
                </div>
                
                <!-- Campo para descrição -->
                <div class="form-group">
                    <label for="descricao-aviso">Descrição:</label>
                    <textarea id="descricao-aviso" rows="4" required placeholder="Detalhes do aviso..."></textarea>
                </div>
                
                <!-- Seção de recorrência para avisos -->
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
                    <!-- Texto informativo -->
                    <div class="info-recorrencia">
                        <i class="fas fa-info-circle"></i> 
                        <span id="info-texto-aviso">O aviso será criado apenas para o dia selecionado.</span>
                    </div>
                </div>
                
                <!-- Botões de ação -->
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Aviso</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================
         VARIÁVEIS PHP PARA JAVASCRIPT
         ======================================== -->
    <script>
        // Passar dados do PHP para JavaScript
        const tipoUsuario = '<?php echo $tipo_usuario; ?>';
        const ajaxUrl = 'calendario-ajax.php'; // URL para o arquivo de processamento AJAX
        
        // Debug: verificar se as variáveis estão sendo passadas corretamente
        console.log('Tipo de usuário:', tipoUsuario);
        console.log('URL para AJAX:', ajaxUrl);
        console.log('Página carregada com sucesso');
    </script>
    
    <!-- Incluir arquivo JavaScript com toda a lógica do frontend -->
    <script src="JS/calendario.js"></script>
</body>
</html>

<?php
// ========================================
// PONTOS DE EXPANSÃO FUTURA:
// ========================================

/* 
1. VISUALIZAÇÕES ALTERNATIVAS:
   - Vista semanal detalhada
   - Vista de agenda/lista
   - Vista anual compacta
   - Vista de timeline

2. FILTROS E PESQUISA:
   - Filtrar por tipo de evento
   - Pesquisar por palavra-chave
   - Filtrar por autor
   - Filtrar por data

3. EXPORTAÇÃO E IMPRESSÃO:
   - Exportar para PDF
   - Imprimir calendário
   - Exportar para Excel
   - Sincronizar com Google Calendar

4. PERSONALIZAÇÃO:
   - Temas de cores
   - Layout customizável
   - Configurações de usuário
   - Preferências de exibição

5. RECURSOS AVANÇADOS:
   - Arrastar e soltar eventos
   - Redimensionar eventos
   - Visualização de conflitos
   - Sugestões inteligentes

6. INTEGRAÇÃO SOCIAL:
   - Compartilhar eventos
   - Comentários em eventos
   - Menções de usuários
   - Feed de atividades

7. MOBILE E PWA:
   - App mobile nativo
   - Progressive Web App
   - Notificações push
   - Modo offline

8. ANALYTICS E RELATÓRIOS:
   - Dashboard de estatísticas
   - Relatórios de produtividade
   - Análise de padrões
   - Métricas de uso

9. AUTOMAÇÃO:
   - Lembretes automáticos
   - Criação de eventos recorrentes
   - Integração com outros sistemas
   - Workflows automatizados

10. ACESSIBILIDADE:
    - Suporte a leitores de tela
    - Navegação por teclado
    - Alto contraste
    - Múltiplos idiomas
*/
?>
