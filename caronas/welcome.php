<?php
// Inicializar a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin_odonto2"]) || $_SESSION["loggedin_odonto2"] !== true) {
    header("location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font: 14px sans-serif;
            text-align: center;
            background-color: #f5f5f5; /* Fundo claro */
        }
        canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            pointer-events: none;
        }
        .page-header {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        .page-header h1 {
            font-size: 1.8em;
            color: #333;
        }
        .btn-xl {
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 10px;
            width: 15%;
        }
    </style>
</head>
<body>
    <canvas id="particle-background"></canvas>
    <div class="page-header">
        <h1>Olá, <b><?php echo isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : "Usuário"; ?></b>. Seja Bem-Vindo.</h1>
        <!-- Formulários originais -->
        <form method="GET" action="msg.php">
            <label for="id">Defina um Contrato:</label>
            <input type="text" id="id" name="id" class="form-control" required>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form><br>
        <form method="GET" action="pageloca.php">
            <label for="cv">Pesquisar Contrato:</label>
            <input type="text" id="cv" name="cv" class="form-control" required>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form><br>
        <form method="GET" action="https://carlitoslocacoes.com/site/page.php?cv=&">
            <label for="id">Pesquisar Prestação:</label>
            <input type="text" id="id" name="id" class="form-control" required>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form><br>
            </div>
    <p>
        <!-- Botões originais -->
       <!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página Responsiva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid mt-4">
    <div class="row g-3 d-flex flex-wrap justify-content-center">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/site/trocador.php" class="btn btn-outline-primary btn-xl w-100" 
               aria-label="Declaração do Serviço número 1">Declaração do Serviço #1</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/site/trocadorpagamento.php" class="btn btn-outline-secondary btn-xl w-100" 
               aria-label="Controle de Informações número 2">Controle de Informações #2</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="meusequipamentos.php" class="btn btn-outline-info btn-xl w-100" 
               aria-label="Comunicação Externa número 3">Comunicação Externa #3</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/site5/" class="btn btn-outline-dark btn-xl w-100" 
               aria-label="Central de Prestações número 4">Central de Prestações #4</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/site/consultar_vooreserva.php" class="btn btn-outline-success btn-xl w-100" 
               aria-label="Consultar Reserva de Voo">Consultar Reserva de Voo</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/site/todas_reservas_voo.php" class="btn btn-outline-secondary btn-xl w-100" 
               aria-label="Ver Todas Reservas de Voos">Ver Todas Reservas de Voos</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/graficos.php" class="btn btn-outline-secondary btn-xl w-100" 
               aria-label="Descubra Óleo">Descubra Óleo</a>
               
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/site/identificacao.php" class="btn btn-outline-secondary btn-xl w-100" 
               aria-label="Identificacao">Identificação*IMPORTANTE*</a>
               
        </div>
    </div>

    <br>

    <div class="row g-3 d-flex flex-wrap justify-content-center">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/reservadoquarto.php" class="btn btn-info btn-xl w-100" 
               aria-label="Reserva de Quartos">Reserva Quartos</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/site/embarque.php" class="btn btn-info btn-xl w-100" 
               aria-label="Embarque">Passagem</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="https://carlitoslocacoes.com/site/destacar.php" class="btn btn-primary btn-xl w-100" 
               aria-label="Destacar informações">Destacar</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="reset-password.php" class="btn btn-warning btn-xl w-100" 
               aria-label="Alterar senha">Alterar senha</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 text-center">
            <a href="logout.php" class="btn btn-danger btn-xl w-100" 
               aria-label="Sair do sistema">Sair</a>
        </div>
    </div>
</div>


<br>
        <a href="../caronas2" class="btn btn-success btn-xl">Site</a>
    </p>
    <script>
    // Configurar o canvas e o contexto
    const canvas = document.getElementById("particle-background");
    const ctx = canvas.getContext("2d");

    // Ajustar dimensões do canvas
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    // Criar partículas
    const particles = [];
    const numParticles = 100;

    for (let i = 0; i < numParticles; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            radius: Math.random() * 3 + 2,
            dx: Math.random() * 2 - 1,
            dy: Math.random() * 2 - 1
        });
    }

    // Função para calcular distância entre partículas ou mouse
    function distance(p1, p2) {
        const dx = p1.x - p2.x;
        const dy = p1.y - p2.y;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // Função para desenhar partículas
    function drawParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        for (const particle of particles) {
            // Desenhar círculos representando partículas
            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            ctx.fillStyle = "#333";
            ctx.fill();

            // Atualizar posição da partícula
            particle.x += particle.dx;
            particle.y += particle.dy;

            // Refletir partículas nas bordas
            if (particle.x < 0 || particle.x > canvas.width) particle.dx *= -1;
            if (particle.y < 0 || particle.y > canvas.height) particle.dy *= -1;
        }

        // Desenhar linhas entre partículas próximas
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dist = distance(particles[i], particles[j]);
                if (dist < 100) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(51, 51, 51, ${1 - dist / 100})`;
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }

        // Solicitar próxima atualização do quadro
        requestAnimationFrame(drawParticles);
    }

    // Adicionar interatividade do mouse
    canvas.addEventListener("mousemove", (event) => {
        const rect = canvas.getBoundingClientRect();
        const mouseX = event.clientX - rect.left;
        const mouseY = event.clientY - rect.top;

        for (const particle of particles) {
            const dist = distance({ x: mouseX, y: mouseY }, particle);
            if (dist < 50) {
                const angle = Math.atan2(particle.y - mouseY, particle.x - mouseX);
                const pushDistance = 5;

                // Atualizar direção para afastar partícula
                particle.dx = Math.cos(angle) * pushDistance;
                particle.dy = Math.sin(angle) * pushDistance;
                particle.x += particle.dx;
                particle.y += particle.dy;
            }
        }
    });

    // Iniciar animação
    drawParticles();
</script>
</body>
</html>
