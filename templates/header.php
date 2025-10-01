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
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="sidebar-collapsed"> <nav class="sidebar">
        <div class="sidebar-header"></div> 
        
        <ul>
            <li class="has-submenu">
                <a href="#" class="submenu-toggle">
                    <div><i class="fas fa-folder-plus"></i><span>Cadastros</span></div>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li><a href="cadastro_geral.php?reset=1"><i class="fas fa-user-plus"></i><span>Cadastrar Aluno</span></a></li>
                    <li><a href="cadastro_professor.php?reset=1"><i class="fas fa-chalkboard-teacher"></i><span>Cadastrar Professores</span></a></li>
                    <li><a href="cadastro_turma.php"><i class="fas fa-school"></i><span>Cadastrar Turmas</span></a></li>
                    <li><a href="adicionar_avisos.php"><i class="fas fa-bullhorn"></i><span>Adicionar Avisos</span></a></li>

                </ul>
            </li>

            <li class="has-submenu">
                <a href="#" class="submenu-toggle">
                    <div><i class="fas fa-chart-pie"></i><span>Relatórios</span></div>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li><a href="#"><i class="fas fa-file-invoice"></i><span>Relatório de Alunos</span></a></li>
                    <li><a href="#"><i class="fas fa-file-alt"></i><span>Relatório de Turmas</span></a></li>
                </ul>
            </li>
            

        </ul>
    </nav>
    

    <header>
        <a href="painel.php" class="logo-link">
            <div class="header-logo">
                <i class="fas fa-graduation-cap"></i>
                <div>
                    <h1>Sistema Escolar</h1>
                    <p>GV Enterprise</p>
                </div>
            </div>
        </a>
        <a href="logout.php" class="logout-button">
            <i class="fas fa-sign-out-alt"></i> Sair
        </a>
    </header>

    <div class="main-content">
        <div class="content-wrapper">