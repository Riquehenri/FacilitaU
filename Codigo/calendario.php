<?php
session_start(); // Inicia a sessão para acessar variáveis de sessão

$page_title = "Calendário - Facilita U";
include 'config.php'; // Inclui configurações como conexão com o banco de dados
include 'header.php'; // Inclui o cabeçalho da página

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php"); // Redireciona para login se não estiver logado
    exit();
}

$usuario_id = $_SESSION['usuario_id'];



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

// Data atual
$hoje = date('Y-n-j');

// Nomes dos meses em português
$meses_pt = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// Simulação de dados de eventos (em produção, viria do banco de dados)
$eventos = [
    $ano . '-' . sprintf('%02d', $mes) . '-05' => [
        ['tipo' => 'estudo', 'titulo' => 'Matemática - Álgebra', 'hora' => '08:00', 'duracao' => 120, 'professor' => 'Prof. Silva'],
        ['tipo' => 'aviso_professor', 'titulo' => 'Prova de História na próxima semana', 'hora' => '14:00', 'professor' => 'Prof. Santos'],
    ],
    $ano . '-' . sprintf('%02d', $mes) . '-12' => [
        ['tipo' => 'estudo', 'titulo' => 'Física - Mecânica', 'hora' => '09:00', 'duracao' => 90, 'professor' => 'Prof. Costa'],
        ['tipo' => 'estudo', 'titulo' => 'Português - Redação', 'hora' => '15:00', 'duracao' => 60, 'professor' => 'Prof. Lima'],
        ['tipo' => 'aviso_coordenador', 'titulo' => 'Reunião de pais - 15/12', 'hora' => '10:00', 'coordenador' => 'Coord. Maria'],
    ],
    $ano . '-' . sprintf('%02d', $mes) . '-18' => [
        ['tipo' => 'estudo', 'titulo' => 'Química - Orgânica', 'hora' => '07:30', 'duracao' => 90, 'professor' => 'Prof. Oliveira'],
        ['tipo' => 'estudo', 'titulo' => 'Inglês - Grammar', 'hora' => '16:00', 'duracao' => 45, 'professor' => 'Prof. Johnson'],
    ],
    $ano . '-' . sprintf('%02d', $mes) . '-25' => [
        ['tipo' => 'aviso_professor', 'titulo' => 'Entrega do projeto de Biologia', 'hora' => '08:00', 'professor' => 'Prof. Ferreira'],
        ['tipo' => 'estudo', 'titulo' => 'Geografia - Cartografia', 'hora' => '13:30', 'duracao' => 75, 'professor' => 'Prof. Rocha'],
    ],
];

// Função para contar eventos por dia
function contarEventos($data, $eventos) {
    return isset($eventos[$data]) ? count($eventos[$data]) : 0;
}

// Função para obter tipos de eventos do dia
function getTiposEventos($data, $eventos) {
    if (!isset($eventos[$data])) return [];
    $tipos = [];
    foreach ($eventos[$data] as $evento) {
        if (!in_array($evento['tipo'], $tipos)) {
            $tipos[] = $evento['tipo'];
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
    <title>Calendário de Estudos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }

        .calendario-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .calendario-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
        }

        .mes-ano {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .navegacao {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: absolute;
            top: 50%;
            left: 20px;
            right: 20px;
            transform: translateY(-50%);
        }

        .btn-nav {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-nav:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .calendario-grid {
            padding: 20px;
        }

        .dias-semana {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-bottom: 10px;
        }

        .dia-semana {
            text-align: center;
            font-weight: 600;
            color: #666;
            padding: 10px 5px;
            font-size: 14px;
        }

        .dias-mes {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .dia {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            border: 2px solid transparent;
            min-height: 60px;
        }

        .dia:hover {
            background: #f0f0f0;
            transform: scale(1.05);
        }

        .dia.hoje {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
        }

        .dia.selecionado {
            border-color: #ff6b6b;
            background: #fff5f5;
        }

        .dia.com-eventos {
            background: #e8f5e8;
        }

        .contador-eventos {
            font-size: 10px;
            background: #4ecdc4;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 2px;
            right: 2px;
        }

        .indicadores-tipo {
            display: flex;
            gap: 2px;
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
        }

        .indicador {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .indicador.estudo { background: #4ecdc4; }
        .indicador.aviso_professor { background: #ff9f43; }
        .indicador.aviso_coordenador { background: #ee5a52; }

        .painel-lateral {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: fit-content;
        }

        .painel-header {
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .painel-content {
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
        }

        .dia-detalhado {
            display: none;
        }

        .dia-detalhado.ativo {
            display: block;
        }

        .horarios-grid {
            display: grid;
            gap: 10px;
        }

        .horario-slot {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 8px;
            min-height: 50px;
            position: relative;
        }

        .horario-slot.ocupado {
            background: #f8f9fa;
            border-color: #dee2e6;
        }

        .horario-label {
            font-weight: 600;
            color: #666;
            width: 60px;
            font-size: 12px;
        }

        .evento-item {
            flex: 1;
            margin-left: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .evento-item.estudo {
            background: #e8f5e8;
            border-left: 4px solid #4ecdc4;
        }

        .evento-item.aviso_professor {
            background: #fff3e0;
            border-left: 4px solid #ff9f43;
        }

        .evento-item.aviso_coordenador {
            background: #ffebee;
            border-left: 4px solid #ee5a52;
        }

        .evento-titulo {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .evento-info {
            font-size: 12px;
            color: #666;
        }

        .btn-adicionar {
            background: #4ecdc4;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .btn-adicionar:hover {
            background: #44a08d;
            transform: translateY(-1px);
        }

        .btn-remover {
            background: #ee5a52;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
            position: absolute;
            top: 5px;
            right: 5px;
        }

        .legenda {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .legenda-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .legenda-cor {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #4ecdc4;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .painel-lateral {
                order: -1;
            }
        }
    </style>
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
                <h3>📚 Planejamento de Estudos</h3>
            </div>
            <div class="painel-content">
                <div id="info-inicial">
                    <p style="text-align: center; color: #666; margin-top: 50px;">
                        Clique em um dia do calendário para ver os compromissos e adicionar novos eventos.
                    </p>
                </div>

                <div id="dia-detalhado" class="dia-detalhado">
                    <h4 id="titulo-dia"></h4>
                    <button class="btn-adicionar" onclick="abrirModal()" style="margin-bottom: 20px;">
                        ➕ Adicionar Evento
                    </button>
                    <div id="horarios-container" class="horarios-grid"></div>
                </div>

                <div class="legenda">
                    <h5 style="margin-bottom: 10px;">Legenda:</h5>
                    <div class="legenda-item">
                        <div class="legenda-cor" style="background: #4ecdc4;"></div>
                        <span>Sessão de Estudo</span>
                    </div>
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

    <!-- Modal para adicionar evento -->
    <div id="modal-evento" class="modal">
        <div class="modal-content">
            <h3>Adicionar Novo Evento</h3>
            <form id="form-evento">
                <div class="form-group">
                    <label for="tipo-evento">Tipo de Evento:</label>
                    <select id="tipo-evento" required>
                        <option value="estudo">Sessão de Estudo</option>
                        <option value="aviso_professor">Aviso de Professor</option>
                        <option value="aviso_coordenador">Aviso de Coordenador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="titulo-evento">Título:</label>
                    <input type="text" id="titulo-evento" required placeholder="Ex: Matemática - Álgebra">
                </div>
                <div class="form-group">
                    <label for="hora-evento">Horário:</label>
                    <input type="time" id="hora-evento" required>
                </div>
                <div class="form-group" id="duracao-group">
                    <label for="duracao-evento">Duração (minutos):</label>
                    <input type="number" id="duracao-evento" min="15" max="480" step="15" value="60">
                </div>
                <div class="form-group">
                    <label for="professor-evento">Professor/Coordenador:</label>
                    <input type="text" id="professor-evento" placeholder="Nome do responsável">
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let diaSelecionado = null;
        let dataSelecionada = null;
        
        // Dados de eventos (simulando banco de dados)
        let eventos = <?php echo json_encode($eventos); ?>;

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

            // Gerar grade de horários
            gerarGradeHorarios(data);
        }

        function gerarGradeHorarios(data) {
            const container = document.getElementById('horarios-container');
            container.innerHTML = '';

            const eventosData = eventos[data] || [];
            
            // Horários de 7:00 às 22:00
            for (let hora = 7; hora <= 22; hora++) {
                for (let minuto = 0; minuto < 60; minuto += 30) {
                    const horarioStr = `${hora.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}`;
                    const slot = document.createElement('div');
                    slot.className = 'horario-slot';
                    
                    // Verificar se há evento neste horário
                    const eventoNoHorario = eventosData.find(evento => evento.hora === horarioStr);
                    
                    if (eventoNoHorario) {
                        slot.classList.add('ocupado');
                        slot.innerHTML = `
                            <div class="horario-label">${horarioStr}</div>
                            <div class="evento-item ${eventoNoHorario.tipo}">
                                <div class="evento-titulo">${eventoNoHorario.titulo}</div>
                                <div class="evento-info">
                                    ${eventoNoHorario.duracao ? `${eventoNoHorario.duracao} min` : ''} 
                                    ${eventoNoHorario.professor || eventoNoHorario.coordenador || ''}
                                </div>
                                <button class="btn-remover" onclick="removerEvento('${data}', '${horarioStr}')">✕</button>
                            </div>
                        `;
                    } else {
                        slot.innerHTML = `
                            <div class="horario-label">${horarioStr}</div>
                            <button class="btn-adicionar" onclick="abrirModal('${horarioStr}')">+ Adicionar</button>
                        `;
                    }
                    
                    container.appendChild(slot);
                }
            }
        }

        function abrirModal(horario = '') {
            document.getElementById('modal-evento').style.display = 'block';
            if (horario) {
                document.getElementById('hora-evento').value = horario;
            }
            
            // Mostrar/ocultar campo duração baseado no tipo
            document.getElementById('tipo-evento').addEventListener('change', function() {
                const duracaoGroup = document.getElementById('duracao-group');
                duracaoGroup.style.display = this.value === 'estudo' ? 'block' : 'none';
            });
        }

        function fecharModal() {
            document.getElementById('modal-evento').style.display = 'none';
            document.getElementById('form-evento').reset();
        }

        function removerEvento(data, horario) {
            if (confirm('Tem certeza que deseja remover este evento?')) {
                if (eventos[data]) {
                    eventos[data] = eventos[data].filter(evento => evento.hora !== horario);
                    if (eventos[data].length === 0) {
                        delete eventos[data];
                    }
                }
                
                // Atualizar visualização
                gerarGradeHorarios(data);
                atualizarCalendario();
                
                // Aqui você faria a requisição AJAX para remover do banco de dados
                console.log('Evento removido:', data, horario);
            }
        }

        // Submissão do formulário
        document.getElementById('form-evento').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const novoEvento = {
                tipo: document.getElementById('tipo-evento').value,
                titulo: document.getElementById('titulo-evento').value,
                hora: document.getElementById('hora-evento').value,
                duracao: document.getElementById('tipo-evento').value === 'estudo' ? 
                        parseInt(document.getElementById('duracao-evento').value) : null,
                professor: document.getElementById('professor-evento').value || null
            };

            // Adicionar evento aos dados
            if (!eventos[dataSelecionada]) {
                eventos[dataSelecionada] = [];
            }
            eventos[dataSelecionada].push(novoEvento);

            // Atualizar visualização
            gerarGradeHorarios(dataSelecionada);
            atualizarCalendario();
            fecharModal();

            // Aqui você faria a requisição AJAX para salvar no banco de dados
            console.log('Novo evento adicionado:', novoEvento);
        });

        function atualizarCalendario() {
            // Atualizar contadores e indicadores no calendário
            document.querySelectorAll('.dia[data-data]').forEach(dia => {
                const data = dia.dataset.data;
                const eventosData = eventos[data] || [];
                
                // Remover indicadores existentes
                const contadorExistente = dia.querySelector('.contador-eventos');
                const indicadoresExistentes = dia.querySelector('.indicadores-tipo');
                if (contadorExistente) contadorExistente.remove();
                if (indicadoresExistentes) indicadoresExistentes.remove();
                
                // Adicionar novos indicadores se houver eventos
                if (eventosData.length > 0) {
                    dia.classList.add('com-eventos');
                    
                    const contador = document.createElement('div');
                    contador.className = 'contador-eventos';
                    contador.textContent = eventosData.length;
                    dia.appendChild(contador);
                    
                    const tipos = [...new Set(eventosData.map(e => e.tipo))];
                    const indicadores = document.createElement('div');
                    indicadores.className = 'indicadores-tipo';
                    tipos.forEach(tipo => {
                        const indicador = document.createElement('div');
                        indicador.className = `indicador ${tipo}`;
                        indicadores.appendChild(indicador);
                    });
                    dia.appendChild(indicadores);
                } else {
                    dia.classList.remove('com-eventos');
                }
            });
        }

        // Fechar modal clicando fora
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('modal-evento');
            if (e.target === modal) {
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