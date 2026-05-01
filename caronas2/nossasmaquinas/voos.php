<?php
session_start();

$servername = "localhost";
$username = "u839226731_cztuap";
$password = "Meu6595869Trator";
$dbname = "u839226731_meutrator";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Usuário logado
$usuarioLogado = $_SESSION["username_odonto2"] ?? null;

// Paginação
$limite = 9; 
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina - 1) * $limite;

// Filtros de pesquisa
$searchQuery = "WHERE 1=1"; 
if (!empty($_GET['search_destino'])) {
    $search_destino = $conn->real_escape_string($_GET['search_destino']);
    $searchQuery .= " AND v.destino LIKE '%$search_destino%'";
}
if (!empty($_GET['search_horario'])) {
    $search_horario = $conn->real_escape_string($_GET['search_horario']);
    $searchQuery .= " AND v.horario = '$search_horario'";
}
if (!empty($_GET['search_id'])) {
    $search_id = intval($_GET['search_id']);
    $searchQuery .= " AND v.id = $search_id";
}
if (!empty($_GET['search_origem'])) {
    $search_origem = $conn->real_escape_string($_GET['search_origem']);
    $searchQuery .= " AND v.origem LIKE '%$search_origem%'";
}
if (!empty($_GET['search_username'])) {
    $search_username = $conn->real_escape_string($_GET['search_username']);
    $searchQuery .= " AND v.username LIKE '%$search_username%'";
}
if (!empty($_GET['search_preco_max'])) {
    $search_preco_max = floatval($_GET['search_preco_max']);
    $searchQuery .= " AND v.preco <= $search_preco_max";
}
// Filtro por data
// Filtro por data
if (!empty($_GET['search_data'])) {
    $search_data = $conn->real_escape_string($_GET['search_data']);
    $searchQuery .= " AND DATE(v.datas_permitidas) = '$search_data'";
}

// Ordenação
$ordenar = $_GET['ordenar'] ?? 'recentes';
switch ($ordenar) {
    case 'preco':
        $orderBy = "ORDER BY v.preco ASC";
        break;
    case 'horario':
    $orderBy = "ORDER BY 
                   CASE WHEN v.datas_permitidas IS NULL THEN 1 ELSE 0 END, 
                   v.datas_permitidas ASC, 
                   v.horario ASC";
    break;

    case 'recentes':
    default:
        $orderBy = "ORDER BY v.id DESC";
        break;
}

// Consulta principal
$sql = "SELECT v.id, v.destino, v.preco, v.horario, v.origem, v.username, v.telefone, v.datas_permitidas
        FROM voos v
        $searchQuery $orderBy
        LIMIT $limite OFFSET $offset";




$result = $conn->query($sql);

// Total de páginas
$sqlTotal = "SELECT COUNT(*) AS total 
             FROM voos v 
             $searchQuery";
$resultTotal = $conn->query($sqlTotal);
$totalVoos = $resultTotal->fetch_assoc()['total'];
$totalPaginas = ceil($totalVoos / $limite);


$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>HeyCar</title>
    <meta charset="UTF-8">
    <style>
        body {font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(to right, #6a11cb, #2575fc); margin:0; padding:0; color:#fff;}
        h2 {margin:20px 0; font-size:2rem; color:#fff;}
        .menu {display:flex; flex-wrap:wrap; justify-content:center; gap:15px; margin:20px auto; max-width:900px;}
        .btn {background: linear-gradient(135deg, #2575fc, #6a11cb); color:white; padding:12px 24px; border-radius:10px; text-decoration:none; display:inline-block; font-weight:600; transition:all 0.3s ease; text-align:center;}
        .btn:hover {background: linear-gradient(135deg, #6a11cb, #2575fc); transform:translateY(-3px);}
        .main-container {display:flex; justify-content:space-between; gap:30px; padding:20px;}
        .cardform {
    flex: 1;
    max-width: 350px;
    padding: 25px;
    border-radius: 12px;
    background: #ffffff;
    color: #333;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    box-sizing: border-box; /* garante que padding conte na largura */
}

/* Ajuste dos inputs */
.cardform input[type="text"],
.cardform input[type="number"],
.cardform input[type="time"] {
    width: 100%;
    max-width: 100%; /* impede que ultrapassem o card */
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box; /* respeita o espaço interno */
}

/* Botão dentro do card */
.cardform .btn {
    width: 100%;
    text-align: center;
}

        .form-container div {margin-bottom:18px;}
        label {display:block; font-weight:bold; margin-bottom:8px; color:#2575fc;}
        input[type="text"], input[type="number"], input[type="time"] {width:100%; padding:14px; font-size:16px; border:1px solid #ccc; border-radius:8px;}
        .product-container {flex:3; display:flex; flex-wrap:wrap; gap:20px;}
        .product-card {width:calc(33.33% - 20px); max-width:320px; background:#fff; color:#333; padding:20px; border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.2);}
        /* Paginação */
.pagination {
    text-align: center;
    margin: 30px 0;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px; /* espaço entre os botões */
}

.pagination a {
    display: inline-block;
    padding: 10px 18px;
    background: linear-gradient(135deg, #2575fc, #6a11cb);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.3s ease;
    font-weight: 600;
    min-width: 40px;
    text-align: center;
}

.pagination a:hover {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    transform: translateY(-2px);
}

/* Página ativa */
.pagination a[style] {
    background: #6a11cb !important;
    font-weight: bold;
    box-shadow: 0 0 8px rgba(0,0,0,0.3);
}

        @media (max-width:900px){.main-container{flex-direction:column; align-items:center;} .product-card{width:90%;}}
    </style>
</head>
<body>

    <h2>HeyCar - Sistema de Transporte Cooperativo</h2>

    <!-- Barra de navegação -->
    <nav class="menu" role="navigation" aria-label="Menu principal">
        <a href="https://carlitoslocacoes.com/todofarol/" class="btn">🏠 Início</a>
        <a href="https://carlitoslocacoes.com/HeyCar/caronas/login.php" class="btn">Entrar</a>
        <a href="https://carlitoslocacoes.com/farolqr/site/logout.php" class="btn">Sair</a>
        <a href="https://carlitoslocacoes.com/HeyCar/caronas2/nossasmaquinas/reservados_voo.php" class="btn">Verificar</a>
        <a href="https://carlitoslocacoes.com/HeyCar/caronas3/cadastro_produto/cadastro_voo.php" class="btn">➕ Cadastrar Carona</a>
    </nav>

    <!-- Botões de ordenação -->
    <div class="menu">
        <a href="?ordenar=preco" class="btn">💰 Mais baratos primeiro</a>
        <a href="?ordenar=horario" class="btn">⏰ Mais próximos no horário</a>
        <a href="?ordenar=recentes" class="btn">🆕 Mais recentes cadastrados</a>
    </div>

    <div class="main-container">
        <div class="cardform">
            <form method="get" action="" class="form-container">
                <div><label for="search_origem">Origem:</label><input type="text" id="search_origem" name="search_origem"></div>
                <div><label for="search_destino">Destino:</label><input type="text" id="search_destino" name="search_destino"></div>
                <div><label for="search_horario">Horário:</label><input type="time" id="search_horario" name="search_horario"></div>
                <div><label for="search_id">ID:</label><input type="number" id="search_id" name="search_id"></div>
                <div>
  <label for="search_data">Data:</label>
  <input type="date" id="search_data" name="search_data">
</div>
                <div><label for="search_username">Criador:</label><input type="text" id="search_username" name="search_username"></div>
                <div><label for="search_preco_max">Preço Máximo (AURA):</label><input type="number" id="search_preco_max" name="search_preco_max"></div>
                <div><input type="submit" value="Pesquisar" class="btn"></div>
            </form>
        </div>

        <div class="product-container">
           <?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='product-card'>";

        // Botão de excluir (apenas se o dono for o usuário logado)
        if ($usuarioLogado && $usuarioLogado === $row["username"]) {
            echo "<form method='post' action='excluir_voo.php' style='display:inline;'>
                    <input type='hidden' name='voo_id' value='" . $row["id"] . "'>
                    <button type='submit' class='delete-btn' title='Excluir Carona' aria-label='Excluir carona número " . htmlspecialchars($row["id"]) . "'>X</button>
                  </form>";
        }

        echo "<h3>✈️ Carona #" . htmlspecialchars($row["id"]) . "</h3>";
        echo "<p><strong>Origem:</strong> " . htmlspecialchars($row["origem"]) . "</p>";
        echo "<p><strong>Destino:</strong> " . htmlspecialchars($row["destino"]) . "</p>";
        echo "<p><strong>Horário:</strong> " . htmlspecialchars($row["horario"]) . "</p>";
        echo "<p><strong>Preço:</strong> <span style='color:#2575fc; font-weight:bold;'>AURA " . number_format($row["preco"], 8, ',', '.') . "</span></p>";
        echo "<p><strong>Criador:</strong> " . htmlspecialchars($row["username"]) . "</p>";
        echo "<p><strong>Telefone:</strong> " . htmlspecialchars($row["telefone"]) . "</p>";
        echo "<p><strong>Data:</strong> " . htmlspecialchars($row["datas_permitidas"]) . "</p>";
        echo "<p><a href='../../caronas/reservapassagem.php?id=" . $row["id"] . "' class='btn'>🛫 Reservar</a></p>";
        echo "</div>";
    }
} else {
    echo "<p>Nenhuma carona encontrado.</p>";
}
?>

        </div>
    </div>

    <!-- Paginação -->
    <nav class='pagination'>
        <?php 
        if ($totalPaginas > 1) {
            // Botão anterior
            if ($pagina > 1) {
                echo "<a href='?pagina=" . ($pagina - 1) . "&ordenar=$ordenar' aria-label='Página anterior'>« Anterior</a>";
            }

            // Números das páginas
            for ($i = 1; $i <= $totalPaginas; $i++) {
                $classeAtiva = ($i == $pagina) ? "style='background:#6a11cb; font-weight:bold;'" : "";
                echo "<a href='?pagina=$i&ordenar=$ordenar' $classeAtiva aria-label='Ir para página $i'>$i</a>";
            }

            // Botão próximo
            if ($pagina < $totalPaginas) {
                echo "<a href='?pagina=" . ($pagina + 1) . "&ordenar=$ordenar' aria-label='Próxima página'>Próxima »</a>";
            }
        }
        ?>
    </nav>

</body>
</html>
