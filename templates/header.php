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
    <!-- Link para o CSS do Font Awesome (ícones) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="sidebar">
        <ul>
            <!-- <li><a href="painel.php" title="Painel"><i class="fas fa-tachometer-alt"></i> <span>Painel</span></a></li> -->
            <li><a href="cadastro_geral.php?reset=1" title="Cadastro de aluno"><i class="fas fa-user-plus"></i> <span>Cadastro de Aluno</span></a></li>
            <li><a href="cadastro_professor.php" title="Cadastrar Professor"><i class="fas fa-user-tie"></i> <span>Cadastrar Professor</span></a></li>
            <li><a href="cadastro_turma.php" title="Cadastrar Turma"><i class="fas fa-users"></i> <span>Cadastrar Turma</span></a></li>
            <!-- Adicione outros links aqui -->
            <!-- Adicione outros links aqui -->
        </ul>
        <ul class="logout-section">
             <li><a href="logout.php" title="Sair"><i class="fas fa-sign-out-alt"></i> <span>Sair (<?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>)</span></a></li>
        </ul>
    </nav>

    <header>
        <a href="painel.php" class="logo-link">
            <h1>Sistema Escolar</h1>
        </a>
        <a href="logout.php" class="logout-button">Sair</a>
    </header>

    <div class="container">