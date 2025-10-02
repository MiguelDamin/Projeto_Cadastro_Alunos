<?php
// actions/turma_criar.php
session_start();
require_once '../conexao.php';

// --- VERIFICAÇÕES DE SEGURANÇA ---
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    die("Acesso negado.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Método não permitido.");
}

// --- VALIDAÇÃO DOS DADOS ---
$errors = [];
$required_fields = ['nome_turma', 'ano_letivo', 'id_nivel_ensino', 'id_serie', 'turno'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = "O campo '{$field}' é obrigatório.";
    }
}

// Se houver erros, armazena na sessão e redireciona de volta
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST; // Guarda os dados para repopular o formulário
    header('Location: ../cadastro_turma.php');
    exit;
}

// --- PREPARAÇÃO DOS DADOS ---
$nome_turma = trim($_POST['nome_turma']);
$ano_letivo = (int)$_POST['ano_letivo'];
$id_nivel_ensino = (int)$_POST['id_nivel_ensino'];
$id_serie = (int)$_POST['id_serie'];
$turno = $_POST['turno'];
$max_alunos = !empty($_POST['numero_maximo_alunos']) ? (int)$_POST['numero_maximo_alunos'] : null;

// Gera o código da turma de forma robusta, buscando o nome do nível de ensino
try {
    $stmt_nivel = $pdo->prepare("SELECT nome FROM niveis_ensino WHERE id = ?");
    $stmt_nivel->execute([$id_nivel_ensino]);
    $nivel_nome = $stmt_nivel->fetchColumn();

    $nivelSigla = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $nivel_nome))));
    $nomeSigla = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nome_turma));
    $turnoSigla = strtoupper(substr($turno, 0, 1));
    $codigo_turma = "{$ano_letivo}-{$nivelSigla}-{$nomeSigla}-{$turnoSigla}";
} catch (PDOException $e) {
    // Falha ao gerar o código, podemos prosseguir com um código nulo ou lançar um erro
    $codigo_turma = null; // Ou trate o erro como preferir
}


// --- PERSISTÊNCIA NO BANCO DE DADOS ---
$pdo->beginTransaction();
try {
    // O comando INSERT agora inclui TODAS as colunas relevantes da tabela `turmas`
    $sql = "INSERT INTO turmas 
                (nome_turma, ano_letivo, periodo, codigo_turma, id_nivel_ensino, id_serie, numero_maximo_alunos, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        $nome_turma,
        $ano_letivo,
        $turno,
        $codigo_turma,
        $id_nivel_ensino,
        $id_serie,
        $max_alunos,
        'Em Planejamento' // Status inicial padrão
    ]);
    
    $id_nova_turma = $pdo->lastInsertId();
    $pdo->commit();

    // --- REDIRECIONAMENTO ---
    // Após criar a turma, redireciona para a nova tela de "Gerenciar Turma",
    // onde o usuário poderá adicionar alunos, horários, etc.
    header('Location: ../gerenciar_turmas.php?id=' . $id_nova_turma . '&sucesso=turma_criada');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    // Em caso de erro, redireciona de volta com a mensagem de erro específica do banco
    $_SESSION['form_errors'] = ["Erro de banco de dados: " . $e->getMessage()];
    $_SESSION['form_data'] = $_POST;
    header('Location: ../cadastro_turma.php');
    exit;
}
?>