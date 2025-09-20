<?php 
// Inclui o cabeçalho que já faz a verificação de login
require 'templates/header.php'; 
?>

<h2>Bem-vindo ao Painel de Controle!</h2>
<p>
    Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong>.
</p>
<p>
    Utilize o menu acima para navegar entre as funcionalidades do sistema, como cadastrar novos alunos, responsáveis e turmas.
</p>

<?php 
// Inclui o rodapé
require 'templates/footer.php'; 
?>