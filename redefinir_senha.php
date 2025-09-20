<?php
$token = $_GET['token'] ?? null;
if ($token === null) {
    die("Token não fornecido.");
}

$token_hash = hash("sha256", $token);

require 'conexao.php';

// Verifica se o token hash existe e não expirou
$sql = "SELECT * FROM usuarios WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute([$token_hash]);
$user = $stmt->fetch();

if ($user === false) {
    die("Link inválido ou expirado. Por favor, solicite uma nova redefinição.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 400px;">
        <h2>Redefinir Nova Senha</h2>
        <form action="processa_redefinir_senha.php" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <label for="senha">Nova Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <label for="senha_confirm">Confirme a Nova Senha:</label>
            <input type="password" id="senha_confirm" name="senha_confirm" required>

            <button type="submit">Salvar Nova Senha</button>
        </form>
    </div>
</body>
</html>