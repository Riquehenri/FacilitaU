<?php
$host = 'localhost:3307'; // Use 'localhost:3307' se estiver usando XAMPP ou MAMP com MySQL na porta 3307
$usuario = 'root';
$senha = ''; // Senha padrão do XAMPP ou MAMP é geralmente vazia, mas se você alterou, coloque a senha correta aqui
// Se estiver usando MAMP, a senha padrão é 'root'
$banco = 'facilitau_db';

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>

<?php
/*
===============================================
  FACILITAU_PROJ – PASSO A PASSO PROVA DE AUTORIA
  Objetivo: Adicionar novo campo (ex: linkedin_url)
===============================================

🔹 CONTEXTO:
Adicionar campo de redes sociais no cadastro e edição de perfil de usuários.
Tecnologias: PHP + MySQL + HTML

🔹 ARQUIVOS ENVOLVIDOS:
- cadastro_usuario.php           (formulário de cadastro)
- editar_perfil.php              (formulário de edição)
- perfil.php                     (visualização dos dados)
- config.php                     (conexão com o banco)
- config MySQL                   (via phpMyAdmin)
===============================================

1. [BANCO DE DADOS - MySQL]
--------------------------------
✅ Acesse phpMyAdmin
✅ Execute:
   ALTER TABLE usuarios ADD linkedin_url VARCHAR(255);

⚠️ Isso adiciona o campo no banco para ser usado nas próximas etapas

--------------------------------

2. [FRONT-END - HTML nos PHPs]
--------------------------------
✅ Abrir **cadastro_usuario.php** e **editar_perfil.php**
✅ Adicionar o campo no formulário:

   <label for="linkedin">LinkedIn:</label>
   <input type="url" id="linkedin" name="linkedin_url" placeholder="https://linkedin.com/in/usuario">

⚠️ O atributo "name" deve ser **linkedin_url** para ser capturado via $_POST

--------------------------------

3. [BACK-END - PHP Inserção e Edição]
--------------------------------
✅ Em **cadastro_usuario.php**
   $linkedin = $_POST['linkedin_url'];
   Incluir no `INSERT INTO usuarios (...) VALUES (...)`

✅ Em **editar_perfil.php**
   Capturar e aplicar no `UPDATE usuarios SET ...`

⚠️ Usar `mysqli_real_escape_string()` se quiser proteger contra caracteres especiais

--------------------------------

4. [EXIBIÇÃO DO DADO – perfil.php]
--------------------------------
✅ Atualizar SELECT para incluir linkedin_url:
   SELECT nome, email, linkedin_url FROM usuarios

✅ Exibir:
   echo "<a href='{$row['linkedin_url']}' target='_blank'>Meu LinkedIn</a>";

--------------------------------

5. [TESTAR]
--------------------------------
✅ Preencher o novo campo ao cadastrar
✅ Verificar se foi salvo no banco (via phpMyAdmin)
✅ Verificar se aparece corretamente em perfil.php

--------------------------------

💡 EXTRA:
- Validar se o campo foi preenchido com uma URL válida
- Deixar o campo opcional se não for obrigatório
*/
?>

