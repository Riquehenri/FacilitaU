<?php
// Inicia a sessão para garantir que estamos manipulando a sessão atual
session_start();
// Destrói todos os dados da sessão, efetivamente desconectando o usuário
session_destroy();
// Redireciona o usuário para a página inicial após logout
header("Location: index.php");
// Termina a execução do script para garantir que nada mais seja executado após o redirecionamento
exit();
?>
