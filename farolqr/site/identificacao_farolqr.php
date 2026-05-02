<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) return false;
    if (preg_match('/^(.)\1{10}$/', $cpf)) return false;

    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += $cpf[$i] * (($t + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ($cpf[$t] != $digito) return false;
    }
    return true;
}

// Configuração da sessão persistente
// Configuração da sessão persistente
$lifetime = 86400; // 1 dia
$domain   = 'carlitoslocacoes.com';

ini_set('session.cookie_lifetime', $lifetime);
ini_set('session.gc_maxlifetime', $lifetime);

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path'     => '/',
    'domain'   => $domain,
    'secure'   => true,    // use true se estiver em HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// AGORA sim inicia a sessão
session_start();

// Verifica login e usuário da sessão
if (
    !isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true ||
    empty($_SESSION["username_odonto2"])
) {
    header("Location: https://carlitoslocacoes.com/HeyCar/farolqr/site/login_farolqr.php");
    exit;
}

// Pega usuário da sessão
$usuario = $_SESSION["username_odonto2"];


// Conexão com banco
$conn = new mysqli("localhost", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
$conn->set_charset("utf8mb4");

$mensagem = "";
$caixaExistente = null;

// Verificar se já tem caixa postal
$stmtVerifica = $conn->prepare("SELECT caixa_postal, documento, telefone, orcid, foto_perfil, data_criacao 
                                FROM identificacao_odonto2 WHERE username = ?");
$stmtVerifica->bind_param("s", $usuario);
$stmtVerifica->execute();
$resultVerifica = $stmtVerifica->get_result();
$caixaExistente = $resultVerifica->fetch_assoc();
$stmtVerifica->close();

// Criar caixa postal
if (isset($_POST['criar_caixa_postal'])) {
    if ($caixaExistente) {
        $mensagem = "⚠️ Você já possui uma caixa postal.";
    } else {
        $documento = trim($_POST["documento"]);
        $telefone = trim($_POST["telefone"]);
        $fotoPerfil = null;

        // Validação de CPF
        if (!validarCPF($documento)) {
            $mensagem = "❌ Documento inválido. Informe um CPF válido.";
        } else {
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg','jpeg','png','gif'];
                if (in_array($ext, $permitidas)) {
                    $nomeArquivo = 'perfil_' . uniqid() . '.' . $ext;
                    $caminho = 'uploads/' . $nomeArquivo;
                    if (!is_dir('uploads')) mkdir('uploads');
                    move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho);
                    $fotoPerfil = $caminho;
                } else {
                    $mensagem = "❌ Tipo de arquivo inválido.";
                }
            }

            // gerar código único
            do {
                $codigo = 'FAROLQR_' . substr(md5($usuario . microtime(true) . random_int(1000, 9999)), 0, 10);
                $stmtCheck = $conn->prepare("SELECT 1 FROM identificacao_odonto2 WHERE caixa_postal = ?");
                $stmtCheck->bind_param("s", $codigo);
                $stmtCheck->execute();
                $stmtCheck->store_result();
                $existe = $stmtCheck->num_rows > 0;
                $stmtCheck->close();
            } while ($existe);

            $stmt = $conn->prepare("INSERT INTO identificacao_odonto2 
    (username, documento, telefone, foto_perfil, caixa_postal, data_criacao) 
    VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssss", $usuario, $documento, $telefone, $fotoPerfil, $codigo);
            $stmt->execute();
            $stmt->close();
            $mensagem = "📬 Caixa postal criada com sucesso: <strong>$codigo</strong>";
        }
    }
}

// Atualizar documento
if (isset($_POST['editar_documento'])) {
    $novoDocumento = trim($_POST["novo_documento"]);
    if (!validarCPF($novoDocumento)) {
        $mensagem = "❌ Documento inválido. Informe um CPF válido.";
    } else {
        $stmtUpdate = $conn->prepare("UPDATE identificacao_odonto2 SET documento = ? WHERE username = ?");
        $stmtUpdate->bind_param("ss", $novoDocumento, $usuario);
        $stmtUpdate->execute();
        $stmtUpdate->close();
        $mensagem = "✅ Documento atualizado com sucesso.";
    }
}
?>

<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>📬 Caixa Postal Odonto2</title>
    <style>
        body { font-family: Arial, sans-serif; background: #eef; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 0 8px #aaa; }
        h2 { color: #007bff; text-align: center; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input[type="text"], input[type="file"] {
            width: 100%; padding: 10px; margin-top: 5px;
            border: 1px solid #ccc; border-radius: 6px;
        }
        button {
            width: 100%; padding: 10px; margin-top: 15px;
            background: #007bff; color: #fff;
            border: none; border-radius: 6px;
            cursor: pointer; font-size: 16px;
        }
        button:hover { background: #0056b3; }
        .mensagem { margin-top: 20px; text-align: center; font-size: 16px; color: #333; }
        .caixa {
            background: #f9f9f9; margin-top: 30px; padding: 15px;
            border-left: 5px solid #007bff; border-radius: 8px;
        }
        .caixa strong { display: block; font-size: 18px; }
        .caixa small { display: block; margin-top: 5px; color: #555; }
        form.editar { margin-top: 15px; }
        img.foto-perfil {
            max-width: 150px; border-radius: 8px;
            margin-top: 10px; display: block;
        }
        .btn-login {
            background: #444; color: #fff;
            padding: 10px; border-radius: 6px;
            font-size: 16px; margin-top: 20px;
            width: 100%; border: none;
        }
        .btn-login:hover { background: #222; }
    </style>
</head>
<body>
    <div class="container">
       <h2>Bem-vindo, <?= htmlspecialchars($_SESSION["username_odonto2"]) ?></h2>

       <form method="POST" action="" enctype="multipart/form-data">
    <label for="documento">Documento CPF:</label>
    <input type="text" name="documento" id="documento" placeholder="Digite seu documento" required>

    <label for="telefone">Telefone:</label>
    <input type="text" name="telefone" id="telefone" placeholder="Digite seu telefone" required>


    <label for="foto_perfil">Foto de Perfil:</label>
    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*">

    <input type="hidden" name="criar_caixa_postal" value="1">
    <!-- Botão que envia o formulário -->
    <button type="submit">📬 Criar Caixa Postal</button>
</form>

<!-- Botão separado que só redireciona -->
<button class="btn-login" onclick="window.location.href='https://carlitoslocacoes.com/HeyCar/'">
    Início
</button>


<button class="btn-login" onclick="window.location.href='https://carlitoslocacoes.com/HeyCar/farolqr/site/logout.php'">
    🔐 Sair
</button>
            
 <?php if ($mensagem): ?>
            <div class="mensagem"><?= $mensagem ?></div>
        <?php endif; ?>

        <?php if ($caixaExistente): ?>
            <div class="caixa">
                <strong>📦 Caixa Postal: <?= htmlspecialchars($caixaExistente['caixa_postal']) ?></strong>
                <small>🧾 Documento CPF: <?= htmlspecialchars($caixaExistente['documento']) ?></small>
                <small>📞 Telefone: <?= htmlspecialchars($caixaExistente['telefone']) ?></small>
                <small>🕒 Criado em: <?= $caixaExistente['data_criacao'] ?></small>

                <?php if (!empty($caixaExistente['foto_perfil'])): ?>
                    <img src="../../../farolqr/site/<?= htmlspecialchars($caixaExistente['foto_perfil']) ?>" alt="Foto de perfil" class="foto-perfil">
                <?php endif; ?>

                <form method="POST" class="editar">
                    <input type="text" name="novo_documento" placeholder="Atualizar documento" required>
                    <input type="hidden" name="editar_documento" value="1">
                    <button type="submit">✏️ Editar Documento</button>
                </form>
               <button class="btn-login" onclick="window.location.href='https://carlitoslocacoes.com/HeyCar/farolqr/site/balance_transacao.php'">
    Banco
</button>
                <button class="btn-login" onclick="window.location.href='https://carlitoslocacoes.com/index.php'">
    Comprar Aura
</button>

            </div>

        </form>

            
        <?php endif; ?>
       
    </div>
    
</body>
</html>

