<?php
// Inclui o arquivo de conexão
include 'conexao.php'; // A variável $pdo virá daqui

// Buscar os dados do aluno
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM alunos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $aluno = $stmt->fetch();
    
    if (!$aluno) {
        // Se não encontrar o aluno, redireciona para a busca
        header("Location: edit.php");
        exit;
    }   
} else {
    // Se não houver ID na URL, redireciona para a busca
    header("Location: edit.php");
    exit;
}

// Processar o UPDATE quando o formulário for enviado
if ($_POST) {
    $nome = $_POST['nome'];
    $idade = $_POST['idade'];
    $email = $_POST['email'];
    $curso = $_POST['curso'];
    
    $sql = "UPDATE alunos SET nome = ?, idade = ?, email = ?, curso = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nome, $idade, $email, $curso, $id])) {
        $sucesso = "Aluno atualizado com sucesso!";
        // Atualiza os dados do aluno na página para refletir a mudança
        $aluno['nome'] = $nome;
        $aluno['idade'] = $idade;
        $aluno['email'] = $email;
        $aluno['curso'] = $curso;
    } else {
        $erro = "Erro ao atualizar aluno!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale-1.0">
<link rel="stylesheet" href="style.css">
<title>Editar Aluno</title>
</head>
<body>
<div class="container">
<h2>Editar Aluno</h2>

<?php if (isset($sucesso)): ?>
    <p style="color: green;"><?php echo $sucesso; ?></p>
<?php endif; ?>

<?php if (isset($erro)): ?>
    <p style="color: red;"><?php echo $erro; ?></p>
<?php endif; ?>

<form method="POST">
    <label>Nome:</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($aluno['nome']); ?>" required>
    
    <label>Idade:</label>
    <input type="number" name="idade" value="<?php echo htmlspecialchars($aluno['idade']); ?>" required>
    
    <label>E-mail:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($aluno['email']); ?>" required>
    
    <label>Curso:</label>
    <input type="text" name="curso" value="<?php echo htmlspecialchars($aluno['curso']); ?>" required>
    
    <button type="submit">Salvar Alterações</button>
</form>

<p><a href="edit.php">← Voltar para busca</a></p>
</div>
</body>
</html>