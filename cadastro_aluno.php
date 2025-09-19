<?php 
require 'templates/header.php'; 
require 'conexao.php';

// Buscar os responsáveis para preencher o <select>
$responsaveis = $pdo->query("SELECT id, nome_completo FROM responsaveis ORDER BY nome_completo")->fetchAll();
?>

<h2>Cadastro de Novo Aluno</h2>

<form action="salva_aluno.php" method="POST">
    <label for="nome_completo">Nome Completo:</label>
    <input type="text" name="nome_completo" id="nome_completo" required>

    <label for="data_nascimento">Data de Nascimento:</label>
    <input type="date" name="data_nascimento" id="data_nascimento" required>

    <label for="email">E-mail:</label>
    <input type="email" name="email" id="email">

    <label for="cpf">CPF:</label>
    <input type="text" name="cpf" id="cpf">

    <label for="id_responsavel_principal">Responsável Principal:</label>
    <select name="id_responsavel_principal" id="id_responsavel_principal">
        <option value="">Selecione um responsável</option>
        <?php foreach ($responsaveis as $responsavel): ?>
            <option value="<?php echo $responsavel['id']; ?>">
                <?php echo htmlspecialchars($responsavel['nome_completo']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p><small>Se o responsável não está na lista, <a href="cadastro_responsavel.php">cadastre-o primeiro</a>.</small></p>

    <button type="submit">Cadastrar Aluno</button>
</form>

<?php require 'templates/footer.php'; ?>