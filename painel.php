<?php 
// Inclui o cabeçalho que já faz a verificação de login
require 'templates/header.php'; 
?>

<main class="painel-container">

    <aside class="painel-lateral">
        <h2>Bem-vindo ao Painel de Controle!</h2>
        Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong>.
        <p>Utilize o menu acima para navegar entre as funcionalidades do sistema, como cadastrar novos alunos, responsáveis e turmas.</p>
    </aside>

    <section class="conteudo-principal">
        <p>Selecione uma opção no menu acima para começar.</p>
    </section>

</main>

<?php 
// Inclui o rodapé
require 'templates/footer.php'; 
?>