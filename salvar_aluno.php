<?php
session_start();
// Se não estiver logado, não pode salvar!
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require 'conexao.php';

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados do formulário
    // É importante validar e sanitizar esses dados no projeto real!
    $nome_completo = $_POST['nome_completo'];
    $data_nascimento = $_POST['data_nascimento'];
    $email = $_POST['email'] ?: null; // Se for vazio, guarda NULL
    $cpf = $_POST['cpf'] ?: null;
    $id_responsavel_principal = $_POST['id_responsavel_principal'] ?: null;

    try {
        // Prepara a instrução SQL para inserção
        $sql = "INSERT INTO alunos (nome_completo, data_nascimento, email, cpf, id_responsavel_principal) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // Executa a inserção passando os valores
        $stmt->execute([
            $nome_completo, 
            $data_nascimento, 
            $email, 
            $cpf, 
            $id_responsavel_principal
        ]);

        // Redireciona para o painel com mensagem de sucesso
        header("Location: painel.php?sucesso=1");
        exit;

    } catch (PDOException $e) {
        // Em caso de erro, redireciona com uma mensagem de erro
        // Em um sistema real, você deveria logar o erro em vez de exibi-lo
        header("Location: cadastro_aluno.php?erro=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Se não for POST, redireciona
    header("Location: cadastro_aluno.php");
    exit;
}
?>