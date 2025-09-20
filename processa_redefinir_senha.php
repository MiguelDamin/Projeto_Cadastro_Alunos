<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $senha = $_POST['senha'];
    $senha_confirm = $_POST['senha_confirm'];

    // Validação básica
    if ($senha !== $senha_confirm) {
        die("As senhas não coincidem.");
    }
    // Adicione mais validações aqui (tamanho mínimo da senha, etc.)

    $token_hash = hash("sha256", $token);
    require 'conexao.php';

    // Verifica o token novamente por segurança
    $sql = "SELECT * FROM usuarios WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token_hash]);
    $user = $stmt->fetch();

    if ($user === false) {
        die("Link inválido ou expirado.");
    }

    // Hash da nova senha
    $nova_senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Atualiza a senha e limpa os campos de reset
    $sql_update = "UPDATE usuarios 
                   SET senha_hash = ?, reset_token_hash = NULL, reset_token_expires_at = NULL 
                   WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$nova_senha_hash, $user['id']]);

    // Redireciona para o login com uma mensagem de sucesso
    header("Location: login.php?sucesso=senha_redefinida");
    exit;
}