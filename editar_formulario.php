<?php
// Conectar no banco
$pdo = new PDO("mysql:host=127.0.0.1;dbname=cadastro_alunos", "root", "Miguel11!");

// Buscar os dados do aluno
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM alunos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $aluno = $stmt->fetch();
    
    if (!$aluno) {
        header("Location: editar.php");
        exit;
    }
} else {
    header("Location: editar.php");
    exit;
}

// Processar o UPDATE
if ($_POST) {
    $nome = $_POST['nome'];
    $idade = $_POST['idade'];
    $email = $_POST['email'];
    $curso = $_POST['curso'];
    
    $sql = "UPDATE alunos SET nome = ?, idade = ?, email = ?, curso = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nome, $idade, $email, $curso, $id])) {
        $sucesso = "Aluno atualizado com sucesso!";
    } else {
        $erro = "Erro ao atualizar aluno!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <input type="text" name="nome" value="<?php echo $aluno['nome']; ?>" required>
    
    <label>Idade:</label>
    <input type="number" name="idade" value="<?php echo $aluno['idade']; ?>" required>
    
    <label>E-mail:</label>
    <input type="email" name="email" value="<?php echo $aluno['email']; ?>" required>
    
    <label>Curso:</label>
    <input type="text" name="curso" value="<?php echo $aluno['curso']; ?>" required>
    
    <button type="submit">Salvar Alterações</button>
</form>

<p><a href="edit.php">← Voltar para busca</a></p>
</div>
</body>
</html>