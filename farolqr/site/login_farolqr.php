<?php
session_start();

// Forçar HSTS (apenas se estiver usando HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

// Se já está logado, redireciona
if (isset($_SESSION["loggedin_odonto2"]) && $_SESSION["loggedin_odonto2"] === true) {
    header("Location: identificacao_farolqr.php"); // página protegida
    exit;
}

// Include config file
require_once "config.php";

// Define variáveis
$username = $password = "";
$username_err = $password_err = "";

// Processa form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Valida credenciais
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password FROM odonto2_users WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $db_username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Login OK
                            $_SESSION["loggedin_odonto2"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username_odonto2"] = $db_username;

                            // Redireciona para página protegida
                            header("Location: identificacao_farolqr.php");
                            exit;
                        } else {
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    $username_err = "No account found with that username.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($link);
}
?>

 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; text-align: center; }
    </style>
</head>
<body>
    	<style>
  body {
  font: 20px Montserrat, sans-serif;
  line-height: 1.8;
  margin: 0;
  padding: 0;
  background-color: #2196F3; /* fundo azul */
  color: #fff;
}

/* Container centralizado */
.container-fluid {
  padding: 20px;
}

/* Caixa do formulário */
.page-header {
  background: rgba(255,255,255,0.1); /* leve transparência sobre o azul */
  padding: 20px;
  border-radius: 10px;
  max-width: 500px;
  margin: 40px auto;
  color: #fff;
}

/* Inputs */
input[type="text"], input[type="password"] {
  width: 100%;
  padding: 12px;
  margin-bottom: 15px;
  border: none;
  border-radius: 5px;
  font-size: 16px;
}

/* Botões */
.btn-xl {
  padding: 12px 20px;
  font-size: 16px;
  border-radius: 10px;
  width: 100%;
  margin-bottom: 10px;
}

/* Responsividade */
@media (max-width: 768px) {
  h2 {
    font-size: 22px;
  }
  p {
    font-size: 14px;
  }
  .page-header {
    margin: 20px;
    padding: 15px;
  }
}

@media (max-width: 480px) {
  h2 {
    font-size: 18px;
  }
  .btn-xl {
    font-size: 14px;
    padding: 10px;
  }
}
input.form-control {
  background-color: #fff; /* fundo branco */
  color: #000; /* texto preto */
  font-family: Montserrat, sans-serif;
  font-size: 16px;
}

  </style>
  
<div class="page-header">
    <div class="wrapper">
        <h2>Entrar</h2>
        <p>Preencha com seus dados para acessar o Sistema.</p>
        </div>
        <div class="container-fluid bg-3"> 
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

            <div class="form-group">
                <input type="submit" class="btn btn-primary btn-xl" value="Entrar">
                <a class="btn btn-info btn-xl" href="https://carlitoslocacoes.com/todofarol" role="button">Início</a>
            </div>
            <p>Não fez o registro no Sistema ainda?<br> <a href="register_odonto2.php" class="btn btn-success btn-xl">Registrar-se</a></p>
        </form>
        </div>
    </div>   
    <!-- Botão de acessibilidade VLibras -->
<div vw class="enabled">
  <div vw-access-button class="active"></div>
  <div vw-plugin-wrapper>
    <div class="vw-plugin-top-wrapper"></div>
  </div>
</div>

<!-- Script oficial VLibras -->
<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
  new window.VLibras.Widget('https://vlibras.gov.br/app');
</script>

</body>
</html>