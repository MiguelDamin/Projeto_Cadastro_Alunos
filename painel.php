<?php 
// Inclui o cabeçalho que já faz a verificação de login
require 'templates/header.php'; 
?>

<main class="painel-container">

    <div class="widget">
        <h2>Bem-vindo!</h2>
        <p>Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong>.</p>
        <p>Utilize o menu acima para navegar entre as funcionalidades do sistema.</p>
    </div>

    <div class="widget">
        <h3>Alunos Recentes</h3>
        <p>Aqui você pode adicionar um resumo de alunos cadastrados, por exemplo.</p>
    </div>

    <div class="widget">
        <h3>Turmas</h3>
        <p>Outra informação importante, como as últimas turmas criadas.</p>
    </div>

    <div class="widget">
        <h3>Atalhos</h3>
        <p>Um atalho para a funcionalidade mais usada.</p>
    </div>

</main>

<?php 
// Inclui o rodapé
require 'templates/footer.php'; 
?>