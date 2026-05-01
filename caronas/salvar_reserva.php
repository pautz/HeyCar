<?php
session_start();

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($cx->connect_error) {
    die("Erro de conexão: " . $cx->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar os dados enviados via POST
    $voo_id = intval($_POST["voo_id"]);
    $assento = htmlspecialchars($_POST["assento"]);
    $data_reserva = $_POST["data_reserva"];
    $transacao_hash = $_POST["transacao_hash"];
    $eq_user = isset($_SESSION["username_odonto2"]) ? $_SESSION["username_odonto2"] : "";

    // Verificar se a reserva já existe para evitar duplicação
    $stmt_verificar = $cx->prepare("SELECT id FROM reservas_voo WHERE voo_id = ? AND numero_assento = ? AND data_reserva = ?");
    $stmt_verificar->bind_param("iss", $voo_id, $assento, $data_reserva);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();

    if ($result_verificar->num_rows > 0) {
        echo "Erro: Esse assento já está reservado para essa data.";
    } else {
        // Inserir nova reserva no banco de dados
        $stmt = $cx->prepare("INSERT INTO reservas_voo (eq_user, voo_id, numero_assento, data_reserva, transacao_hash, pago) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sisss", $eq_user, $voo_id, $assento, $data_reserva, $transacao_hash);

        if ($stmt->execute()) {
            echo "Reserva registrada com sucesso!";
        } else {
            echo "Erro ao registrar reserva: " . $stmt->error;
        }
        $stmt->close();
    }

    $stmt_verificar->close();
}
?>
