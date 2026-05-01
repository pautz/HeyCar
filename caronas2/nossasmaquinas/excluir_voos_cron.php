<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fuso horário
date_default_timezone_set('America/Sao_Paulo');

$hora_atual = (int)date("H"); 
$data_anterior = date("Y-m-d", strtotime("-1 day"));  

// Só permite exclusão entre 23h e 01h
if ($hora_atual >= 23 || $hora_atual < 1) {

    $cx = new mysqli("localhost", "u839226731_cztuap", "Meu6595869Trator", "u839226731_meutrator");
    if ($cx->connect_error) {
        die("Erro na conexão com o banco: " . $cx->connect_error);
    }
    $cx->set_charset("utf8mb4");

    // Buscar voos que tenham datas_permitidas igual ao dia anterior
    $sql = "SELECT id FROM voos WHERE DATE(datas_permitidas) = '$data_anterior'";
    $result = $cx->query($sql);

    while ($row = $result->fetch_assoc()) {
        $voo_id = intval($row["id"]);

        // Excluir assentos vinculados
        $cx->query("DELETE FROM assentos WHERE voo_id = $voo_id");

        // Excluir voo
        $cx->query("DELETE FROM voos WHERE id = $voo_id");

        echo "Voo $voo_id excluído (data $data_anterior) em " . date("Y-m-d H:i:s") . "<br>";
    }

    $cx->close();

} else {
    echo "<h3 style='color:red;'>⚠️ Exclusão de voos só permitida entre 23h00 e 01h00.</h3>";
    echo "<p>Hora atual do servidor: " . date("Y-m-d H:i:s") . "</p>";
}
?>
