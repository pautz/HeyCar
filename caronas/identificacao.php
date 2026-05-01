<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["username_odonto2"]) || $_SESSION["username_odonto2"] !== true) {
    header("location: login.php");
    exit;
}


// Configuração do banco de dados
$servername = "localhost"; // Alterar se necessário
$dbname = "u839226731_meutrator"; // Nome do banco
$username = "u839226731_cztuap"; // Usuário do banco
$password = "Meu6595869Trator"; // Senha do banco
 

// Conectar ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Obter o nome do usuário da sessão
$usuario = isset($_SESSION["username_odonto2"]) ? $_SESSION["username_odonto2"] : "Desconhecido";
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <style>/* Definição geral */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f4f4f4;
}

/* Estilização do container */
.container {
    max-width: 400px;
    margin: auto;
    background: #fff;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Títulos */
h2 {
    text-align: center;
    color: #333;
}

/* Formulário */
form {
    display: flex;
    flex-direction: column;
}

/* Labels */
label {
    font-weight: bold;
    margin-top: 10px;
}

/* Campos de entrada */
input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Botão */
button {
    margin-top: 15px;
    padding: 10px;
    background: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    font-size: 16px;
}

button:hover {
    background: #0056b3;
}

/* Responsividade */
@media (max-width: 600px) {
    .container {
        width: 90%;
    }
    
    input[type="text"],
    button {
        font-size: 14px;
    }
}
</style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Identidade</title>
</head>
<body>
    <h2>Insira seu Documento de Identidade</h2>

    <form method="POST" action="">
        <label for="documento">Número do Documento de Identidade:</label>
        <input type="text" id="documento" name="documento" required>
        <br><br>
        <label for="username">Usuário:</label>
        <input type="text" id="username" value="<?php echo htmlspecialchars($usuario); ?>" disabled>
        <br><br>
        <button type="submit">Enviar</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $documento = $_POST["documento"];

        // Insere no banco usando apenas a sessão do servidor
        $stmt = $conn->prepare("INSERT INTO identificacao (username, documento) VALUES (?, ?)");
        $stmt->bind_param("ss", $usuario, $documento);

        if ($stmt->execute()) {
            echo "<p>Dados salvos com sucesso na tabela identificacao!</p>";
        } else {
            echo "<p>Erro ao salvar: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }

    // Fechar conexão
    $conn->close();
    ?>
</body>
</html>

