<?php
$host = "ballast.proxy.rlwy.net";
$port = "15574";
$username = "root";
$password = "gakUZoQlkPfWBiXEmEtGbvhLHCdvKuxH";
$database = "railway";

// cria a conexão
$conn = new mysqli($host, $user, $pass, $db);

// testa a conexão
if ($conn->connect_error) {
  die("Erro de conexão: " . $conn->connect_error);
}
?>
