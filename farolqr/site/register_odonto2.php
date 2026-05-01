<?php
session_start();
date_default_timezone_set('America/Cuiaba');

// Conexão com o banco
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'u839226731_cztuap');
define('DB_PASSWORD', 'Meu6595869Trator');
define('DB_NAME', 'u839226731_meutrator');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Variáveis
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
$maior18_err = "";
$captcha_err = "";

// Processa o formulário
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Valida reCAPTCHA v2
    $recaptcha_secret = "6LcJ984sAAAAAAjRoNw0Ddh1dcJhkKlQyC3AMlbN"; // substitua pela sua secret key
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret="
        . $recaptcha_secret . "&response=" . $recaptcha_response);
    $captcha_success = json_decode($verify);

    if ($captcha_success->success == false) {
        $captcha_err = "É necessário confirmar o reCAPTCHA.";
    }

    // Valida usuário
    if(empty(trim($_POST["username"]))){
        $username_err = "Digite um nome de usuário.";
    } else{
        $sql = "SELECT id FROM odonto2_users WHERE username = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "Este nome de usuário já está em uso.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Erro ao verificar usuário. Tente novamente.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Valida senha
    if(empty(trim($_POST["password"]))){
        $password_err = "Digite uma senha.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "A senha deve ter pelo menos 6 caracteres.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Confirma senha
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Confirme sua senha.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "As senhas não coincidem.";
        }
    }

    // Valida maior de 18 anos
    if(empty($_POST["maior18"])){
        $maior18_err = "É necessário confirmar que você tem 18 anos ou mais.";
    }

    // Se tudo estiver válido, insere no banco
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($maior18_err) && empty($captcha_err)){
        $sql = "INSERT INTO odonto2_users (username, password, maior18) VALUES (?, ?, ?)";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password, $param_maior18);
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_maior18 = 1; // sempre 1 se marcado
            if(mysqli_stmt_execute($stmt)){
                $_SESSION['loggedin_odonto2'] = true;
                $_SESSION['username_odonto2'] = $username;
                header("location: https://carlitoslocacoes.com/");
                exit;
            } else{
                echo "Erro ao registrar. Tente novamente.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Crie sua Conta Odonto2</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
       body { font: 18px Montserrat, sans-serif; line-height: 1.8; margin: 0; padding: 20px; background-color: #2196F3; color: #fff; }
       .wrapper { max-width: 500px; margin: auto; background: rgba(255,255,255,0.1); padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.2); color: #fff; }
       .form-control { width: 100%; padding: 12px; margin-bottom: 15px; border: none; border-radius: 5px; font-size: 16px; }
       .btn-xl { padding: 12px 20px; font-size: 16px; border-radius: 10px; width: 100%; margin-bottom: 10px; }
       .help-block { color: #ffcccb; font-size: 0.9em; }
    </style>
</head>
<body>
<div class="wrapper">
    <h2>Cadastre-se no FarolQR</h2>
    <p>Preencha com seus dados para criar sua conta.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
            <label>Usuário</label>
            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
            <span class="help-block"><?php echo $username_err; ?></span>
        </div>    
        <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
            <label>Senha</label>
            <input type="password" name="password" class="form-control">
            <span class="help-block"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
            <label>Confirme sua senha</label>
            <input type="password" name="confirm_password" class="form-control">
            <span class="help-block"><?php echo $confirm_password_err; ?></span>
        </div>
        <div class="form-group <?php echo (!empty($maior18_err)) ? 'has-error' : ''; ?>">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="maior18" value="1" required>
                    Confirmo que tenho 18 anos ou mais
                </label>
            </div>
            <span class="help-block"><?php echo $maior18_err; ?></span>
        </div>
        <!-- reCAPTCHA v2 -->
        <div class="form-group <?php echo (!empty($captcha_err)) ? 'has-error' : ''; ?>">
            <div class="g-recaptcha" data-sitekey="6LcJ984sAAAAAMl7i71NhuBzAxAbZk8sJLyx2GYs"></div>
            <span class="help-block"><?php echo $captcha_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-success btn-xl" value="Cadastrar">
            <a href="login_farolqr.php" class="btn btn-info btn-xl">Voltar</a>
        </div>
        <p>Já possui cadastro? <a href="https://carlitoslocacoes.com/farolqr/site/login_farolqr.php">Clique aqui para acessar o sistema.</a></p>
    </form>
</div>
</body>
</html>
