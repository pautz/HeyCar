<?php
session_start();
if (!isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true) {
    header("location: login_farolqr.php");
    exit;
}

$usuario = $_SESSION["username_odonto2"];
$mensagem = "";
$comprovante = null;

$conn = new mysqli("localhost", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($conn->connect_error) die("Erro na conexão: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

// 🔍 Buscar caixa_postal e saldo do usuário logado
$stmtUser = $conn->prepare("SELECT caixa_postal, saldo_total FROM identificacao_odonto2 WHERE username = ? LIMIT 1");
$stmtUser->bind_param("s", $usuario);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
$userData = $resUser->fetch_assoc();
$caixaUsuario = $userData["caixa_postal"] ?? null;
$saldoAura = $userData["saldo_total"] ?? 0;
$stmtUser->close();
// 🚨 Se não encontrar o usuário na tabela, redireciona para identificação
if (!$userData) {
    header("Location: https://carlitoslocacoes.com/HeyCar/farolqr/site/identificacao_farolqr.php");
    exit;
}

// ✅ Mensagem pós transação
if (isset($_GET["transacao"]) && isset($_SESSION["comprovante_aura"])) {
    $mensagem = "✅ Transação registrada com sucesso!";
    $comprovante = $_SESSION["comprovante_aura"];
    unset($_SESSION["comprovante_aura"]);
}

// 🚀 Processar transferência
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["enviar_aura"])) {
    $destinoCaixa = $_POST["caixa_destino"];
    $valor = (int)$_POST["valor"];
    $senhaInformada = $_POST["senha_usuario"];

    // Verificar senha do usuário
    $stmtSenha = $conn->prepare("SELECT password FROM odonto2_users WHERE username = ?");
    $stmtSenha->bind_param("s", $usuario);
    $stmtSenha->execute();
    $resSenha = $stmtSenha->get_result();
    $senhaCorreta = $resSenha->fetch_assoc()["password"] ?? null;
    $stmtSenha->close();

    // Limite diário
    $limiteDiario = 1500000;
    $hoje = date("Y-m-d");
    $stmtTotalDia = $conn->prepare("
        SELECT SUM(valor) AS total_dia 
        FROM transacoes_aura 
        WHERE remetente = ? AND DATE(data_registro) = ?
    ");
    $stmtTotalDia->bind_param("ss", $usuario, $hoje);
    $stmtTotalDia->execute();
    $resTotalDia = $stmtTotalDia->get_result();
    $totalDia = (int)($resTotalDia->fetch_assoc()["total_dia"] ?? 0);
    $stmtTotalDia->close();

    // 🔒 Verificações
    if (!$senhaCorreta || !password_verify($senhaInformada, $senhaCorreta)) {
        $mensagem = "⚠️ Senha incorreta. Transação não autorizada.";
    } elseif ($valor <= 0 || $saldoAura < $valor) {
        $mensagem = "⚠️ Valor inválido ou saldo insuficiente.";
    } elseif ($valor > 72000) {
        $mensagem = "⚠️ O valor máximo por transação é 72.000 aura.";
    } elseif (($totalDia + $valor) > $limiteDiario) {
        $mensagem = "⚠️ Limite diário de 1.500.000 aura excedido. Você já enviou {$totalDia} hoje.";
    } else {
        // Verifica se caixa_postal de destino existe
        $stmtDest = $conn->prepare("SELECT saldo_total FROM identificacao_odonto2 WHERE caixa_postal = ?");
        $stmtDest->bind_param("s", $destinoCaixa);
        $stmtDest->execute();
        $resDest = $stmtDest->get_result();
        $destData = $resDest->fetch_assoc();
        $stmtDest->close();

        if ($destData) {
            $conn->begin_transaction();
            try {
                // Atualizar saldo do remetente
                $stmtDeb = $conn->prepare("UPDATE identificacao_odonto2 SET saldo_total = saldo_total - ? WHERE caixa_postal = ? AND username = ?");
                $stmtDeb->bind_param("iss", $valor, $caixaUsuario, $usuario);
                $stmtDeb->execute();
                $stmtDeb->close();

                // Atualizar saldo do destinatário
                $stmtCred = $conn->prepare("UPDATE identificacao_odonto2 SET saldo_total = saldo_total + ? WHERE caixa_postal = ?");
                $stmtCred->bind_param("is", $valor, $destinoCaixa);
                $stmtCred->execute();
                $stmtCred->close();

                // 🔑 Gerar assinatura única
                $assinatura = hash('sha256', $usuario . $destinoCaixa . $valor . $caixaUsuario . microtime(true));

                // Registrar transação com assinatura
                $stmtInsert = $conn->prepare("
                    INSERT INTO transacoes_aura (remetente, destinatario, valor, caixa_origem, caixa_destino, assinatura)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmtInsert->bind_param("ssisss", $usuario, $destinoCaixa, $valor, $caixaUsuario, $destinoCaixa, $assinatura);
                $stmtInsert->execute();

                $transacaoId = $conn->insert_id;
                $stmtInsert->close();

                // Gerar comprovante
                $comprovante = [
                    "id_transacao" => $transacaoId,
                    "remetente" => $usuario,
                    "destinatario" => $destinoCaixa,
                    "valor" => $valor,
                    "caixa_origem" => $caixaUsuario,
                    "caixa_destino" => $destinoCaixa,
                    "data" => date("d/m/Y H:i:s"),
                    "assinatura" => $assinatura
                ];
                $_SESSION["comprovante_aura"] = $comprovante;

                // Registrar comprovante com assinatura
                $stmtComp = $conn->prepare("
                    INSERT INTO comprovantes_aura (remetente, destinatario, valor, caixa_origem, caixa_destino, transacao_id, assinatura)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtComp->bind_param("ssissis", $usuario, $destinoCaixa, $valor, $caixaUsuario, $destinoCaixa, $transacaoId, $assinatura);
                $stmtComp->execute();
                $stmtComp->close();

                $conn->commit();
                header("Location: " . $_SERVER["PHP_SELF"] . "?transacao=ok");
                exit;

            } catch (Exception $e) {
                $conn->rollback();
                $mensagem = "❌ Erro ao transferir aura: " . $e->getMessage();
            }
        } else {
            $mensagem = "⚠️ Caixa postal não encontrada.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>🔐 Painel de Aura</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
  :root {
  --bg-color: #f0f4f8;
  --card-color: #ffffff;
  --accent-color: #007bff;
  --accent-hover: #0056b3;
  --text-color: #333;
  --radius: 12px;
  --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  --font-main: 'Segoe UI', 'Roboto', sans-serif;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: var(--font-main);
  background-color: var(--bg-color);
  color: var(--text-color);
  padding: 20px;
}

.container {
  max-width: 700px;
  margin: auto;
  background-color: var(--card-color);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 30px;
  text-align: center;
}

h1, h2, h3 {
  font-weight: 500;
  margin-bottom: 20px;
  color: var(--accent-color);
}

p {
  margin: 10px 0;
  font-size: 1rem;
}

.balance {
  font-size: 1.8rem;
  margin: 20px 0;
  color: var(--text-color);
}

form {
  margin-top: 20px;
  text-align: left;
}

input[type="text"],
input[type="number"],
input[type="password"] {
  width: 100%;
  padding: 12px;
  margin: 10px 0;
  border-radius: var(--radius);
  border: 1px solid #ccc;
  font-size: 1rem;
  background-color: #f9f9f9;
}

button {
  background-color: var(--accent-color);
  color: white;
  border: none;
  padding: 14px;
  border-radius: var(--radius);
  font-size: 1rem;
  cursor: pointer;
  width: 100%;
  transition: background 0.3s ease;
}

button:hover {
  background-color: var(--accent-hover);
}

.mensagem {
  margin: 15px 0;
  font-size: 1rem;
  color: #444;
}

.comprovante {
  margin-top: 20px;
  padding: 20px;
  background: #e6fff2;
  border: 2px dashed #28a745;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}

.comprovante p {
  font-size: 0.95rem;
}

img.qr {
  display: block;
  margin: 15px auto;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}

@media (max-width: 600px) {
  .container {
    padding: 20px;
  }

  h1, h2, h3 {
    font-size: 1.4rem;
  }

  .balance {
    font-size: 1.4rem;
  }

  button {
    font-size: 0.95rem;
    padding: 12px;
  }
}

@media print {
  .saldo, form, .mensagem, h2, h3:not(:has(+ .comprovante)) {
    display: none;
  }

  body {
    background: white;
  }

  .comprovante {
    border: none;
    box-shadow: none;
  }

  .comprovante button {
    display: none;
  }
}
.btn-aura {
  display: inline-block;
  background-color: #007bff;
  color: white;
  padding: 14px 24px;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: bold;
  text-decoration: none;
  transition: background 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-aura:hover {
  background-color: #0056b3;
}

  </style>
</head>
<body>
  <div class="container">
      <a href="https://carlitoslocacoes.com/todofarol" class="btn-aura" target="_blank">Início</a>
      <a href="https://carlitoslocacoes.com/index.php" class="btn-aura" target="_blank">🛒 Compre AURA</a>
<a href="https://carlitoslocacoes.com/entregue_semente" class="btn-aura" target="_blank">🌐 Entradas</a><br><br>
<a href="https://carlitoslocacoes.com/bank/extrato_saida.php" class="btn-aura" target="_blank">🌐 Saídas</a>
<a href="https://carlitoslocacoes.com/venda_aura/" class="btn-aura" target="_blank">🌐 Venda Aura</a>
<a href="https://carlitoslocacoes.com/todofarol/site2/nossasmaquinas/gestao.php" class="btn-aura" target="_blank">🌐 Gestão</a>

<a href="https://carlitoslocacoes.com/HeyCar/farolqr/site/logout.php" class="btn-aura">Sair</a>

    <h2>🔐 Painel de Aura</h2>
<p><strong>Caixa Postal:</strong> <?= htmlspecialchars(html_entity_decode($caixaUsuario, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?></p>

    <?php if ($mensagem): ?>
      <div class="mensagem"><?= $mensagem ?></div>
    <?php endif; ?>

    <?php if ($comprovante): ?>
<div class="comprovante">
  <h3>📄 Comprovante de Transação</h3>
  <p><strong>Remetente:</strong> <?= htmlspecialchars($comprovante["remetente"]) ?></p>
  <p><strong>Caixa Origem:</strong> <?= htmlspecialchars($comprovante["caixa_origem"]) ?></p>
  <p><strong>Caixa Destino:</strong> <?= htmlspecialchars($comprovante["caixa_destino"]) ?></p>
  <p><strong>Quantidade:</strong> <?= $comprovante["valor"] ?> aura</p>
  <p><strong>Data:</strong> <?= $comprovante["data"] ?></p>

  <!-- Assinatura com campo de copia e cola -->
  <p><strong>Assinatura:</strong></p>
  <input type="text" id="assinatura" value="<?= htmlspecialchars($comprovante["assinatura"]) ?>" readonly style="width:100%;padding:8px;">
  <button onclick="copiarAssinatura()">📋 Copiar Assinatura</button>

  <img class="qr" src="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode(json_encode($comprovante)) ?>&size=150x150" alt="QR Comprovante">
  <button onclick="window.print()">🖨️ Imprimir Comprovante</button>
</div>

<script>
function copiarAssinatura() {
  var campo = document.getElementById("assinatura");
  campo.select();
  campo.setSelectionRange(0, 99999); // para mobile
  document.execCommand("copy");
  alert("Assinatura copiada!");
}
</script>
<?php endif; ?>


    <div class="saldo">✨ Saldo total de aura: <strong><?= $saldoAura ?></strong></div>

    <h3>📦 Enviar Aura</h3>
    <form method="POST">
      <input type="text" name="caixa_destino" placeholder="Caixa postal destino" required>
      <input type="number" name="valor" placeholder="Quantidade de aura a enviar" required>
      <input type="password" name="senha_usuario" placeholder="Sua senha para confirmar" required>
      <input type="hidden" name="enviar_aura" value="1">
      <button>⚡ Confirmar e Enviar Aura</button>
    </form>
  </div>
  
</body>
</html>

