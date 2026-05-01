<?php
require('fpdf/fpdf.php');
require('phpqrcode/qrlib.php');
session_start();

// Exibir erros para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($cx->connect_error) {
    die("Erro na conexão: " . $cx->connect_error);
}

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$eq_user = $_SESSION["username"];

// Processar embarque e gerar PDF
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["transacao_hash"])) {
    $transacao_hash = $_POST["transacao_hash"];

    // Validar reserva
    $query_check = "SELECT r.voo_id, r.numero_assento, r.data_reserva, r.transacao_hash, r.embarcado, r.data_embarque, i.documento
                    FROM reservas_voo r
                    JOIN identificacao i ON r.eq_user = i.username
                    WHERE r.eq_user = ? AND r.transacao_hash = ?";
    $stmt_check = $cx->prepare($query_check);
    $stmt_check->bind_param("ss", $eq_user, $transacao_hash);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();

    if (!$row) {
        die("Erro: Esta reserva não pertence ao usuário logado.");
    } elseif ($row["embarcado"] == 1) {
        die("Erro: Este passageiro já embarcou e não pode embarcar novamente.");
    }

    // Atualizar status para impedir reimpressão
    $query_update = "UPDATE reservas_voo SET embarcado = 1, data_embarque = NOW() WHERE eq_user = ? AND transacao_hash = ?";
    $stmt_update = $cx->prepare($query_update);
    $stmt_update->bind_param("ss", $eq_user, $transacao_hash);
    $stmt_update->execute();

    // Gerar QR Code com apenas a hash
    $url_verificacao = "https://carlitoslocacoes.com/site/emvoo.php?transacao_hash=" . urlencode($transacao_hash);
    $arquivo_qr = "qrcodes/qr_" . $transacao_hash . ".png";
    QRcode::png($url_verificacao, $arquivo_qr, QR_ECLEVEL_L, 6);

    // Gerar PDF com QR Code
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(190, 10, 'Registro de Embarque', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);

    $pdf->Cell(50, 10, 'Usuário:', 0, 0);
    $pdf->Cell(140, 10, $eq_user, 0, 1);
    $pdf->Cell(50, 10, 'Documento de Identidade:', 0, 0);
    $pdf->Cell(140, 10, $row['documento'], 0, 1);
    $pdf->Cell(50, 10, 'Voo ID:', 0, 0);
    $pdf->Cell(140, 10, $row['voo_id'], 0, 1);
    $pdf->Cell(50, 10, 'Assento:', 0, 0);
    $pdf->Cell(140, 10, $row['numero_assento'], 0, 1);
    $pdf->Cell(50, 10, 'Data Reserva:', 0, 0);
    $pdf->Cell(140, 10, $row['data_reserva'], 0, 1);
    $pdf->Cell(50, 10, 'Transação Hash:', 0, 0);
    $pdf->Cell(140, 10, $row['transacao_hash'], 0, 1);
    $pdf->Cell(50, 10, 'Data do Embarque:', 0, 0);
    $pdf->Cell(140, 10, date("Y-m-d H:i:s"), 0, 1);

    // Adicionar QR Code ao PDF
    $pdf->Image($arquivo_qr, 80, 120, 50, 50);

    // Excluir HTML antes de gerar PDF
    ob_clean();
    $pdf->Output('D', 'registro_embarque.pdf');

    session_destroy();
    exit;
}

$cx->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Embarque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">Registro de Embarque</h2>

    <form method="POST" class="text-center">
        <label for="transacao_hash" class="form-label">Digite o Hash da Transação:</label>
        <input type="text" id="transacao_hash" name="transacao_hash" class="form-control w-50 mx-auto" required>
        <button type="submit" class="btn btn-primary mt-2">Registrar Embarque</button>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
