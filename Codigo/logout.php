<?php
// Inicia o sistema de sessões no PHP
// As sessões permitem armazenar informações do usuário entre páginas
session_start();

// Destroi completamente a sessão atual do usuário
// Isso remove TODOS os dados que estavam armazenados na sessão
// Como: usuário logado, preferências, carrinho de compras, etc.
session_destroy();

// Redireciona o usuário de volta para a página inicial (index.php)
// O header("Location") deve ser usado antes de qualquer saída HTML
header("Location: index.php");

// Encerra imediatamente a execução do script
// Isso garante que nenhum código adicional seja executado após o redirecionamento
exit();
?>