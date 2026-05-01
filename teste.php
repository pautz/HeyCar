<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste de Horário do Servidor</h2>";

// Mostra o timezone que o servidor PHP está usando por padrão
echo "<p>Timezone padrão do servidor: " . date_default_timezone_get() . "</p>";

// Mostra a data/hora atual do servidor com esse timezone
echo "<p>Data e hora atuais (servidor): " . date("Y-m-d H:i:s") . "</p>";

// Mostra também em UTC para comparação
$dt = new DateTime("now", new DateTimeZone("UTC"));
echo "<p>Data e hora em UTC: " . $dt->format("Y-m-d H:i:s") . "</p>";
?>
