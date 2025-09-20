<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['step'])) {
    header('Location: cadastro_geral.php');
    exit;
}

$step = $_POST['step'];

if ($step == 1) {
    // --- PROCESSANDO DADOS DO PASSO 1 (RESPONSÁVEL) ---
    
    // Limpa o CEP para salvar apenas números
    $cep_limpo = preg_replace('/[^0-9]/', '', $_POST['cep_resp'] ?? '');

    // Guarda TODOS os dados do responsável na sessão
    $_SESSION['dados_responsavel'] = [
        'nome_completo_resp' => $_POST['nome_completo_resp'],
        'cpf_resp' => $_POST['cpf_resp'],
        'email_resp' => $_POST['email_resp'],
        'telefone_resp' => $_POST['telefone_resp'],
        'cep_resp' => $cep_limpo,
        'logradouro_resp' => $_POST['logradouro_resp'],
        'numero_resp' => $_POST['numero_resp'],
        'complemento_resp' => $_POST['complemento_resp'],
        'bairro_resp' => $_POST['bairro_resp'],
        'cidade_resp' => $_POST['cidade_resp'],
        'uf_resp' => $_POST['uf_resp']
    ];

    // Avança para o próximo passo
    $_SESSION['step'] = 2;
    header('Location: cadastro_geral.php');
    exit;

} elseif ($step == 2) {
    // --- PROCESSANDO DADOS DO PASSO 2 (ALUNO) E SALVANDO TUDO ---

    $dados_responsavel = $_SESSION['dados_responsavel'];
    
    $nome_aluno = $_POST['nome_completo_aluno'];
    $data_nascimento_aluno = $_POST['data_nascimento_aluno'];
    $email_aluno = $_POST['email_aluno'] ?: null;
    $cpf_aluno = $_POST['cpf_aluno'] ?: null;
    
    $pdo->beginTransaction();

    try {
        // 1. INSERE O RESPONSÁVEL com TODOS os campos
        $sql_resp = "INSERT INTO responsaveis 
                        (nome_completo, cpf, email, telefone, cep, logradouro, numero, complemento, bairro, cidade, uf) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_resp = $pdo->prepare($sql_resp);
        
        $stmt_resp->execute([
            $dados_responsavel['nome_completo_resp'],
            $dados_responsavel['cpf_resp'],
            $dados_responsavel['email_resp'],
            $dados_responsavel['telefone_resp'],
            $dados_responsavel['cep_resp'],
            $dados_responsavel['logradouro_resp'],
            $dados_responsavel['numero_resp'],
            $dados_responsavel['complemento_resp'],
            $dados_responsavel['bairro_resp'],
            $dados_responsavel['cidade_resp'],
            $dados_responsavel['uf_resp']
        ]);

        $id_responsavel = $pdo->lastInsertId();

        // 2. INSERE O ALUNO
        $sql_aluno = "INSERT INTO alunos (nome_completo, data_nascimento, email, cpf, id_responsavel_principal) VALUES (?, ?, ?, ?, ?)";
        $stmt_aluno = $pdo->prepare($sql_aluno);
        $stmt_aluno->execute([
            $nome_aluno,
            $data_nascimento_aluno,
            $email_aluno,
            $cpf_aluno,
            $id_responsavel
        ]);

        $pdo->commit();

        unset($_SESSION['step']);
        unset($_SESSION['dados_responsavel']);

        header("Location: painel.php?sucesso=cadastro_completo");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        unset($_SESSION['step']);
        unset($_SESSION['dados_responsavel']);
        die("Erro ao cadastrar: " . $e->getMessage());
    }
}
?>