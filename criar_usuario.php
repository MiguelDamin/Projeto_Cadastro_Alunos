<?php
// Defina o nome de usuário e a senha que você quer criar
$nome_usuario = 'admin';
$senha_plana = 'admin1114'; // Use uma senha forte no seu projeto real!

// Gera um hash seguro para a senha
$senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

echo "<h1>Usuário de Teste (Corrigido)</h1>";
echo "<p>Use o comando SQL abaixo para inserir um usuário na sua tabela 'usuarios'.</p>";
echo "<hr>";
echo "<strong>Nome de Usuário:</strong> " . htmlspecialchars($nome_usuario) . "<br>";
echo "<strong>Senha em Texto Plano (para você saber):</strong> " . htmlspecialchars($senha_plana) . "<br>";
echo "<strong>Senha com HASH (para o banco):</strong> " . htmlspecialchars($senha_hash) . "<br>";
echo "<hr>";
echo "<h3>Comando SQL Corrigido:</h3>";

/* EXPLICAÇÃO DAS MUDANÇAS:
- Removemos a coluna 'primeiro_acesso'.
- Adicionamos a coluna 'ativo' com o valor 1 (true), para que o usuário já possa fazer login.
- Não precisamos inserir 'id' e 'data_criacao' porque o banco de dados faz isso automaticamente.
- Não precisamos inserir 'nivel_acesso' porque ele já tem um valor padrão ('usuario').
*/

echo "<pre><code>INSERT INTO usuarios (nome_usuario, senha_hash, ativo) VALUES ('" . $nome_usuario . "', '" . $senha_hash . "', 1);</code></pre>";

?>