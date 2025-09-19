<?php
include "conexao.php"; // A variável $conn virá daqui

/*
  AVISO: Os nomes das variáveis foram ajustados para corresponder às colunas do seu banco de dados.
  Você PRECISA garantir que no seu formulário HTML, os campos (inputs) tenham os 'name' correspondentes.
  Exemplo: <input type="text" name="nome_completo">
*/

// Pegar os dados do formulário
$nome_completo     = $_POST["nome_completo"];
$data_nascimento   = $_POST["data_nascimento"]; // Ex: '2005-10-25'
$email             = $_POST["email"];
$cpf               = $_POST["cpf"];
$caminho_foto      = $_POST["caminho_foto"]; // Ex: 'uploads/foto_aluno.jpg'


// Preparar a SQL para evitar injeção de SQL
// A lista de colunas agora bate exatamente com a sua tabela
$stmt = $conn->prepare("INSERT INTO alunos (nome_completo, data_nascimento, email, cpf, caminho_foto) VALUES (?, ?, ?, ?, ?)");

// "sssss" significa que todos os 5 parâmetros são strings. 
// O banco de dados converterá a data para o formato correto.
$stmt->bind_param("sssss", $nome_completo, $data_nascimento, $email, $cpf, $caminho_foto);

// Executar e verificar o resultado
if ($stmt->execute()) {
  // Redireciona para a página inicial com mensagem de sucesso
  header("Location: index.html?status=sucesso");
  exit(); // É uma boa prática adicionar exit() após um redirecionamento
} else {
  // Mostra um erro mais detalhado para depuração
  echo "Erro ao cadastrar aluno: " . $stmt->error;
}

// Fechar a conexão
$stmt->close();
$conn->close();
?>