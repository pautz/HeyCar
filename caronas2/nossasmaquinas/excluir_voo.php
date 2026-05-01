<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true) {
    header("location: login.php");
    exit;
}

$usuarioLogado = $_SESSION["username_odonto2"] ?? null;

$cx = new mysqli("localhost", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($cx->connect_error) {
    die("Erro na conexão com o banco: " . $cx->connect_error);
}
$cx->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["voo_id"])) {
    $voo_id = intval($_POST["voo_id"]);

    // Buscar dono do voo
    $stmt = $cx->prepare("SELECT username FROM voos WHERE id = ?");
    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<p style='color:red;'>Voo não encontrado.</p>";
    } else {
        $row = $result->fetch_assoc();
        $donoVoo = $row["username"];

        if ($usuarioLogado === $donoVoo) {
            // Excluir registros dependentes primeiro (mas não reservas_voo)
            $cx->query("DELETE FROM assentos WHERE voo_id = $voo_id");
            $cx->query("DELETE FROM datas_voo WHERE voo_id = $voo_id");
            // ❌ NÃO excluir reservas_voo

            // Agora excluir o voo
            $stmtDelete = $cx->prepare("DELETE FROM voos WHERE id = ?");
            $stmtDelete->bind_param("i", $voo_id);

            if ($stmtDelete->execute()) {
                echo "<p style='color:green;'>Voo excluído com sucesso!</p>";
            } else {
                echo "<p style='color:red;'>Erro ao excluir voo.</p>";
            }
            $stmtDelete->close();
        } else {
            echo "<p style='color:red;'>Você não tem permissão para excluir este voo.</p>";
        }
    }
    $stmt->close();
} else {
    echo "<p style='color:red;'>Requisição inválida.</p>";
}

$cx->close();

// Redireciona de volta para a listagem
header("refresh:2;url=voos.php");
exit;
?>
