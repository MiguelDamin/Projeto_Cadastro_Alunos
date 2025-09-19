<?php
// Preencha com os dados do seu banco de dados do Railway
$host = "ballast.proxy.rlwy.net";      // Cole o valor de MYSQLHOST aqui
$user = "root";      // Cole o valor de MYSQLUSER aqui
$pass = "gakUZoQlkPfWBiXEmEtGbvhLHCdvKuxH";  // Cole o valor de MYSQLPASSWORD aqui
$db   = "sistema_escolar";  // Cole o valor de MYSQLDATABASE aqui
$port = 15574;        // Cole o valor de MYSQLPORT aqui (sem aspas)

// Cria a conexão
$conexao = new mysqli($host, $user, $pass, $db, $port);

// Testa a conexão
if ($conn->connect_error) {
  die("Erro de conexão: " . $conn->connect_error);
}

// Cria uma conexão PDO para usar nos outros arquivos
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db}";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão PDO: " . $e->getMessage());
}
?>