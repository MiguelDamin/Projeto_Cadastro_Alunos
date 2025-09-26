<?php
// Inicia a sessão para verificar o login.
session_start();
require_once 'conexao.php';

// --- 1. VERIFICAÇÕES DE SEGURANÇA ---
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro_turma.php');
    exit;
}

// --- 2. COLETA E LIMPEZA DOS DADOS DO FORMULÁRIO ---
$nome_turma = trim($_POST['nome_turma'] ?? '');
$ano_letivo = (int)($_POST['ano_letivo'] ?? 0);
$codigo_turma = trim($_POST['codigo_turma'] ?? '');
$nivel_ensino = $_POST['nivel_ensino'] ?? '';
$serie_ano = $_POST['serie_ano'] ?? '';
$turno = $_POST['turno'] ?? '';
$max_alunos = (int)($_POST['numero_maximo_alunos'] ?? 0);
$data_inicio = $_POST['data_inicio'] ?: null;
$data_fim = $_POST['data_fim'] ?: null;
$status = $_POST['status'] ?? 'Aberta';
$id_professor_regente = (int)($_POST['id_professor_regente'] ?? 0);
$id_sala_aula = (int)($_POST['id_sala'] ?? 0); // Presumindo que você adicione este campo no formulário
$descricao = trim($_POST['descricao'] ?? '');

// --- 3. VALIDAÇÃO DOS DADOS ---
if (empty($nome_turma) || empty($ano_letivo) || empty($nivel_ensino) || empty($serie_ano) || empty($turno)) {
    die("Erro: Nome da Turma, Ano Letivo, Nível, Série e Turno são obrigatórios. Por favor, volte e preencha todos.");
}

// Se o código da turma veio vazio, gera um automaticamente
if (empty($codigo_turma)) {
    $nivelSigla = strtoupper(implode('', array_map(function($word) { return $word[0]; }, explode(' ', $nivel_ensino))));
    $nomeSigla = strtoupper(str_replace([' ', 'º', 'ª'], '', $nome_turma));
    $turnoSigla = strtoupper(substr($turno, 0, 1));
    $codigo_turma = "{$ano_letivo}-{$nivelSigla}-{$nomeSigla}-{$turnoSigla}";
}

// --- 4. EXECUÇÃO NO BANCO DE DADOS ---
$pdo->beginTransaction();
try {
    // A instrução SQL agora corresponde EXATAMENTE à sua tabela
    $sql_turma = "INSERT INTO turmas 
                    (nome_turma, ano_letivo, periodo, codigo_turma, nivel_ensino, serie_ano, numero_maximo_alunos, data_inicio, data_fim, id_sala_aula, id_professor_regente, status, descricao) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                  
    $stmt_turma = $pdo->prepare($sql_turma);
    
    // A ordem das variáveis no execute() DEVE seguir a ordem das colunas no INSERT
    $stmt_turma->execute([
        $nome_turma,
        $ano_letivo,
        $turno, // Coluna `periodo` no banco
        $codigo_turma,
        $nivel_ensino,
        $serie_ano,
        $max_alunos > 0 ? $max_alunos : null,
        $data_inicio,
        $data_fim,
        $id_sala_aula > 0 ? $id_sala_aula : null,
        $id_professor_regente > 0 ? $id_professor_regente : null,
        $status,
        $descricao
    ]);
    
    // Se a inserção foi bem-sucedida, confirma as alterações no banco.
    $pdo->commit();

    // Redireciona para o painel com uma mensagem de sucesso.
    header('Location: painel.php?sucesso=turma_cadastrada');
    exit;

} catch (PDOException $e) {
    // Se qualquer erro ocorreu durante a inserção, desfaz a operação.
    $pdo->rollBack();
    
    // Exibe a mensagem de erro específica do banco de dados para depuração.
    die("Erro ao salvar a turma no banco de dados: " . $e->getMessage());
}
?>