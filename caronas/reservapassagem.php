<?php
session_start();

// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar login
if (!isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true) {
    header("location: login.php");
    exit;
}

$_SESSION["eq_user"] = $_SESSION["username_odonto2"];
$eq_user = $_SESSION["eq_user"];

// Conexão com banco
$cx = new mysqli("127.0.0.1", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($cx->connect_error) {
    die("Erro na conexão com o banco: " . $cx->connect_error);
}
$cx->set_charset("utf8mb4");

$username = htmlspecialchars($_SESSION["username_odonto2"]);
$voo_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$vooSelecionado = null;
$assentosDisponiveis = [];
$datasReservadasPorAssento = [];
$datasPermitidas = [];

if ($voo_id) {
    $stmt = $cx->prepare("SELECT id, destino, preco, metamask, datas_permitidas FROM voos WHERE id = ?");
    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $vooSelecionado = $result->fetch_assoc();
        $datasPermitidas = explode(",", $vooSelecionado['datas_permitidas']);
    } else {
        die("Erro: Voo não encontrado.");
    }
    $stmt->close();

    $stmt = $cx->prepare("SELECT numero_assento FROM assentos WHERE voo_id = ? AND pago = 0");
    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assentosDisponiveis[] = $row['numero_assento'];
    }
    $stmt->close();

    $stmt = $cx->prepare("SELECT numero_assento, data_reserva FROM reservas_voo WHERE voo_id = ? AND pago = 1 AND transacao_hash IS NOT NULL");
    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $datasReservadasPorAssento[$row['numero_assento']][] = $row['data_reserva'];
    }
    $stmt->close();
}

$saldoAura = 0;
$stmtSaldo = $cx->prepare("SELECT saldo_total FROM identificacao_odonto2 WHERE username = ? LIMIT 1");
if ($stmtSaldo) {
    $stmtSaldo->bind_param("s", $username);
    $stmtSaldo->execute();
    $resSaldo = $stmtSaldo->get_result();
    if ($resSaldo->num_rows > 0) {
        $rowSaldo = $resSaldo->fetch_assoc();
        $saldoAura = $rowSaldo['saldo_total'] ?? 0;
    }
    $stmtSaldo->close();
}
$stmtUser = $cx->prepare("SELECT documento, caixa_postal, saldo_total 
                          FROM identificacao_odonto2 
                          WHERE username = ? LIMIT 1");
$stmtUser->bind_param("s", $username);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
$userData = $resUser->fetch_assoc();
$stmtUser->close();

if (!$userData || empty($userData['caixa_postal'])) {
    // Se não tiver caixa_postal, redireciona para a página de identificação
    header("Location: https://carlitoslocacoes.com/farolqr/site/identificacao_farolqr.php");
    exit;
}


$cx->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Reserva de Passagem</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/css/pikaday.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/pikaday.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .wrapper {
            max-width: 500px;
            width: 100%;
            background: rgba(255, 255, 255, 0.15);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2, h3 {
            font-weight: bold;
            margin-bottom: 15px;
        }
        select, input {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            background-color: white;
            color: black;
        }
        button {
            width: 100%;
            background: #1e90ff;
            color: #fff;
            border: none;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            transition: 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        button:disabled {
            background: #cccccc;
            color: #333;
            cursor: not-allowed;
        }
        .saldo-box {
            background: rgba(76, 175, 80, 0.3);
            border: 2px solid #4CAF50;
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .reserva-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .assento-box, .data-box {
            flex: 1;
        }
        @media screen and (max-width: 768px) {
            .wrapper { max-width: 90%; padding: 20px; }
            h2, h3 { font-size: 20px; }
            select, input { font-size: 18px; }
            button { font-size: 20px; }
            .reserva-container { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="wrapper" role="main">
    <h2 role="heading" aria-level="2">Olá, <?php echo $username; ?> - Reserva de Passagem</h2>
    <p aria-live="polite">Escolha seu assento e a data do voo.</p>

    <h3 aria-label="Destino do voo">
        Destino: <?php echo htmlspecialchars($vooSelecionado['destino']); ?> - Aura <?php echo number_format($vooSelecionado['preco'], 8, ',', '.'); ?>
    </h3>

    <div class="saldo-box" role="status" aria-live="polite">
        Seu Saldo AURA: <b><?php echo number_format($saldoAura, 0, ',', '.'); ?></b>
    </div>

    <div class="reserva-container">
        <div class="assento-box">
            <label for="assento">Selecione um assento:</label>
            <select id="assento" aria-label="Lista de assentos disponíveis">
                <?php foreach ($assentosDisponiveis as $assento): ?>
                    <option value="<?php echo $assento; ?>" aria-label="Assento número <?php echo $assento; ?>">
                        <?php echo $assento; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="data-box">
            <label for="datepicker">Selecione uma Data:</label>
            <input type="text" id="datepicker" readonly aria-label="Campo de seleção de data do voo">
        </div>
    </div>

    <button id="confirmarReserva" disabled onclick="redirecionarParaPagamentoAura()" aria-label="Confirmar reserva via AURA">
        Confirmar Reserva via AURA
    </button>
</div>

<!-- Elemento invisível para leitores de tela -->
<div id="statusReserva" aria-live="polite" style="position:absolute; left:-9999px;"></div>

<script>
    var datasPermitidas = <?php echo json_encode($datasPermitidas); ?>;
    var datasReservadasPorAssento = <?php echo json_encode($datasReservadasPorAssento); ?>;
    var assentoSelect = document.getElementById("assento");
    var picker;

    function atualizarCalendario(assentoSelecionado) {
        var datasReservadas = datasReservadasPorAssento[assentoSelecionado] || [];

        if (picker) {
            picker.destroy();
        }

        picker = new Pikaday({
            field: document.getElementById('datepicker'),
            format: 'YYYY-MM-DD',
            disableDayFn: function (date) {
                let dataStr = date.toISOString().split('T')[0];
                if (!datasPermitidas.includes(dataStr)) {
                    return true; // bloqueia
                }
                if (datasReservadas.includes(dataStr)) {
                    return true; // bloqueia
                }
                return false; // permitido
            },
            onSelect: function (date) {
                const dataFormatada = moment(date).format('YYYY-MM-DD');
                document.getElementById("datepicker").value = dataFormatada;
                document.getElementById("confirmarReserva").disabled = false;

                // Atualiza mensagem invisível para leitores de tela
                document.getElementById("statusReserva").textContent = "Data selecionada: " + dataFormatada;
            }
        });
    }

    assentoSelect.addEventListener("change", function () {
    atualizarCalendario(this.value);
    document.getElementById("statusReserva").textContent = "Assento selecionado: " + this.value;
});


    if (assentoSelect.value) {
        atualizarCalendario(assentoSelect.value);
    }

    function redirecionarParaPagamentoAura() {
        const assentoSelecionado = document.getElementById("assento").value;
        const dataSelecionada = document.getElementById("datepicker").value;
        const vooId = <?php echo $voo_id; ?>;

        window.location.href = `pagamento_aura_voo.php?voo_id=${vooId}&assento=${assentoSelecionado}&data_reserva=${dataSelecionada}`;
    }
</script>

</body>
</html>
