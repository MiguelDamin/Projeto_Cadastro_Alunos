<?php
// Lê as variáveis de ambiente fornecidas pelo Railway
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// Cria a conexão usando as variáveis de ambiente
$conn = new mysqli($host, $user, $pass, $db, $port);

// Testa a conexão
if ($conn->connect_error) {
  die("Erro de conexão: " . $conn->connect_error);
}

// Cria uma conexão PDO para usar nos outros arquivos (opcional, mas recomendado)
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db}";
    $pdo = new PDO($dsn, $user, $pass);
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão PDO: " . $e->getMessage());
}
?>