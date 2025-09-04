<?php
include "conexao.php";

$nome  = $_POST["nome"];
$idade = $_POST["idade"];
$email = $_POST["email"];
$curso = $_POST["curso"];
$aluno_editado = $_POST["aluno_editado"];



$sql = "INSERT INTO alunos (nome, idade, email, curso)
        VALUES ('$nome', '$idade', '$email', '$curso')";

if ($conn->query($sql) === TRUE) {
  echo "Aluno cadastrado com sucesso!";
} else {
  echo "Erro: " . $conn->error;
}


$conn->close();
?>
