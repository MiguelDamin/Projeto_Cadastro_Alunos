<?php 
// Inclui o cabeçalho (header + sidebar)
require 'templates/header.php'; 
?>

<head>
    <title>Painel - Sistema Escolar</title>
</head>

<div class="page-header">
    <h2>Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h2>
    <p>Bem-vindo ao sistema de gestão escolar. Aqui você pode gerenciar alunos, responsáveis e turmas.</p>
</div>

<div class="dashboard-grid">

    <div class="widget card">
        <div class="widget-icon" style="background-color: #e0f7fa;">
            <i class="fas fa-info-circle" style="color: #0097a7;"></i>
        </div>
        <div class="widget-content">
            <h3>Bem-vindo!</h3>
            <p>Utilize o menu lateral para navegar entre as funcionalidades.</p>
        </div>
    </div>

    <div class="widget card">
        <div class="widget-icon" style="background-color: #fff3e0;">
            <i class="fas fa-users" style="color: #ff9800;"></i>
        </div>
        <div class="widget-content">
            <h3>Alunos Recentes</h3>
            <p>Resumo dos últimos alunos cadastrados no sistema.</p>
        </div>
    </div>

    <div class="widget card">
        <div class="widget-icon" style="background-color: #e8f5e9;">
            <i class="fas fa-chalkboard" style="color: #4caf50;"></i>
        </div>
        <div class="widget-content">
            <h3>Turmas</h3>
            <p>Informações sobre as últimas turmas criadas ou ativas.</p>
        </div>
    </div>

    <div class="widget card">
        <div class="widget-icon" style="background-color: #fce4ec;">
            <i class="fas fa-bolt" style="color: #e91e63;"></i>
        </div>
        <div class="widget-content">
            <h3>Atalhos</h3>
            <p>Acesse rapidamente as funcionalidades mais utilizadas.</p>
        </div>
    </div>

</div>

<?php 
// Inclui o rodapé (fecha as tags e adiciona o script da sidebar)
require 'templates/footer.php'; 
?>