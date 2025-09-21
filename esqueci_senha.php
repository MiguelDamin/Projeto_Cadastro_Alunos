<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="container">
        <h2>Recuperar Senha</h2>
        <p>Digite seu e-mail cadastrado e enviaremos um link para você redefinir sua senha.</p>
        
        <?php if (isset($_GET['sucesso'])): ?>
            <p style="color: green;">Verifique seu e-mail para o link de redefinição.</p>
        <?php endif; ?>
        <?php if (isset($_GET['erro'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['erro']); ?></p>
        <?php endif; ?>

        <form action="processa_esqueci_senha.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Enviar Link de Recuperação</button>
        </form>
    </div>
</body>
</html>