<?php
session_start();

// 1. VERIFICA AUTENTICAÇÃO
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, não pode processar nada.
    header("Location: login.php");
    exit;
}

// 2. VERIFICA SE O ACESSO É VÁLIDO (VIA POST)
// Se alguém tentar acessar este arquivo diretamente pela URL, redireciona.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['adicionar_aviso'])) {
    header("Location: adicionar_aviso.php");
    exit;
}

// 3. INCLUI A CONEXÃO
require 'conexao.php';

// 4. COLETA E VALIDA OS DADOS
$titulo = trim($_POST['titulo'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');
$nivel_permissao = trim($_POST['nivel_permissao'] ?? '');
$status = trim($_POST['status'] ?? '');
$data_publicacao = trim($_POST['data_publicacao'] ?? '');

// Define a data de publicação para agora se estiver vazia
if (empty($data_publicacao)) {
    $data_publicacao = date('Y-m-d H:i:s');
} else {
    $data_publicacao = date('Y-m-d H:i:s', strtotime($data_publicacao));
}

// Validações dos campos
if (empty($titulo) || empty($mensagem) || empty($nivel_permissao) || empty($status)) {
    header("Location: adicionar_aviso.php?erro=Todos os campos obrigatórios devem ser preenchidos.");
    exit();
}

if (!in_array($nivel_permissao, ['publico', 'professores', 'administradores'])) {
    header("Location: adicionar_aviso.php?erro=Nível de permissão inválido.");
    exit();
}

if (!in_array($status, ['ativo', 'inativo', 'rascunho'])) {
    header("Location: adicionar_aviso.php?erro=Status inválido.");
    exit();
}

// 5. TENTA INSERIR NO BANCO
try {
    $sql = "INSERT INTO avisos (titulo, mensagem, data_publicacao, usuario_id, nivel_permissao, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        $titulo,
        $mensagem,
        $data_publicacao,
        $_SESSION['usuario_id'],
        $nivel_permissao,
        $status
    ]);
    
    // Redireciona com mensagem de sucesso ou falha
    if ($resultado) {
        header("Location: adicionar_aviso.php?sucesso=1");
    } else {
        header("Location: adicionar_aviso.php?erro=Falha ao salvar o aviso no banco de dados.");
    }
    
} catch (PDOException $e) {
    error_log("Erro ao cadastrar aviso: " . $e->getMessage());
    
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        header("Location: adicionar_aviso.php?erro=Erro: A tabela 'avisos' não foi encontrada.");
    } else {
        header("Location: adicionar_aviso.php?erro=Ocorreu um erro no banco de dados.");
    }
}

// Encerra o script após o redirecionamento.
exit();
?>
