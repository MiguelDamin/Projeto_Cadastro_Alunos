<?php
// Adicione estas duas linhas para depuração
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Inicia a sessão.
session_start();

// 2. Inclui o arquivo de conexão
require_once 'conexao.php';

// 3. Verifica se a requisição é do tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // 5. PREPARA A QUERY CORRIGIDA
    //    Removemos 'primeiro_acesso' e colocamos 'ativo', que existe na sua tabela.
    $sql = "SELECT id, nome_usuario, senha_hash, ativo FROM usuarios WHERE nome_usuario = ?";
    
    $stmt = $conexao->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $user = $resultado->fetch_assoc();

            // 7. Verifica se a senha está correta E se o usuário está ativo
            if (password_verify($senha, $user['senha_hash'])) {

                // VERIFICA SE O USUÁRIO ESTÁ ATIVO (ativo = 1)
                if ($user['ativo'] == 1) {
                    // 8. Login bem-sucedido! Guarda os dados na sessão.
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_nome'] = $user['nome_usuario'];

                    // 9. Redireciona para a página principal
                    header("Location: cadastro_cliente.php");
                    exit();
                } else {
                    // Se o usuário não está ativo, redireciona com um erro específico
                    header("Location: login.php?erro=inativo");
                    exit();
                }
            }
        }
        
        // Se o usuário não foi encontrado ou a senha está errada
        header("Location: login.php?erro=1");
        exit();

    } else {
        echo "<h1>Erro na preparação da query: " . $conexao->error . "</h1>";
    }

    $stmt->close();
} else {
    header("Location: login.php");
    exit();
}

$conexao->close();
?>