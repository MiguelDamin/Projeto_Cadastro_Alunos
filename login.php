<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cadastro de Clientes</title>
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <div class="container" style="max-width: 500px;">
        <h1>Acessar o Sistema</h1>

        <?php
        // Verifica se existe um parâmetro 'erro' na URL
        if (isset($_GET['erro'])) {
            echo '<p style="color: red; text-align: center; border: 1px solid red; padding: 10px; border-radius: 5px;">Usuário ou senha inválidos!</p>';
        }
        ?>

        <form action="processa_login.php" method="POST">
            <div class="campo">
                <label for="usuario">Usuário:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="campo">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>