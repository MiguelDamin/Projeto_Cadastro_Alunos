<?php
// Habilita a exibição de erros para facilitar a depuração
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Inicia a sessão (essencial para o login)
session_start();

// 2. Inclui o arquivo de conexão
// Garanta que este arquivo define a variável $conexao
require_once 'conexao.php';

// 3. Verifica se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Pega o email e a senha do formulário
    // Corrigido: O login agora é feito com 'email', não 'usuario'
    $email = $_POST['email']; 
    $senha = $_POST['senha'];

    // 5. Prepara a consulta SQL com as colunas corretas da sua tabela
    // Corrigido: Buscamos por 'email' e selecionamos 'senha', 'nome_completo' e 'perfil'
    $sql = "SELECT id, nome_completo, senha, perfil, ativo FROM usuarios WHERE email = ?";
    
    $stmt = $conexao->prepare($sql);

    if ($stmt) {
        // 6. Associa o email do formulário ao '?' da consulta
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        // 7. Se encontrou exatamente um usuário com aquele email...
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            // 8. Verifica se a senha digitada corresponde à senha hash no banco
            // Corrigido: Usamos a coluna 'senha'
            if (password_verify($senha, $usuario['senha'])) {

                // 9. Verifica se o usuário está ativo (ativo = 1 ou TRUE)
                if ($usuario['ativo']) {
                    // 10. SUCESSO! Guarda os dados importantes na sessão
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome_completo']; // Corrigido
                    $_SESSION['usuario_perfil'] = $usuario['perfil'];     // Melhoria: guarda o perfil

                    // 11. Redireciona para a página principal do sistema
                    header("Location: painel.php"); // Sugestão: usar um nome como 'painel.php'
                    exit();
                } else {
                    // Erro: Usuário existe, mas está inativo
                    header("Location: login.php?erro=inativo");
                    exit();
                }
            }
        }
        
        // Se o email não foi encontrado ou a senha está errada, o erro é o mesmo
        header("Location: login.php?erro=invalido");
        exit();

    } else {
        // Erro fatal na preparação da consulta
        die("Erro na preparação da query: " . $conexao->error);
    }

    $stmt->close();
} else {
    // Se alguém tentar acessar o arquivo diretamente, redireciona para o login
    header("Location: login.php");
    exit();
}

$conexao->close();
?>