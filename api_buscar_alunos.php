<?php
// Inicia a sessão para podermos verificar o login
session_start();
// Inclui a conexão com o banco de dados
require_once 'conexao.php';

// --- VERIFICAÇÕES DE SEGURANÇA ---

// Define o cabeçalho da resposta para indicar que o conteúdo é JSON.
header('Content-Type: application/json');

// Garante que apenas usuários logados possam acessar esta API.
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403); // Código de erro "Proibido"
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

// --- LÓGICA DA BUSCA ---

// O Select2, por padrão, envia o termo de busca no parâmetro de URL 'q'.
$termo_busca = $_GET['q'] ?? '';

// Se o termo de busca for muito curto, não fazemos a busca para economizar recursos.
if (strlen($termo_busca) < 3) {
    echo json_encode(['results' => []]); // Retorna um resultado vazio
    exit;
}

try {
    // A consulta busca por nome COMPLETO OU por CPF, para ser mais flexível.
    $sql = "SELECT id, nome_completo, cpf 
            FROM alunos 
            WHERE nome_completo LIKE ? OR cpf LIKE ? 
            ORDER BY nome_completo ASC 
            LIMIT 20"; // Limita a 20 resultados por performance.

    $stmt = $pdo->prepare($sql);
    
    // Usamos '%' para indicar que a busca pode ser em qualquer parte do nome/cpf.
    // Passar os parâmetros no execute() previne SQL Injection.
    $stmt->execute(['%' . $termo_busca . '%', '%' . $termo_busca . '%']);
    
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultados_formatados = [];
    foreach ($alunos as $aluno) {
        // Formata os dados no padrão que o Select2 espera: um array de objetos,
        // onde cada objeto tem uma chave 'id' e uma chave 'text'.
        $resultados_formatados[] = [
            'id' => $aluno['id'],
            'text' => htmlspecialchars($aluno['nome_completo'] . ' (CPF: ' . $aluno['cpf'] . ')')
        ];
    }

    // O Select2 espera um objeto JSON com uma única chave principal chamada 'results'.
    echo json_encode(['results' => $resultados_formatados]);

} catch (PDOException $e) {
    http_response_code(500); // Código de erro "Erro Interno do Servidor"
    // Em produção, você logaria o erro em um arquivo. Para depuração, podemos enviá-lo.
    echo json_encode(['error' => 'Erro ao buscar alunos: ' . $e->getMessage()]);
}
?>