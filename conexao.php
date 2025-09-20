<?php
// Preencha com os dados do seu banco de dados
$host = "ballast.proxy.rlwy.net";      
$user = "root";      
$pass = "gakUZoQlkPfWBiXEmEtGbvhLHCdvKuxH";  
$db   = "sistema_escolar";  
$port = 15574;        
$charset = 'utf8mb4'; // Essencial para suportar todos os caracteres

// DSN (Data Source Name) para a conexão PDO
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

// Opções do PDO para um comportamento mais seguro e útil
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna os dados como array associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa prepared statements nativos do DB
];

// Tenta estabelecer a conexão
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Em caso de erro, exibe uma mensagem e encerra o script
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>