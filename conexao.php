<?php
$host = "127.0.0.1";
$user = "root";      // seu usuário do MySQL
$pass = "Miguel11!";          // sua senha do MySQL
$db   = "cadastro_alunos";

// cria a conexão
$conn = new mysqli($host, $user, $pass, $db);

// testa a conexão
if ($conn->connect_error) {
  die("Erro de conexão: " . $conn->connect_error);
}
?>
