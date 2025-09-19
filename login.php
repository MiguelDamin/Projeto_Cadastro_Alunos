<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Escolar</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width: 400px;">
        <h2>Acessar o Sistema</h2>
        
        <?php if (isset($_GET['erro'])): ?>
            <p style="color: red; text-align: center;">Email ou senha inválidos!</p>
        <?php endif; ?>

        <form action="processa_login.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>