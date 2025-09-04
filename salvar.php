<?php
include "conexao.php"; // A variável $conn virá daqui

// Pegar os dados do formulário
$nome  = $_POST["nome"];
$idade = $_POST["idade"];
$email = $_POST["email"];
$curso = $_POST["curso"];

// Preparar a SQL para evitar injeção de SQL
$stmt = $conn->prepare("INSERT INTO alunos (nome, idade, email, curso) VALUES (?, ?, ?, ?)");
// "ssis" significa que os parâmetros são: string, string, integer, string (ajuste se necessário)
$stmt->bind_param("siss", $nome, $idade, $email, $curso);

// Executar e verificar o resultado
if ($stmt->execute()) {
  // Redireciona para a página inicial com mensagem de sucesso
  header("Location: index.html?status=sucesso");
} else {
  // Mostra um erro mais genérico
  echo "Erro ao cadastrar aluno: " . $stmt->error;
}

// Fechar a conexão
$stmt->close();
$conn->close();
?>