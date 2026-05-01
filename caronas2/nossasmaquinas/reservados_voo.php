<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true) {
    header("location: login.php");
    exit;
}

$usuario = $_SESSION["username_odonto2"];

$conn = new mysqli("127.0.0.1", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$assentos = [];
$mensagem = "";

// Paginação mapa de assentos
$limite = 10;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina - 1) * $limite;
$totalRegistros = 0;
$totalPaginas = 1;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $voo_id = !empty($_POST["voo_id"]) ? intval($_POST["voo_id"]) : null;
    $data_reserva = !empty($_POST["data_reserva"]) ? $_POST["data_reserva"] : null;
    $transacao_hash = !empty($_POST["transacao_hash"]) ? trim($_POST["transacao_hash"]) : null;

    // Se só veio o hash, descobrir o voo_id correspondente
    if ($transacao_hash && !$voo_id) {
        $stmtVoo = $conn->prepare("SELECT voo_id FROM reservas_voo WHERE transacao_hash = ? LIMIT 1");
        $stmtVoo->bind_param("s", $transacao_hash);
        $stmtVoo->execute();
        $resVoo = $stmtVoo->get_result()->fetch_assoc();
        $stmtVoo->close();
        if ($resVoo) {
            $voo_id = $resVoo['voo_id'];
        }
    }

    if ($voo_id) {
        // Verifica se o usuário logado é dono do voo
        $stmtOwner = $conn->prepare("SELECT username FROM voos WHERE id = ?");
        $stmtOwner->bind_param("i", $voo_id);
        $stmtOwner->execute();
        $resOwner = $stmtOwner->get_result();
        $ownerData = $resOwner->fetch_assoc();
        $stmtOwner->close();

        if ($ownerData && $ownerData['username'] !== $usuario) {
            $mensagem = "Você não tem permissão para visualizar reservas deste voo.";
        } else {
            // Monta SQL flexível
            $sqlBase = "FROM reservas_voo r LEFT JOIN voos v ON r.voo_id = v.id WHERE 1=1";
            $params = [];
            $types = "";

            if ($voo_id) {
                $sqlBase .= " AND r.voo_id = ?";
                $params[] = $voo_id;
                $types .= "i";
            }
            if ($data_reserva) {
                $sqlBase .= " AND r.data_reserva = ?";
                $params[] = $data_reserva;
                $types .= "s";
            }
            if ($transacao_hash) {
                $sqlBase .= " AND r.transacao_hash = ?";
                $params[] = $transacao_hash;
                $types .= "s";
            }

            // Contagem para paginação
            $stmtCount = $conn->prepare("SELECT COUNT(*) AS total " . $sqlBase);
            if (!empty($params)) $stmtCount->bind_param($types, ...$params);
            $stmtCount->execute();
            $resCount = $stmtCount->get_result();
            if ($rowCount = $resCount->fetch_assoc()) {
                $totalRegistros = $rowCount['total'];
                $totalPaginas = ceil($totalRegistros / $limite);
            }
            $stmtCount->close();

            // Busca reservas com paginação
            $sql = "SELECT r.numero_assento, r.data_reserva, r.created_at, r.eq_user, 
                           r.transacao_hash, r.pago, v.origem, v.destino, v.horario, v.username "
                 . $sqlBase . " LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);

            $params[] = $limite;
            $params[] = $offset;
            $types .= "ii";
            $stmt->bind_param($types, ...$params);

            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $assentos[$row['numero_assento']] = [
                    "status" => $row['pago'] ? "ocupado" : "livre",
                    "user" => $row['eq_user'],
                    "data_reserva" => $row['data_reserva'],
                    "created_at" => $row['created_at'],
                    "hash" => $row['transacao_hash'],
                    "origem" => $row['origem'] ?? '-',
                    "destino" => $row['destino'] ?? '-',
                    "horario" => $row['horario'] ?? '-',
                    "dono_voo" => $row['username'] ?? '(voo excluído)'
                ];
            }
            $stmt->close();

            if (empty($assentos)) {
                $mensagem = "Nenhuma reserva encontrada.";
            }
        }
    }
}

// --- Últimas reservas nos voos do dono ---
$limiteUser = 24;
$paginaUser = isset($_GET['paginaUser']) ? max(1, intval($_GET['paginaUser'])) : 1;
$offsetUser = ($paginaUser - 1) * $limiteUser;

$stmtCountUser = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM reservas_voo r
    JOIN voos v ON r.voo_id = v.id
    WHERE v.username = ?
");
$stmtCountUser->bind_param("s", $usuario);
$stmtCountUser->execute();
$resCountUser = $stmtCountUser->get_result();
$totalRegistrosUser = $resCountUser->fetch_assoc()['total'];
$totalPaginasUser = ceil($totalRegistrosUser / $limiteUser);
$stmtCountUser->close();

$stmtUser = $conn->prepare("
    SELECT r.id, r.voo_id, r.numero_assento, r.data_reserva, r.created_at, r.transacao_hash, r.pago,
           v.origem, v.destino, v.horario, r.eq_user
    FROM reservas_voo r
    JOIN voos v ON r.voo_id = v.id
    WHERE v.username = ?
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
");
$stmtUser->bind_param("sii", $usuario, $limiteUser, $offsetUser);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$reservasUser = $resultUser->fetch_all(MYSQLI_ASSOC);
$stmtUser->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Mapa de Reservas</title>
</head>
<body>
    <h1>Pesquisar Reservas</h1>
    <p>Usuário logado: <?= htmlspecialchars($usuario) ?></p>

    <form method="POST">
        <label>ID do Voo:</label>
        <input type="number" name="voo_id"><br><br>
        <label>Data da Reserva:</label>
        <input type="date" name="data_reserva"><br><br>
        <label>Hash da Transação:</label>
        <input type="text" name="transacao_hash" placeholder="Digite o transacao_hash exato"><br><br>
        <button type="submit">Pesquisar</button>
    </form>

    <?php if ($mensagem): ?>
        <p style="color:red;"><?= htmlspecialchars($mensagem) ?></p>
    <?php endif; ?>

    <?php if (!empty($assentos) && !$mensagem): ?>
        <h2>Mapa de Assentos</h2>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Assento</th>
                    <th>Status</th>
                    <th>Usuário</th>
                    <th>Data da Reserva</th>
                    <th>Data de Criação</th>
                    <th>Hash</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assentos as $numero => $info): ?>
                    <tr>
                        <td><?= htmlspecialchars($numero) ?></td>
                        <td><?= $info['status'] ?></td>
                        <td><?= $info['user'] ?: "-" ?></td>
                        <td><?= $info['data_reserva'] ?: "-" ?></td>
                        <td><?= $info['created_at'] ?: "-" ?></td>
                        <td><?= $info['hash'] ?: "-" ?></td>
                    </tr>
                                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginação do mapa de assentos -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?pagina=<?= $i ?>" class="<?= $i == $pagina ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <!-- Minhas últimas reservas -->
    <h2>Últimas Reservas nos Meus Voos</h2>
    <?php if (!empty($reservasUser)): ?>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>ID Reserva</th>
                    <th>Voo</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Horário</th>
                    <th>Assento</th>
                    <th>Data da Reserva</th>
                    <th>Data de Criação</th>
                    <th>Status</th>
                    <th>Hash</th>
                    <th>Passageiro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservasUser as $res): ?>
                    <tr>
                        <td><?= htmlspecialchars($res['id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($res['voo_id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($res['origem'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($res['destino'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($res['horario'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($res['numero_assento'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($res['data_reserva'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($res['created_at'] ?? '-') ?></td>
                        <td><?= $res['pago'] ? "✅ Pago" : "⚠️ Pendente" ?></td>
                        <td><?= htmlspecialchars($res['transacao_hash'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($res['eq_user'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginação inteligente -->
        <div class="pagination">
            <?php if ($paginaUser > 1): ?>
                <a href="?paginaUser=<?= $paginaUser - 1 ?>">« Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginasUser; $i++): ?>
                <a href="?paginaUser=<?= $i ?>" class="<?= $i == $paginaUser ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($paginaUser < $totalPaginasUser): ?>
                <a href="?paginaUser=<?= $paginaUser + 1 ?>">Próxima »</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>Nenhuma reserva encontrada nos seus voos.</p>
    <?php endif; ?>
</body>
</html>
