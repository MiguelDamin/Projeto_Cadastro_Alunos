<?php
// --- CARREGAMENTO DE VARIÁVEIS DE AMBIENTE ---
// Inclui o autoloader do Composer para carregar a biblioteca DotEnv
require_once __DIR__ . '/vendor/autoload.php';

// Carrega as variáveis do arquivo .env para o ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// --- DADOS DE CONEXÃO VINDOS DO ARQUIVO .ENV ---
$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$db   = $_ENV['DB_DATABASE'];
$port = $_ENV['DB_PORT'];
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