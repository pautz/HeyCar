<?php
// Inicializa a sessão
session_start();

// Inclui a conexão com o banco
require_once "config.php";

// Remove a sessão ativa do banco, se existir
if (isset($_SESSION["id"])) {
    $user_id = $_SESSION["id"];
    $sql_delete = "DELETE FROM active_sessions WHERE user_id = ?";
    if ($stmt_delete = mysqli_prepare($link, $sql_delete)) {
        mysqli_stmt_bind_param($stmt_delete, "i", $user_id);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
    }
}

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destroi a sessão
session_unset();
session_destroy();

// Redireciona para a página de login
header("Location: login_farolqr.php");
exit;
?>
