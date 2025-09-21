<?php
// --- CONTROLE DE TEMPO DA SESSÃO ---
// Define o tempo de vida do cookie da sessão em segundos.
// Ex: 3600 = 1 hora. 7200 = 2 horas. 86400 = 1 dia.
$tempo_limite_sessao = 7200; // Definimos 2 horas
session_set_cookie_params($tempo_limite_sessao);

// Inicia a sessão (ou continua a sessão existente com o novo tempo de vida)
session_start();

// Verifica se o usuário está logado
// Se 'usuario_id' não existir na sessão, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão Escolar</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<header>
    <h1>Sistema Escolar</h1>
    <nav>
        <ul>
            <li><a href="painel.php">Painel</a></li>
            <li><a href="cadastro_geral.php?reset=1">Novo Cadastro</a></li>
            <li><a href="cadastro_turma.php">Cadastrar Turma</a></li>
            <li><a href="logout.php" style="color: #ffc107;">Sair (<?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>)</a></li>
        </ul>
    </nav>
</header>
<body>
    <div class="container">