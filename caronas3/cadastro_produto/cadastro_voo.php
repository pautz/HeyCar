<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Cuiaba');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true) {
    header("location: https://carlitoslocacoes.com/HeyCar/caronas/login.php");
    exit;
}

$eq_user = $_SESSION["username_odonto2"];

$conn = new mysqli("localhost", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Cadastro de Voos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        input[type="submit"] { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
    </style>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <h2 role="heading" aria-level="2">Cadastro de Voos</h2>
    <form id="cadastroVoo" action="" method="post" role="form" aria-label="Formulário de cadastro de voos">
        <label for="origem">Origem:</label>
        <input type="text" id="origem" name="origem" required aria-label="Origem do voo">

        <label for="destino">Destino:</label>
        <input type="text" id="destino" name="destino" required aria-label="Destino do voo">

        <label for="preco">Preço em AURA:</label>
        <input type="number" id="preco" name="preco" step="0.000000001" min="0.000000001" required aria-label="Preço em AURA">

        <label for="quantidade_assentos">Quantidade de Assentos:</label>
        <input type="number" id="quantidade_assentos" name="quantidade_assentos" required aria-label="Quantidade de assentos">

        <label for="metamask">Caixa Postal para Pagamento:</label>
        <input type="text" id="metamask" name="metamask" required aria-label="Caixa postal para pagamento">

        <label for="horario">Horário do Voo:</label>
        <input type="time" id="horario" name="horario" required aria-label="Horário do voo">

        <label for="telefone">Telefone para Contato:</label>
        <input type="tel" id="telefone" name="telefone" 
               placeholder="(99) 99999-9999" 
               pattern="\(\d{2}\)\s\d{5}-\d{4}" required aria-label="Telefone para contato">

        <label for="data_voo">Data do Voo:</label>
        <!-- Só permite hoje ou datas futuras -->
        <input type="date" id="data_voo" name="data_voo" 
               min="<?php echo date('Y-m-d'); ?>" 
               required aria-label="Data do voo">

        <div class="g-recaptcha" data-sitekey="6LcJ984sAAAAAMl7i71NhuBzAxAbZk8sJLyx2GYs" aria-label="Verificação reCAPTCHA"></div>
        <br>

        <input type="submit" value="Cadastrar Voo" aria-label="Cadastrar voo">
    </form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recaptcha_secret = "6LcJ984sAAAAAAjRoNw0Ddh1dcJhkKlQyC3AMlbN";
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret="
        . $recaptcha_secret . "&response=" . $recaptcha_response);
    $captcha_success = json_decode($verify);

    if ($captcha_success->success == false) {
        echo "<div role='alert' aria-live='assertive'><h3 style='color:red;'>⚠️ Falha na verificação reCAPTCHA. Tente novamente.</h3></div>";
    } else {
        $origem = htmlspecialchars($_POST['origem']);
        $destino = htmlspecialchars($_POST['destino']);
        $preco = floatval($_POST['preco']);
        $quantidade_assentos = intval($_POST['quantidade_assentos']);
        $metamask = htmlspecialchars($_POST['metamask']);
        $horario = htmlspecialchars($_POST['horario']);
        $telefone = htmlspecialchars($_POST['telefone']);
        $data_voo = $_POST['data_voo'];

        // 🚨 Validação backend: preço > 0
        if ($preco <= 0) {
            echo "<div role='alert' aria-live='assertive'><h3 style='color:red;'>⚠️ O preço em AURA deve ser maior que zero.</h3></div>";
        }
        // 🚨 Validação backend: data não pode ser passada
        elseif (strtotime($data_voo) < strtotime(date("Y-m-d"))) {
            echo "<div role='alert' aria-live='assertive'><h3 style='color:red;'>⚠️ A data escolhida não pode ser anterior a hoje.</h3></div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO voos (destino, origem, preco, metamask, horario, username, telefone, datas_permitidas) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssdsssss", $destino, $origem, $preco, $metamask, $horario, $eq_user, $telefone, $data_voo);

                if ($stmt->execute()) {
                    $voo_id = $stmt->insert_id;
                    for ($i = 1; $i <= $quantidade_assentos; $i++) {
                        $numero_assento = "A" . $i;
                        $stmt_assento = $conn->prepare("INSERT INTO assentos (voo_id, numero_assento, pago) VALUES (?, ?, 0)");
                        if ($stmt_assento) {
                            $stmt_assento->bind_param("is", $voo_id, $numero_assento);
                            $stmt_assento->execute();
                        }
                    }
                    echo "<div role='status' aria-live='polite'><h3 style='color: green;'>Voo cadastrado com sucesso incluindo $quantidade_assentos assentos!</h3></div>";
                    echo "<p>Telefone de contato: $telefone</p>";
                    echo "<p>Data do voo: $data_voo</p>";
                } else {
                    echo "<div role='alert' aria-live='assertive'><h3 style='color: red;'>Erro ao cadastrar voo.</h3></div>";
                }
                $stmt->close();
            }
        }
    }
    $conn->close();
}
?>
</body>
</html>
