<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Escolar</title>
    <link rel="stylesheet" href="css/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'senha_redefinida'): ?>
    <p style="color: green; text-align: center;">Senha redefinida com sucesso! Você já pode fazer o login.</p>
<?php endif; ?>
    <div class="container" style="max-width: 400px;">
        <h2>Acessar o Sistema</h2>
        
        <?php if (isset($_GET['erro']) && $_GET['erro'] == 1): ?>
            <p style="color: red; text-align: center;">E-mail ou senha inválidos!</p>
        <?php elseif (isset($_GET['erro']) && $_GET['erro'] == 'inativo'): ?>
            <p style="color: #ff8c00; text-align: center;">Este usuário está inativo. Contate o administrador.</p>
        <?php elseif (isset($_GET['erro']) && $_GET['erro'] == 'bloqueado'): 
            $tempo = isset($_GET['tempo']) ? htmlspecialchars($_GET['tempo']) : '15';
        ?>
            <p style="color: red; text-align: center;">Conta bloqueada por excesso de tentativas. Tente novamente em <?php echo $tempo; ?> minutos.</p>
        <?php endif; ?>

        <form action="processa_login.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="senha">Senha:</label>
            <div class="password-container">
                <input type="password" id="senha" name="senha" required>
                <i class="fas fa-eye" id="togglePassword"></i>
            </div>
            

            <button type="submit">Entrar</button>
            <p style="text-align: center; margin-top: 15px;">
                <a href="esqueci_senha.php">Esqueci minha senha</a>
            </p>
        </form>
    </div>

    <script>
        console.log('Script de visibilidade de senha iniciado.');

        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#senha');

        console.log('Elemento do ícone:', togglePassword);
        console.log('Elemento do input de senha:', passwordInput);

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                console.log('Ícone foi clicado!');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                console.log('Trocando tipo para:', type);
                this.classList.toggle('fa-eye-slash');
            });
        } else {
            console.error('ERRO: Não foi possível encontrar o ícone ou o campo de senha. Verifique os IDs no HTML!');
        }
    </script>
</body>
</html>