<?php
// Inclui a conexão com o banco de dados.
require_once 'conexao.php';

// --- Validação e Segurança ---
// Começa a sessão para verificar o login, se necessário, como boa prática.
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403); // Proibido
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

// Pega o ID do nível de ensino enviado pela URL (via GET).
$id_nivel = (int)($_GET['id_nivel'] ?? 0);

if ($id_nivel === 0) {
    // Se nenhum ID foi enviado, retorna uma lista vazia.
    echo json_encode([]);
    exit;
}

// Define o cabeçalho da resposta como JSON.
header('Content-Type: application/json');

try {
    // Prepara e executa a consulta para buscar as séries do nível especificado.
    $sql = "SELECT id, nome FROM series WHERE id_nivel_ensino = ? ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_nivel]);
    
    $series = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna a lista de séries em formato JSON.
    echo json_encode($series);

} catch (PDOException $e) {
    http_response_code(500); // Erro Interno do Servidor
    echo json_encode(['error' => 'Erro ao buscar séries: ' . $e->getMessage()]);
}
?>