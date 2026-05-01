<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$cx = new mysqli("127.0.0.1", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($cx->connect_error) {
    die("Erro na conexão: " . $cx->connect_error);
}

// Obtendo a hash fornecida na URL
$transacao_hash = isset($_GET['transacao_hash']) ? trim($_GET['transacao_hash']) : '';

if (!empty($transacao_hash)) {
    // Verificar se voo_ok já é 1
    $checkQuery = "SELECT voo_ok FROM reservas_voo WHERE transacao_hash = ?";
    $stmtCheck = $cx->prepare($checkQuery);
    if ($stmtCheck) {
        $stmtCheck->bind_param("s", $transacao_hash);
        $stmtCheck->execute();
        $stmtCheck->bind_result($voo_ok);
        $stmtCheck->fetch();
        $stmtCheck->close();

        if ($voo_ok == 1) {
            echo "<p class='text-warning text-center'>O voo já estava confirmado!</p>";
        } else {
            // Atualizar voo_ok apenas se ainda não for 1
            $updateQuery = "UPDATE reservas_voo SET voo_ok = 1 WHERE transacao_hash = ?";
            $stmtUpdate = $cx->prepare($updateQuery);
            if ($stmtUpdate) {
                $stmtUpdate->bind_param("s", $transacao_hash);
                if ($stmtUpdate->execute()) {
                    echo "<p class='text-success text-center'>Voo atualizado com sucesso!</p>";
                } else {
                    echo "<p class='text-danger text-center'>Erro na atualização: " . $stmtUpdate->error . "</p>";
                }
                $stmtUpdate->close();
            } else {
                echo "<p class='text-danger text-center'>Erro na preparação da consulta: " . $cx->error . "</p>";
            }
        }
    } else {
        echo "<p class='text-danger text-center'>Erro na consulta de voo_ok: " . $cx->error . "</p>";
    }
}

// Exibir reservas com a hash fornecida
$query = "
    SELECT r.voo_id, v.destino, v.preco, r.numero_assento, r.data_reserva, r.transacao_hash, r.eq_user,
           r.embarcado, r.data_embarque, i.documento, r.voo_ok
    FROM reservas_voo r
    JOIN voos v ON r.voo_id = v.id
    LEFT JOIN identificacao i ON r.eq_user = i.username
    WHERE r.transacao_hash = ?
";

$stmt = $cx->prepare($query);
if (!$stmt) {
    die("<p class='text-danger text-center'>Erro na consulta: " . $cx->error . "</p>");
}

$stmt->bind_param("s", $transacao_hash);
$stmt->execute();
$result = $stmt->get_result();

$reservas = [];
while ($row = $result->fetch_assoc()) {
    $reservas[] = $row;
}

$stmt->close();
$cx->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consultar Reservas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center">Buscar Reservas</h2>

    <?php if (!empty($reservas)): ?>
        <div class="mt-4">
            <h3 class="text-center">Detalhes das Reservas</h3>
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Usuário</th>
                        <th>Documento</th>
                        <th>Voo ID</th>
                        <th>Destino</th>
                        <th>Preço (BNB)</th>
                        <th>Assento</th>
                        <th>Data</th>
                        <th>Transação</th>
                        <th>Embarcou?</th>
                        <th>Data do Embarque</th>
                        <th>Voo OK</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><?= htmlspecialchars($reserva['eq_user']) ?></td>
                            <td><?= htmlspecialchars($reserva['documento'] ?? 'Não cadastrado') ?></td>
                            <td><?= htmlspecialchars($reserva['voo_id']) ?></td>
                            <td><?= htmlspecialchars($reserva['destino']) ?></td>
                            <td><?= number_format($reserva['preco'], 8, ',', '.') ?></td>
                            <td><?= htmlspecialchars($reserva['numero_assento']) ?></td>
                            <td><?= htmlspecialchars($reserva['data_reserva']) ?></td>
                            <td><?= htmlspecialchars($reserva['transacao_hash']) ?></td>
                            <td><?= $reserva['embarcado'] ? '<span class="text-success">Sim</span>' : '<span class="text-danger">Não</span>' ?></td>
                            <td><?= $reserva['data_embarque'] ? htmlspecialchars($reserva['data_embarque']) : '—' ?></td>
                            <td><?= $reserva['voo_ok'] ? '<span class="text-success">Confirmado</span>' : '<span class="text-danger">Pendente</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-danger mt-4">Nenhuma reserva encontrada para a hash informada.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
