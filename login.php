<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Escolar</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        
        <div class="auth-icon">
            <i class="fas fa-graduation-cap"></i>
        </div>

        <h1>Sistema Escolar</h1>
        <p class="auth-subtitle">GV Enterprise - Gestão Acadêmica</p>
        
        <div class="message-area">
            <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'senha_redefinida'): ?>
                <p class="form-message success">Senha redefinida com sucesso! Você já pode fazer o login.</p>
            <?php endif; ?>
            <?php if (isset($_GET['erro']) && $_GET['erro'] == 1): ?>
                <p class="form-message error">E-mail ou senha inválidos!</p>
            <?php elseif (isset($_GET['erro']) && $_GET['erro'] == 'inativo'): ?>
                <p class="form-message warning">Este usuário está inativo. Contate o administrador.</p>
            <?php elseif (isset($_GET['erro']) && $_GET['erro'] == 'bloqueado'): 
                $tempo = isset($_GET['tempo']) ? htmlspecialchars($_GET['tempo']) : '15';
            ?>
                <p class="form-message error">Conta bloqueada. Tente novamente em <?php echo $tempo; ?> minutos.</p>
            <?php endif; ?>
        </div>

        <form action="processa_login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email" required>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <div class="password-container">
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                    <i class="fas fa-eye" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit">Entrar</button>
            
            <div class="auth-links">
                <a href="esqueci_senha.php">Esqueci minha senha</a>
            </div>
        </form>
    </div>

    <script>
        // O seu script de visibilidade de senha permanece o mesmo.
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#senha');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>