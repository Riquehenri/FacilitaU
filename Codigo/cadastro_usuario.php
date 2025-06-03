<?php
// ========================================
// SISTEMA DE CADASTRO DE USUÁRIOS - FACILITA U
// ========================================
// Este arquivo é responsável por:
// 1. Exibir o formulário de cadastro
// 2. Processar os dados enviados pelo usuário
// 3. Validar informações (idade, email único)
// 4. Inserir novo usuário no banco de dados
// 5. Redirecionar para login após sucesso

// ========================================
// CONFIGURAÇÕES INICIAIS DA PÁGINA
// ========================================
$page_title = "Cadastro de Usuário"; // Define o título que aparece na aba do navegador
include 'config.php';                 // Inclui arquivo com configurações do banco de dados
include 'header.php';                 // Inclui cabeçalho padrão do site (menu, CSS, etc.)

// ========================================
// PROCESSAMENTO DO FORMULÁRIO (BACKEND)
// ========================================
// Esta seção só executa quando o usuário clica em "Cadastrar"
// $_SERVER['REQUEST_METHOD'] contém o método HTTP usado (GET, POST, etc.)
// POST = método usado para enviar dados de formulários (mais seguro que GET)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ========================================
    // COLETA DE DADOS DO FORMULÁRIO
    // ========================================
    // $_POST = array global que contém todos os dados enviados pelo formulário
    // Cada campo do formulário vira uma chave neste array
    
    $email = $_POST['email'];                    // Email digitado pelo usuário
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha para segurança
    $nome = $_POST['nome'];                      // Nome completo do usuário
    $tipo = $_POST['tipo'];                      // Tipo: estudante, professor ou coordenador
    $data_nascimento = $_POST['data_nascimento']; // Data de nascimento
    $telefone = $_POST['telefone'];              // Número de telefone
    $curso_id = $_POST['curso_id'];              // ID do curso selecionado
    
    // ========================================
    // PONTO DE EXPANSÃO: ADICIONAR NOVOS CAMPOS
    // ========================================
    /* 
    Para adicionar um novo campo (ex: CPF), você faria:
    
    1. Adicionar no HTML (lá embaixo):
    <div class="input-group">
        <label for="cpf">CPF</label>
        <i class="fas fa-id-card input-icon"></i>
        <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" required>
    </div>
    
    2. Coletar o dado aqui:
    $cpf = $_POST['cpf'];
    
    3. Adicionar na validação (se necessário):
    if (!validarCPF($cpf)) {
        $erro = "CPF inválido!";
    }
    
    4. Incluir no SQL de inserção:
    - Adicionar 'cpf' na lista de campos
    - Adicionar '?' na lista de valores
    - Adicionar 's' no bind_param (para string)
    - Adicionar $cpf na lista de variáveis
    */
    
    // ========================================
    // VALIDAÇÃO DE IDADE MÍNIMA
    // ========================================
    // Calcula qual seria a data de nascimento de alguém com exatamente 16 anos hoje
    // strtotime('-16 years') = timestamp de 16 anos atrás
    // date('Y-m-d', timestamp) = converte timestamp para formato de data
    $data_minima = date('Y-m-d', strtotime('-16 years'));
    
    // Se a data de nascimento é MAIOR que a data mínima, significa que a pessoa tem MENOS de 16 anos
    // Exemplo: se hoje é 2024 e data_minima é 2008-01-01
    // Se usuário nasceu em 2010-01-01, então 2010-01-01 > 2008-01-01 = true (menor de idade)
    if ($data_nascimento > $data_minima) {
        $erro = "Você deve ter pelo menos 16 anos para se cadastrar.";
    }
    
    // ========================================
    // PONTO DE EXPANSÃO: VALIDAÇÕES ADICIONAIS
    // ========================================
    /*
    Aqui você pode adicionar mais validações:
    
    // Validar formato de telefone
    if (!preg_match('/^$$\d{2}$$ 9\d{4}-\d{4}$/', $telefone)) {
        $erro = "Formato de telefone inválido!";
    }
    
    // Validar força da senha
    if (strlen($_POST['senha']) < 8) {
        $erro = "Senha deve ter pelo menos 8 caracteres!";
    }
    
    // Validar email institucional
    if (!strpos($email, '@instituicao.edu.br')) {
        $erro = "Use apenas email institucional!";
    }
    
    // Validar CPF (se adicionado)
    if (!validarCPF($cpf)) {
        $erro = "CPF inválido!";
    }
    */

    // ========================================
    // VERIFICAÇÃO DE EMAIL DUPLICADO
    // ========================================
    // Só continua se não houve erro de idade
    if (!isset($erro)) {
        
        // Prepared Statement = forma segura de fazer consultas SQL
        // Previne SQL Injection (ataque onde hacker injeta código SQL malicioso)
        $sql_check = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check); // Prepara a consulta
        
        // bind_param = associa valores aos placeholders (?)
        // "s" = string (tipo do parâmetro)
        // $email = valor a ser inserido no lugar do ?
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();                    // Executa a consulta
        $result_check = $stmt_check->get_result(); // Obtém o resultado
        
        // num_rows = número de linhas retornadas
        // Se for 0, significa que o email não existe no banco
        if ($result_check->num_rows == 0) {
            
            // ========================================
            // INSERÇÃO DO NOVO USUÁRIO
            // ========================================
            // SQL para inserir novo registro na tabela Usuarios
            // ? = placeholders que serão substituídos pelos valores reais
            $sql = "INSERT INTO Usuarios (email, senha, tipo, nome, data_nascimento, telefone, curso_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql); // Prepara a consulta de inserção
            
            // bind_param com 7 parâmetros:
            // "ssssssi" = tipos dos parâmetros (s=string, i=integer)
            // s = email (string)
            // s = senha (string)
            // s = tipo (string)
            // s = nome (string)
            // s = data_nascimento (string)
            // s = telefone (string)
            // i = curso_id (integer)
            $stmt->bind_param("ssssssi", $email, $senha, $tipo, $nome, $data_nascimento, $telefone, $curso_id);
            
            // ========================================
            // PONTO DE EXPANSÃO: INSERÇÃO COM NOVOS CAMPOS
            // ========================================
            /*
            Se você adicionar um campo CPF, o SQL ficaria:
            
            $sql = "INSERT INTO Usuarios (email, senha, tipo, nome, data_nascimento, telefone, curso_id, cpf) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt->bind_param("ssssssiss", $email, $senha, $tipo, $nome, $data_nascimento, $telefone, $curso_id, $cpf);
            
            Note que:
            - Adicionou 'cpf' na lista de campos
            - Adicionou mais um '?' na lista de valores
            - Adicionou 's' no bind_param (para string)
            - Adicionou $cpf na lista de variáveis
            */
            
            // Tentar executar a inserção
            if ($stmt->execute()) {
                // ========================================
                // SUCESSO: REDIRECIONAR PARA LOGIN
                // ========================================
                // header('Location: ...') = redireciona o navegador para outra página
                // exit() = para a execução do script (importante após redirecionamento)
                header('Location: login_usuario.php');
                exit();
                
                // ========================================
                // PONTO DE EXPANSÃO: AÇÕES APÓS CADASTRO
                // ========================================
                /*
                Aqui você pode adicionar:
                
                // Enviar email de boas-vindas
                enviarEmailBoasVindas($email, $nome);
                
                // Criar registro de auditoria
                registrarAcao($conn, 'CADASTRO_USUARIO', $email);
                
                // Enviar notificação para administradores
                notificarAdmins($nome, $email, $tipo);
                
                // Criar configurações padrão do usuário
                criarConfiguracoesPadrao($conn, $usuario_id);
                */
                
            } else {
                // Erro ao inserir no banco de dados
                // $conn->error = mensagem de erro do MySQL
                $erro = "Erro ao cadastrar: " . $conn->error;
            }
            
            $stmt->close(); // Libera recursos da consulta de inserção
            
        } else {
            // Email já existe no banco
            $erro = "Erro! O e-mail já está cadastrado.";
        }
        
        $stmt_check->close(); // Libera recursos da consulta de verificação
    }
}

// ========================================
// BUSCAR LISTA DE CURSOS PARA O FORMULÁRIO
// ========================================
// Esta seção sempre executa (independente de POST)
// Busca todos os cursos disponíveis para popular o select do formulário

$cursos = []; // Array vazio para armazenar os cursos

// SQL para buscar cursos ordenados alfabeticamente
$sql_cursos = "SELECT curso_id, nome FROM Cursos ORDER BY nome";
$result_cursos = $conn->query($sql_cursos); // Executa consulta diretamente (sem prepared statement pois não há parâmetros)

// Verifica se encontrou cursos
if ($result_cursos->num_rows > 0) {
    // Loop através de cada curso encontrado
    while ($row = $result_cursos->fetch_assoc()) {
        // fetch_assoc() = retorna próxima linha como array associativo
        // ['curso_id' => 1, 'nome' => 'Ciência da Computação']
        $cursos[] = $row; // Adiciona curso ao array
    }
}

// ========================================
// PONTO DE EXPANSÃO: BUSCAR OUTROS DADOS
// ========================================
/*
Você pode buscar outros dados para popular selects:

// Buscar estados para select de estado
$estados = [];
$sql_estados = "SELECT estado_id, nome FROM Estados ORDER BY nome";
$result_estados = $conn->query($sql_estados);
if ($result_estados->num_rows > 0) {
    while ($row = $result_estados->fetch_assoc()) {
        $estados[] = $row;
    }
}

// Buscar cidades baseadas no estado (via AJAX)
// Buscar áreas de interesse
// Buscar níveis de escolaridade
// etc.
*/

$conn->close(); // Fecha conexão com banco de dados (boa prática)
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- ========================================
         CONFIGURAÇÕES DO CABEÇALHO HTML
         ======================================== -->
    <meta charset="UTF-8"> <!-- Codificação de caracteres (suporte a acentos) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsividade mobile -->
    <title>FacilitaU - Cadastro</title> <!-- Título da aba do navegador -->
    
    <!-- CSS externo para estilos da página -->
    <link rel="stylesheet" href="CSS/Login.css">
    
    <!-- FontAwesome para ícones (envelope, cadeado, etc.) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- ========================================
         CONTAINER PRINCIPAL DA PÁGINA
         ======================================== -->
    <div class="container">
        
        <!-- ========================================
             SEÇÃO INFORMATIVA (LADO ESQUERDO)
             ======================================== -->
        <div class="info-section">
            <h1 class="logo">FacilitaU</h1>
            <h2>Bem-vindo ao FacilitaU</h2>
            <p>A plataforma que simplifica a vida acadêmica.</p>
            
            <!-- Box para usuários que já têm conta -->
            <div class="login-box">
                <p>Já possui uma conta?</p>
                <a href="login_usuario.php" class="btn btn-secondary">Acessar conta</a>
            </div>
        </div>

        <!-- ========================================
             SEÇÃO DO FORMULÁRIO (LADO DIREITO)
             ======================================== -->
        <div class="form-section">
            <div id="register-form">
                <h2>Crie sua conta</h2>
                
                <!-- ========================================
                     EXIBIÇÃO DE ERROS
                     ======================================== -->
                <!-- Só mostra se a variável $erro foi definida -->
                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <!-- ========================================
                     FORMULÁRIO DE CADASTRO
                     ======================================== -->
                <!-- action = para onde os dados serão enviados (mesmo arquivo) -->
                <!-- method = como os dados serão enviados (POST = seguro) -->
                <!-- onsubmit = função JavaScript executada antes do envio -->
                <form action="cadastro_usuario.php" method="POST" onsubmit="return validarFormulario()">
                    
                    <!-- ========================================
                         CAMPO: NOME COMPLETO
                         ======================================== -->
                    <div class="input-group">
                        <label for="nome">Nome Completo</label>
                        <i class="fas fa-user input-icon"></i> <!-- Ícone de usuário -->
                        <!-- name = nome do campo (usado no $_POST) -->
                        <!-- id = identificador único (usado pelo JavaScript) -->
                        <!-- required = campo obrigatório (validação HTML5) -->
                        <input type="text" name="nome" id="nome" required>
                    </div>

                    <!-- ========================================
                         CAMPO: EMAIL INSTITUCIONAL
                         ======================================== -->
                    <div class="input-group">
                        <label for="email">E-mail institucional</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <!-- type="email" = validação automática de formato de email -->
                        <input type="email" name="email" id="email" placeholder="seu.email@instituicao.edu.br" required>
                    </div>

                    <!-- ========================================
                         CAMPO: DATA DE NASCIMENTO
                         ======================================== -->
                    <div class="input-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <i class="fas fa-calendar input-icon"></i>
                        <!-- type="date" = seletor de data nativo do navegador -->
                        <!-- max = data máxima permitida (16 anos atrás) -->
                        <input type="date" name="data_nascimento" id="data_nascimento"
                               max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>" required>
                    </div>

                    <!-- ========================================
                         CAMPO: TELEFONE
                         ======================================== -->
                    <div class="input-group">
                        <label for="telefone">Telefone (com DDD)</label>
                        <i class="fas fa-phone input-icon"></i>
                        <!-- type="tel" = campo de telefone -->
                        <!-- oninput = função JavaScript executada a cada digitação -->
                        <input type="tel" name="telefone" id="telefone" placeholder="(XX) 9XXXX-XXXX" 
                               oninput="mascaraTelefone(event)" required>
                    </div>

                    <!-- ========================================
                         CAMPO: SENHA
                         ======================================== -->
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <!-- type="password" = campo de senha (caracteres ocultos) -->
                        <input type="password" name="senha" id="senha" placeholder="********" required>
                        <!-- Ícone do olho para mostrar/ocultar senha -->
                        <i class="fas fa-eye password-toggle" id="toggle-password"></i>
                    </div>

                    <!-- ========================================
                         CAMPO: CONFIRMAR SENHA
                         ======================================== -->
                    <!-- IMPORTANTE: Este campo NÃO tem name, então não é enviado para o servidor -->
                    <!-- É usado apenas para validação no frontend (JavaScript) -->
                    <div class="input-group">
                        <label for="confirmar_senha">Confirmar Senha</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirmar_senha" placeholder="********" required>
                        <i class="fas fa-eye password-toggle" id="toggle-password"></i>
                    </div>

                    <!-- ========================================
                         CAMPO: TIPO DE USUÁRIO
                         ======================================== -->
                    <div class="input-group">
                        <label for="tipo">Tipo de Usuário:</label>
                        <!-- select = lista suspensa (dropdown) -->
                        <select name="tipo" id="tipo" required>
                            <option value="">Selecione</option> <!-- Opção vazia (obriga seleção) -->
                            <option value="estudante">Estudante</option>
                            <option value="professor">Professor</option>
                            <option value="coordenador">Coordenador</option>
                        </select>
                    </div>

                    <!-- ========================================
                         CAMPO: CURSO (DINÂMICO DO BANCO)
                         ======================================== -->
                    <div class="input-group">
                        <label for="curso_id">Curso:</label>
                        <select name="curso_id" id="curso_id" required>
                            <option value="">Selecione seu curso</option>
                            
                            <!-- ========================================
                                 LOOP PARA GERAR OPÇÕES DINAMICAMENTE
                                 ======================================== -->
                            <!-- foreach = loop através do array $cursos -->
                            <?php foreach ($cursos as $curso): ?>
                                <!-- value = valor enviado para o servidor -->
                                <!-- texto = o que o usuário vê -->
                                <option value="<?php echo $curso['curso_id']; ?>">
                                    <!-- htmlspecialchars = previne XSS (Cross-Site Scripting) -->
                                    <?php echo htmlspecialchars($curso['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ========================================
                         PONTO DE EXPANSÃO: NOVOS CAMPOS
                         ======================================== -->
                    <!-- 
                    Aqui você pode adicionar novos campos para sua prova:
                    
                    1. CAMPO CPF:
                    <div class="input-group">
                        <label for="cpf">CPF</label>
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" 
                               oninput="mascaraCPF(event)" required>
                    </div>
                    
                    2. CAMPO ESTADO:
                    <div class="input-group">
                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado" required>
                            <option value="">Selecione seu estado</option>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['estado_id']; ?>">
                                    <?php echo htmlspecialchars($estado['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    3. CAMPO CIDADE (dependente do estado):
                    <div class="input-group">
                        <label for="cidade">Cidade:</label>
                        <select name="cidade" id="cidade" required>
                            <option value="">Primeiro selecione o estado</option>
                        </select>
                    </div>
                    
                    4. CAMPO GÊNERO:
                    <div class="input-group">
                        <label for="genero">Gênero:</label>
                        <select name="genero" id="genero" required>
                            <option value="">Selecione</option>
                            <option value="masculino">Masculino</option>
                            <option value="feminino">Feminino</option>
                            <option value="outro">Outro</option>
                            <option value="nao_informar">Prefiro não informar</option>
                        </select>
                    </div>
                    
                    5. CAMPO ÁREA DE INTERESSE:
                    <div class="input-group">
                        <label for="area_interesse">Área de Interesse:</label>
                        <select name="area_interesse" id="area_interesse">
                            <option value="">Selecione (opcional)</option>
                            <option value="tecnologia">Tecnologia</option>
                            <option value="saude">Saúde</option>
                            <option value="educacao">Educação</option>
                            <option value="negocios">Negócios</option>
                        </select>
                    </div>
                    
                    6. CAMPO BIOGRAFIA:
                    <div class="input-group">
                        <label for="biografia">Biografia (opcional):</label>
                        <textarea name="biografia" id="biografia" rows="3" 
                                  placeholder="Conte um pouco sobre você..."></textarea>
                    </div>
                    
                    7. CAMPO CHECKBOX:
                    <div class="input-group">
                        <label>
                            <input type="checkbox" name="aceita_newsletter" value="1">
                            Desejo receber newsletter
                        </label>
                    </div>
                    
                    8. CAMPO RADIO BUTTONS:
                    <div class="input-group">
                        <label>Como conheceu o FacilitaU?</label>
                        <div class="radio-group">
                            <label><input type="radio" name="como_conheceu" value="google"> Google</label>
                            <label><input type="radio" name="como_conheceu" value="amigo"> Indicação de amigo</label>
                            <label><input type="radio" name="como_conheceu" value="redes_sociais"> Redes sociais</label>
                            <label><input type="radio" name="como_conheceu" value="outro"> Outro</label>
                        </div>
                    </div>
                    -->

                    <!-- ========================================
                         BOTÃO DE ENVIO
                         ======================================== -->
                    <!-- type="submit" = botão que envia o formulário -->
                    <!-- name="submit" = identificador do botão (pode ser verificado no PHP) -->
                    <button type="submit" name="submit" class="btn btn-primary">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================
         SCRIPTS JAVASCRIPT
         ======================================== -->
    <!-- Ordem de carregamento é importante! -->
    <script src="JS/Login.js"></script>        <!-- Funções gerais (mostrar/ocultar senha) -->
    <script src="JS/Vlibras.js"></script>      <!-- Acessibilidade (tradutor de libras) -->
    <script src="JS/validar_cad.js"></script>  <!-- Validações específicas do cadastro -->
    
    <!-- ========================================
         PONTO DE EXPANSÃO: SCRIPTS ADICIONAIS
         ======================================== -->
    <!--
    Você pode adicionar scripts para:
    
    1. Validação de CPF:
    <script>
    function validarCPF(cpf) {
        // Lógica de validação de CPF
    }
    </script>
    
    2. Busca de cidades por estado (AJAX):
    <script>
    document.getElementById('estado').addEventListener('change', function() {
        const estadoId = this.value;
        if (estadoId) {
            fetch(`buscar_cidades.php?estado_id=${estadoId}`)
                .then(response => response.json())
                .then(cidades => {
                    const selectCidade = document.getElementById('cidade');
                    selectCidade.innerHTML = '<option value="">Selecione a cidade</option>';
                    cidades.forEach(cidade => {
                        selectCidade.innerHTML += `<option value="${cidade.id}">${cidade.nome}</option>`;
                    });
                });
        }
    });
    </script>
    
    3. Validação de força da senha:
    <script>
    function verificarForcaSenha(senha) {
        // Lógica para verificar força da senha
        // Mostrar indicador visual (fraca, média, forte)
    }
    </script>
    -->
    
</body>
</html>

<?php
// ========================================
// PONTOS DE EXPANSÃO PARA SUA PROVA:
// ========================================

/*
EXEMPLO COMPLETO: ADICIONANDO CAMPO CPF

1. NO BANCO DE DADOS:
ALTER TABLE Usuarios ADD COLUMN cpf VARCHAR(14) UNIQUE;

2. NO PHP (coleta de dados):
$cpf = $_POST['cpf'];

3. NO PHP (validação):
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) return false;
    
    // Lógica completa de validação de CPF
    // ... (algoritmo de validação)
    
    return true;
}

if (!validarCPF($cpf)) {
    $erro = "CPF inválido!";
}

4. NO PHP (inserção):
$sql = "INSERT INTO Usuarios (email, senha, tipo, nome, data_nascimento, telefone, curso_id, cpf) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt->bind_param("ssssssiss", $email, $senha, $tipo, $nome, $data_nascimento, $telefone, $curso_id, $cpf);

5. NO HTML:
<div class="input-group">
    <label for="cpf">CPF</label>
    <i class="fas fa-id-card input-icon"></i>
    <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" required>
</div>

6. NO JAVASCRIPT (máscara):
function mascaraCPF(event) {
    let valor = event.target.value.replace(/\D/g, '');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    event.target.value = valor;
}

COMO EXIBIR OS DADOS DEPOIS:

1. CRIAR PÁGINA DE PERFIL (perfil_usuario.php):
<?php
session_start();
include 'config.php';

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM Usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
?>

<h2>Meu Perfil</h2>
<p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
<p><strong>CPF:</strong> <?php echo htmlspecialchars($usuario['cpf']); ?></p>
<p><strong>Telefone:</strong> <?php echo htmlspecialchars($usuario['telefone']); ?></p>

2. CRIAR LISTA DE USUÁRIOS (para administradores):
<?php
$sql = "SELECT u.*, c.nome as curso_nome FROM Usuarios u 
        LEFT JOIN Cursos c ON u.curso_id = c.curso_id 
        ORDER BY u.nome";
$result = $conn->query($sql);

echo "<table>";
echo "<tr><th>Nome</th><th>Email</th><th>CPF</th><th>Curso</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . htmlspecialchars($row['cpf']) . "</td>";
    echo "<td>" . htmlspecialchars($row['curso_nome']) . "</td>";
    echo "</tr>";
}
echo "</table>";
?>

DICAS PARA A PROVA:

1. SEMPRE use htmlspecialchars() ao exibir dados do banco
2. SEMPRE use prepared statements para inserir dados
3. SEMPRE valide dados no backend (PHP), não confie só no frontend
4. SEMPRE criptografe senhas com password_hash()
5. SEMPRE feche conexões e statements
6. SEMPRE trate erros adequadamente
7. SEMPRE use nomes descritivos para variáveis e funções

CAMPOS MAIS COMUNS EM PROVAS:
- CPF
- RG  
- Estado/Cidade
- Gênero
- Data de admissão
- Salário (para funcionários)
- Matrícula (para estudantes)
- Área de atuação
- Biografia/Descrição
- Foto de perfil
*/
?>
