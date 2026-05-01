<?php
require('fpdf/fpdf.php'); // Biblioteca FPDF para gerar PDFs
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($cx->connect_error) {
    die("Erro na conexão: " . $cx->connect_error);
}

// Obtendo usuário logado e filtros
$eq_user = $_SESSION["username"];
$data_filtro = isset($_GET['data_reserva']) ? $_GET['data_reserva'] : '';
$destino_filtro = isset($_GET['destino']) ? $_GET['destino'] : '';
$voo_id_filtro = isset($_GET['voo_id']) ? $_GET['voo_id'] : '';

// Parâmetros de paginação
$por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $por_pagina;

// Criando consulta dinâmica com paginação
$query = "
    SELECT r.voo_id, v.destino, v.preco, r.numero_assento, r.data_reserva, r.transacao_hash 
    FROM reservas_voo r
    JOIN voos v ON r.voo_id = v.id
    WHERE r.eq_user = ?
";

$params = [$eq_user];
$types = "s";

if (!empty($data_filtro)) {
    $query .= " AND r.data_reserva = ?";
    $params[] = $data_filtro;
    $types .= "s";
}
if (!empty($destino_filtro)) {
    $query .= " AND v.destino = ?";
    $params[] = $destino_filtro;
    $types .= "s";
}
if (!empty($voo_id_filtro)) {
    $query .= " AND r.voo_id = ?";
    $params[] = $voo_id_filtro;
    $types .= "s";
}

$query .= " LIMIT ? OFFSET ?";
$params[] = $por_pagina;
$types .= "ii";
$params[] = $offset;

// Preparar e executar consulta
$stmt = $cx->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Contagem total de registros para paginação
$query_total = "SELECT COUNT(*) AS total FROM reservas_voo WHERE eq_user = ?";
$stmt_total = $cx->prepare($query_total);
$stmt_total->bind_param("s", $eq_user);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_registros = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);

$stmt_total->close();
$stmt->close();
$cx->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minhas Reservas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">Suas Reservas</h2>

    <form method="GET" class="mb-4 text-center">
        <label for="data_reserva" class="form-label">Filtrar por Data:</label>
        <input type="date" id="data_reserva" name="data_reserva" class="form-control w-50 mx-auto" value="<?= htmlspecialchars($data_filtro); ?>">

        <label for="destino" class="form-label mt-3">Filtrar por Destino:</label>
        <input type="text" id="destino" name="destino" class="form-control w-50 mx-auto" value="<?= htmlspecialchars($destino_filtro); ?>">

        <label for="voo_id" class="form-label mt-3">Filtrar por ID do Voo:</label>
        <input type="text" id="voo_id" name="voo_id" class="form-control w-50 mx-auto" value="<?= htmlspecialchars($voo_id_filtro); ?>">

        <input type="hidden" name="pagina" value="1"> <!-- Sempre inicia na primeira página -->
        <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Voo ID</th>
                        <th>Destino</th>
                        <th>Preço (BNB)</th>
                        <th>Assento</th>
                        <th>Data</th>
                        <th>Transação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['voo_id']); ?></td>
                            <td><?= htmlspecialchars($row['destino']); ?></td>
                            <td><?= number_format($row['preco'], 8, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($row['numero_assento']); ?></td>
                            <td><?= htmlspecialchars($row['data_reserva']); ?></td>
                            <td><?= htmlspecialchars($row['transacao_hash']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <nav class="text-center mt-4">
            <ul class="pagination">
                <?php if ($pagina_atual > 1): ?>
                    <li class="page-item"><a class="page-link" href="?data_reserva=<?= urlencode($data_filtro); ?>&destino=<?= urlencode($destino_filtro); ?>&voo_id=<?= urlencode($voo_id_filtro); ?>&pagina=<?= $pagina_atual - 1 ?>">Anterior</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?= ($i == $pagina_atual) ? 'active' : '' ?>">
                        <a class="page-link" href="?data_reserva=<?= urlencode($data_filtro); ?>&destino=<?= urlencode($destino_filtro); ?>&voo_id=<?= urlencode($voo_id_filtro); ?>&pagina=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina_atual < $total_paginas): ?>
                    <li class="page-item"><a class="page-link" href="?data_reserva=<?= urlencode($data_filtro); ?>&destino=<?= urlencode($destino_filtro); ?>&voo_id=<?= urlencode($voo_id_filtro); ?>&pagina=<?= $pagina_atual + 1 ?>">Próximo</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="text-center mt-4">
            <a href="gerar_pdf.php?data_reserva=<?= urlencode($data_filtro); ?>&destino=<?= urlencode($destino_filtro); ?>&voo_id=<?= urlencode($voo_id_filtro); ?>" class="btn btn-success">Baixar PDF</a>
        </div>

    <?php else: ?>
        <p class="text-center text-danger">Nenhuma reserva encontrada com esses filtros.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
