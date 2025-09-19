<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cadastro de Clientes</title>
    <link rel="stylesheet" href="style.css"> </head>
        <header>
        <h1>GV Enterprise</h1>
        <nav>
            <ul>
                <li><a href="index.html">Início</a></li> <!-- Link para a página inicial -->
                <li><a href="edit.php">Editar</a></li> <!-- Link para um arquivo na pasta "pasta" -->
                <li><a href="index.html">Cadastrar</a></li> <!-- Link para um arquivo em "outras_pastas" -->
            </ul>
        </nav>
    </header>
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
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required>
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