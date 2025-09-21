<!-- Substituido pelo cadastro geral -->
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CONFIGURAÇÕES DE SEGURANÇA ---
define('MAX_LOGIN_ATTEMPTS', 5); // Número máximo de tentativas
define('LOCKOUT_TIME', 900); // Tempo de bloqueio em segundos (15 minutos)

// Define o tempo de vida do cookie da sessão para 2 horas
$tempo_limite_sessao = 7200;
session_set_cookie_params($tempo_limite_sessao);

session_start();
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // CORREÇÃO: Selecionamos as colunas corretas e buscamos pelo 'email'
    // Adicionamos as colunas de controle de tentativa de login
    $sql = "SELECT id, nome_completo, email, senha_hash, perfil, ativo, failed_login_attempts, last_failed_login_at FROM usuarios WHERE email = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]); 
        $user = $stmt->fetch(); 

        // Verifica se a conta está temporariamente bloqueada
        if ($user && $user['failed_login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $last_attempt_time = strtotime($user['last_failed_login_at']);
            if (time() - $last_attempt_time < LOCKOUT_TIME) {
                // Ainda bloqueado
                $tempo_restante = ceil((LOCKOUT_TIME - (time() - $last_attempt_time)) / 60);
                header("Location: login.php?erro=bloqueado&tempo=" . $tempo_restante);
                exit();
            }
        }

        // Verificamos se o usuário existe, se a senha está correta E se o usuário está ativo
        if ($user && password_verify($senha, $user['senha_hash'])) {
            
            if ($user['ativo'] == 1) {
                // Se o login for bem-sucedido, zera as tentativas de falha
                if ($user['failed_login_attempts'] > 0) {
                    $sql_reset = "UPDATE usuarios SET failed_login_attempts = 0, last_failed_login_at = NULL WHERE id = ?";
                    $stmt_reset = $pdo->prepare($sql_reset);
                    $stmt_reset->execute([$user['id']]);
                }

                // Login bem-sucedido!
                $_SESSION['usuario_id'] = $user['id'];
                // Vamos guardar o nome completo para exibir no painel
                $_SESSION['usuario_nome'] = $user['nome_completo']; 
                $_SESSION['usuario_perfil'] = $user['perfil'];

                header("Location: painel.php"); 
                exit();
            } else {
                // Usuário existe, mas está inativo
                header("Location: login.php?erro=inativo"); // Podemos criar uma mensagem específica
                exit();
            }

        } else {
            // Se o login falhou, incrementa o contador de falhas se o usuário existir
            if ($user) {
                $sql_fail = "UPDATE usuarios SET failed_login_attempts = failed_login_attempts + 1, last_failed_login_at = NOW() WHERE id = ?";
                $stmt_fail = $pdo->prepare($sql_fail);
                $stmt_fail->execute([$user['id']]);
            }

            // Redireciona para a página de login com erro genérico para não vazar informação
            header("Location: login.php?erro=1");
            exit();
        }

    } catch (PDOException $e) {
        die("Erro ao processar o login: " . $e->getMessage());
    }

} else {
    header("Location: login.php");
    exit();
}
?>