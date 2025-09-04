<?php
// Inclui o arquivo de conexão que agora usa as variáveis do Railway
include 'conexao.php'; // A variável $pdo virá daqui

// Verificar se o formulário foi enviado
$mensagem = "";
if ($_POST && isset($_POST['user_editado'])) {
    $nome_digitado = $_POST["user_editado"];
    
    // Buscar o aluno pelo nome usando a conexão PDO
    $sql = "SELECT * FROM alunos WHERE nome = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome_digitado]);
    $aluno = $stmt->fetch();
    
    if ($aluno) {
        // ENCONTROU! Redireciona para página de edição
        header("Location: editar_formulario.php?id=" . $aluno['id']);
        exit;
    } else {
        $mensagem = "Usuário não encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<title>Editar</title>
</head>
<body>
<div class="container">
<h1>Qual aluno deseja editar?</h1>
<form method="POST">
    <label>Digite o nome:</label>
    <input type="text" id="user_editado" name="user_editado" required>
    <button type="submit">Enviar</button>
</form>

<?php if ($mensagem): ?>
    <p><?php echo $mensagem; ?></p>
<?php endif; ?>

</div>
</body>
</html>