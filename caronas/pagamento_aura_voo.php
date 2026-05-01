<?php
session_start();

// 🔐 VERIFICAÇÃO DE AUTENTICAÇÃO
if (!isset($_SESSION["loggedin_odonto2"])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION["username_odonto2"];
$mensagem = "";
$comprovante = null;
$voo_id = isset($_GET['voo_id']) ? intval($_GET['voo_id']) : null;
$assento = isset($_GET['assento']) ? htmlspecialchars($_GET['assento']) : null;
$data_reserva = isset($_GET['data_reserva']) ? htmlspecialchars($_GET['data_reserva']) : null;

// ⚙️ CONEXÃO COM BANCO DE DADOS
$conn = new mysqli("127.0.0.1", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($conn->connect_error) {
    die("❌ Erro na conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 🔍 BUSCAR DADOS DO USUÁRIO
$stmtUser = $conn->prepare("SELECT documento, caixa_postal, saldo_total FROM identificacao_odonto2 WHERE username = ? LIMIT 1");
$stmtUser->bind_param("s", $usuario);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
$userData = $resUser->fetch_assoc();
$caixaUsuario = $userData["caixa_postal"] ?? null;
$saldoAura = $userData["saldo_total"] ?? 0;
$stmtUser->close();

// 🚨 VALIDAR USUÁRIO
if (!$userData) {
    header("Location: identificacao.php");
    exit;
}

// 📋 BUSCAR INFORMAÇÕES DO VÔO
if ($voo_id) {
    $stmtVoo = $conn->prepare("SELECT id, destino, preco, metamask FROM voos WHERE id = ?");
    $stmtVoo->bind_param("i", $voo_id);
    $stmtVoo->execute();
    $resVoo = $stmtVoo->get_result();
    $vooData = $resVoo->fetch_assoc();
    $stmtVoo->close();
    
    if (!$vooData) {
        $mensagem = "❌ Voo não encontrado!";
    }
} else {
    $mensagem = "⚠️ ID do voo não fornecido!";
}

// 🚀 PROCESSAR PAGAMENTO EM AURA
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["confirmar_pagamento"])) {

    // 🔎 Verificar se o assento já está reservado para a data selecionada
    $stmtCheck = $conn->prepare("
        SELECT id FROM reservas_voo 
        WHERE voo_id = ? AND numero_assento = ? AND data_reserva = ? AND pago = 1
    ");
    $stmtCheck->bind_param("iss", $voo_id, $assento, $data_reserva);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows > 0) {
        $mensagem = "⚠️ Assento já reservado para esta data.";
    } else {
        // Valor vem do banco, não do form
        $valor_pagamento = (int)$vooData['preco'];
        $senha_usuario = $_POST["senha_usuario"];

        // 🔒 VERIFICAR SENHA
        $stmtSenha = $conn->prepare("SELECT password FROM odonto2_users WHERE username = ?");
        $stmtSenha->bind_param("s", $usuario);
        $stmtSenha->execute();
        $resSenha = $stmtSenha->get_result();
        $senhaCorreta = $resSenha->fetch_assoc()["password"] ?? null;
        $stmtSenha->close();

        // 💰 VERIFICAÇÕES DE SEGURANÇA
        if (!$senhaCorreta || !password_verify($senha_usuario, $senhaCorreta)) {
            $mensagem = "⚠️ Senha incorreta. Pagamento não autorizado.";
        } elseif ($valor_pagamento <= 0 || $saldoAura < $valor_pagamento) {
            $mensagem = "⚠️ Valor inválido ou saldo insuficiente.";
        } elseif ($valor_pagamento > 1000000) {
            $mensagem = "⚠️ O valor máximo por transação é 1.000.000 AURA.";
        } else {
            // ✅ PROCESSAR TRANSFERÊNCIA
            $conn->begin_transaction();
            try {
                $assinatura = hash('sha256', $usuario . $voo_id . $valor_pagamento . $caixaUsuario . microtime(true));

                // Débito e crédito
                $stmtDeb = $conn->prepare("UPDATE identificacao_odonto2 SET saldo_total = saldo_total - ? WHERE username = ?");
                $stmtDeb->bind_param("is", $valor_pagamento, $usuario);
                $stmtDeb->execute();
                $stmtDeb->close();

                $stmtCred = $conn->prepare("UPDATE identificacao_odonto2 SET saldo_total = saldo_total + ? WHERE caixa_postal = ?");
                $stmtCred->bind_param("is", $valor_pagamento, $vooData['metamask']);
                $stmtCred->execute();
                $stmtCred->close();

                // Transação
                $stmtInsert = $conn->prepare("
                    INSERT INTO transacoes_aura 
                    (remetente, destinatario, valor, caixa_origem, caixa_destino, assinatura, tipo_transacao) 
                    VALUES (?, ?, ?, ?, ?, ?, 'reserva_voo')
                ");
                $stmtInsert->bind_param("ssisss", $usuario, $vooData['metamask'], $valor_pagamento, $caixaUsuario, $vooData['metamask'], $assinatura);
                $stmtInsert->execute();
                $transacao_id = $conn->insert_id;
                $stmtInsert->close();

                // Comprovante
                $comprovante = [
                    "id_transacao" => $transacao_id,
                    "remetente" => $usuario,
                    "destinatario" => $vooData['metamask'],
                    "valor" => $valor_pagamento,
                    "caixa_origem" => $caixaUsuario,
                    "caixa_destino" => $vooData['metamask'],
                    "data" => date("d/m/Y H:i:s"),
                    "assinatura" => $assinatura,
                    "voo_id" => $voo_id,
                    "assento" => $assento,
                    "data_reserva" => $data_reserva,
                    "destino" => $vooData['destino'],
                    "documento" => $userData['documento']
                ];
                $_SESSION["comprovante_aura"] = $comprovante;

                $stmtComp = $conn->prepare("
                    INSERT INTO comprovantes_aura 
                    (remetente, destinatario, valor, caixa_origem, caixa_destino, transacao_id, assinatura, voo_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtComp->bind_param("ssissssi", $usuario, $vooData['metamask'], $valor_pagamento, $caixaUsuario, $vooData['metamask'], $transacao_id, $assinatura, $voo_id);
                $stmtComp->execute();
                $stmtComp->close();

                $hash_transacao = $assinatura;
                $stmtReserva = $conn->prepare("
                    INSERT INTO reservas_voo 
                    (eq_user, voo_id, numero_assento, data_reserva, transacao_hash, pago) 
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmtReserva->bind_param("sisss", $usuario, $voo_id, $assento, $data_reserva, $hash_transacao);
                $stmtReserva->execute();
                $stmtReserva->close();

                $conn->commit();
                $mensagem = "✅ Pagamento em AURA realizado com sucesso! Reserva confirmada.";
                $saldoAura -= $valor_pagamento;

            } catch (Exception $e) {
                $conn->rollback();
                $mensagem = "❌ Erro ao processar pagamento: " . $e->getMessage();
            }
        }
    }
    $stmtCheck->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>💳 Pagamento via AURA</title>
    <style>:root {
    --bg-color: #f0f4f8;
    --card-color: #ffffff;
    --accent-color: #007bff;
    --accent-hover: #0056b3;
    --success-color: #28a745;
    --text-color: #333;
    --radius: 12px;
    --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Reset */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

/* Container principal */
.container {
    max-width: 600px;
    width: 100%;
    background-color: var(--card-color);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 30px;
}

/* Títulos */
h1, h2 {
    color: var(--accent-color);
    margin-bottom: 20px;
    text-align: center;
}

/* Caixas de informação */
.info-box {
    background: #f8f9fa;
    border-left: 4px solid var(--accent-color);
    padding: 15px;
    margin: 15px 0;
    border-radius: 6px;
}

/* Saldo */
.saldo {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: var(--radius);
    text-align: center;
    font-size: 1.2rem;
    margin: 20px 0;
}

/* Formulário */
form {
    margin-top: 20px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

input[type="password"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

input:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

/* Botões */
button {
    width: 100%;
    background-color: var(--accent-color);
    color: white;
    border: none;
    padding: 14px;
    border-radius: var(--radius);
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 10px;
}

button:hover {
    background-color: var(--accent-hover);
}

/* Mensagens */
.mensagem {
    padding: 15px;
    border-radius: 6px;
    margin: 20px 0;
    font-weight: 500;
}

.mensagem.sucesso {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.mensagem.erro {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Comprovante */
.comprovante {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border: 2px solid var(--success-color);
    border-radius: var(--radius);
    padding: 25px;
    margin-top: 30px;
}

.comprovante h3 {
    color: var(--success-color);
    margin-bottom: 15px;
}

.comprovante-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.comprovante-field {
    background: rgba(255, 255, 255, 0.7);
    padding: 10px;
    border-radius: 6px;
}

.qr-code-container {
    text-align: center;
    margin: 20px 0;
}

.qr-code-container img {
    max-width: 200px;
    border-radius: 8px;
}

/* Responsividade */
@media (max-width: 768px) {
    .container {
        max-width: 95%;
        padding: 20px;
    }

    h1, h2 {
        font-size: 1.4rem;
    }

    .saldo {
        font-size: 1rem;
        padding: 15px;
    }

    button {
        font-size: 1rem;
        padding: 12px;
    }

    .comprovante-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    h1, h2 {
        font-size: 1.2rem;
    }

    .saldo {
        font-size: 0.9rem;
    }

    button {
        font-size: 0.9rem;
        padding: 10px;
    }
}
</style>
</head>
<body>
    <div class="container" role="main">
        <h1>💳 Pagamento em AURA</h1>
        <h2>Sistema de Transporte Cooperativo</h2>

        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo (strpos($mensagem, '✅') !== false) ? 'sucesso' : 'erro'; ?>" role="alert">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <?php if ($vooData): ?>
            <section aria-labelledby="info-voo">
                <h2 id="info-voo">Informações do Voo</h2>
                <div class="info-box">
                    <strong>✈️ Aeronave:</strong> <?= htmlspecialchars($vooData['id']) ?>
                </div>
                <div class="info-box">
                    <strong>✈️ Destino:</strong> <?= htmlspecialchars($vooData['destino']) ?>
                </div>
                <div class="info-box">
                    <strong>💺 Assento:</strong> <?= htmlspecialchars($assento) ?>
                </div>
                <div class="info-box">
                    <strong>📅 Data:</strong> <?= htmlspecialchars($data_reserva) ?>
                </div>
                <div class="saldo" aria-live="polite">
                    ✨ Seu Saldo: <?= number_format($saldoAura, 0, ',', '.') ?> AURA
                </div>
            </section>

            <?php if (!$comprovante): ?>
                <form method="POST" aria-labelledby="form-pagamento">
                    <h2 id="form-pagamento">Formulário de Pagamento</h2>
                    <div class="form-group">
                        <label for="valor_pagar">💵 Valor a Pagar:</label>
                        <p id="valor_pagar"><strong><?= number_format($vooData['preco'], 0, ',', '.') ?> AURA</strong></p>
                    </div>

                    <div class="form-group">
                        <label for="senha_usuario">🔐 Sua Senha:</label>
                        <input type="password" id="senha_usuario" name="senha_usuario" required aria-required="true">
                    </div>

                    <input type="hidden" name="confirmar_pagamento" value="1">
                    <button type="submit" aria-label="Confirmar pagamento em AURA">⚡ Confirmar Pagamento</button>
                </form>
            <?php endif; ?>

            <?php if ($comprovante): ?>
                <section class="comprovante" role="region" aria-labelledby="titulo-comprovante">
                    <h3 id="titulo-comprovante">✅ Comprovante de Pagamento</h3>
                    <div class="comprovante-row">
                        <div class="comprovante-field"><strong>ID:</strong> <?= $comprovante['id_transacao'] ?></div>
                        <div class="comprovante-field"><strong>Aeronave:</strong> <?= $comprovante['voo_id'] ?></div>
                        <div class="comprovante-field"><strong>Destino:</strong> <?= $comprovante['destino'] ?></div>
                        <div class="comprovante-field"><strong>Nome:</strong> <?= $comprovante['remetente'] ?></div>
                        <div class="comprovante-field"><strong>Documento:</strong> <?= $comprovante['documento'] ?></div>
                        <div class="comprovante-field"><strong>Data da Reserva:</strong> <?= $comprovante['data_reserva'] ?></div>
                        <div class="comprovante-field"><strong>Data da Emissão:</strong> <?= $comprovante['data'] ?></div>
                        <div class="comprovante-field"><strong>Assento:</strong> <?= $comprovante['assento'] ?></div>
                        <div class="comprovante-field"><strong>Assinatura:</strong> <?= $comprovante['assinatura'] ?></div>
                        <div class="comprovante-field"><strong>Valor:</strong> <?= number_format($comprovante['valor'], 0, ',', '.') ?> AURA</div>
                    </div>
                    <div class="qr-code-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?= urlencode(json_encode($comprovante)) ?>&size=200x200" alt="QR Code do comprovante de pagamento em AURA">
                    </div>
                    <button onclick="window.print()" aria-label="Imprimir comprovante">🖨️ Imprimir</button>
                </section>
            <?php endif; ?>

        <?php else: ?>
            <div class="mensagem erro" role="alert"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>

