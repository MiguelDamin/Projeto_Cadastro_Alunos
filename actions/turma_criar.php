<?php
// actions/turma_criar.php
session_start();
require_once '../conexao.php';

// --- VERIFICAÇÕES DE SEGURANÇA ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cadastro_turma.php'); exit;
}
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php'); exit;
}

// --- VALIDAÇÃO DOS DADOS ---
$errors = [];
$required_fields = ['nome_turma', 'ano_letivo', 'id_nivel_ensino', 'id_serie', 'turno'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = "O campo '{$field}' é obrigatório.";
    }
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST; // Guarda os dados para repopular
    header('Location: ../cadastro_turma.php');
    exit;
}

// --- PREPARAÇÃO DOS DADOS ---
$nome_turma = trim($_POST['nome_turma']);
$ano_letivo = (int)$_POST['ano_letivo'];
$id_nivel_ensino = (int)$_POST['id_nivel_ensino'];
$id_serie = (int)$_POST['id_serie'];
$turno = $_POST['turno'];
$max_alunos = (int)$_POST['numero_maximo_alunos'];

// Gera o código da turma de forma robusta
$stmt_nivel = $pdo->prepare("SELECT nome FROM niveis_ensino WHERE id = ?");
$stmt_nivel->execute([$id_nivel_ensino]);
$nivel_nome = $stmt_nivel->fetchColumn();

$nivelSigla = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $nivel_nome))));
$nomeSigla = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nome_turma));
$turnoSigla = strtoupper(substr($turno, 0, 1));
$codigo_turma = "{$ano_letivo}-{$nivelSigla}-{$nomeSigla}-{$turnoSigla}";

// --- PERSISTÊNCIA NO BANCO DE DADOS ---
try {
    $sql = "INSERT INTO turmas (nome_turma, ano_letivo, periodo, codigo_turma, id_nivel_ensino, id_serie, numero_maximo_alunos, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nome_turma,
        $ano_letivo,
        $turno,
        $codigo_turma,
        $id_nivel_ensino,
        $id_serie,
        $max_alunos > 0 ? $max_alunos : null,
        'Em Planejamento' // Status inicial padrão
    ]);
    
    $id_nova_turma = $pdo->lastInsertId();

    // --- REDIRECIONAMENTO PARA O PAINEL DE GERENCIAMENTO ---
    header('Location: ../gerenciar_turma.php?id=' . $id_nova_turma . '&sucesso=turma_criada');
    exit;

} catch (PDOException $e) {
    // Em caso de erro, redireciona de volta com a mensagem
    $_SESSION['form_errors'] = ["Erro de banco de dados: " . $e->getMessage()];
    $_SESSION['form_data'] = $_POST;
    header('Location: ../cadastro_turma.php');
    exit;
}
?>