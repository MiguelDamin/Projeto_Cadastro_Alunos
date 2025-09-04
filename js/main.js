function cadastrarAluno() {
  // pega os valores digitados
  let nome = document.getElementById("nome").value;
  let idade = document.getElementById("idade").value;
  let email = document.getElementById("email").value;
  let curso = document.getElementById("curso").value;

  // exibe os dados na tela
  document.getElementById("resultado").innerText =
    "Nome: " + nome + "\n" +
    "Idade: " + idade + "\n" +
    "E-mail: " + email + "\n" +
    "Curso: " + curso;
    

  // log no console (opcional)
  console.log({ nome, idade, email, curso });
}




