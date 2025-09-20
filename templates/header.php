<?php
// Inicia a sessão em todas as páginas
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
    <link rel="stylesheet" href="/MeusProjetos/Projeto_Cadastro_Alunos/css/style.css">
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