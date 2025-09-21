<!-- Substituido pelo cadastro geral -->
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Define o tempo de vida do cookie da sessão para 2 horas
$tempo_limite_sessao = 7200;
session_set_cookie_params($tempo_limite_sessao);

session_start();
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // CORREÇÃO: Selecionamos as colunas corretas e buscamos pelo 'email'
    $sql = "SELECT id, nome_completo, email, senha_hash, perfil, ativo FROM usuarios WHERE email = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]); 
        $user = $stmt->fetch(); 

        // Verificamos se o usuário existe, se a senha está correta E se o usuário está ativo
        if ($user && password_verify($senha, $user['senha_hash'])) {
            
            if ($user['ativo'] == 1) {
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
            // Se o email não foi encontrado ou a senha está errada
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