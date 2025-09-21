<?php
/**
 * Ponto de entrada da aplicação.
 *
 * Este script verifica se o usuário já tem uma sessão ativa (está logado).
 * - Se estiver logado, redireciona para o painel de controle (painel.php).
 * - Se não estiver logado, redireciona para a página de login (login.php).
 */
// Define o tempo de vida do cookie da sessão para 2 horas
$tempo_limite_sessao = 7200;
session_set_cookie_params($tempo_limite_sessao);

// Inicia a sessão para ter acesso às variáveis de sessão.
session_start();

// Verifica se a variável 'usuario_id' existe na sessão.
if (isset($_SESSION['usuario_id'])) {
    // Usuário está logado, redireciona para o painel.
    header('Location: painel.php');
    exit;
} else {
    // Usuário não está logado, redireciona para a página de login.
    header('Location: login.php');
    exit;
}