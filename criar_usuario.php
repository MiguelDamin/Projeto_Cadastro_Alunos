<?php
// --- CONFIGURAÇÃO DO NOVO USUÁRIO ---
$nome_completo = 'AndreyDev';
$email = 'andrey30r@gmail.com';
$senha_plana = 'Andrey11!'; // Use uma senha forte no seu projeto real!
$perfil = 'administrador'; // Opções: 'administrador', 'secretaria', 'professor'

// Gera um hash seguro para a senha (esta parte já estava correta)
$senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

// --- EXIBIÇÃO DA PÁGINA ---
echo "<h1>Gerador de Usuário para o Sistema</h1>";
echo "<p>Use o comando SQL abaixo para inserir um usuário na sua tabela 'usuarios' de forma segura.</p>";
echo "<hr>";
echo "<strong>Nome Completo:</strong> " . htmlspecialchars($nome_completo) . "<br>";
echo "<strong>Email (para login):</strong> " . htmlspecialchars($email) . "<br>";
echo "<strong>Perfil:</strong> " . htmlspecialchars($perfil) . "<br>";
echo "<strong>Senha em Texto Plano (para você saber):</strong> " . htmlspecialchars($senha_plana) . "<br>";
echo "<strong>Senha com HASH (valor para o banco):</strong> " . htmlspecialchars($senha_hash) . "<br>";
echo "<hr>";
echo "<h3>Comando SQL para Inserção:</h3>";

/* EXPLICAÇÃO DOS CAMPOS:
- nome_completo: O nome que será exibido no sistema.
- email: O e-mail que será usado para o login.
- senha: O hash seguro da senha. NUNCA A SENHA REAL!
- perfil: Define as permissões do usuário.
- As colunas 'id', 'ativo', 'criado_em' e 'atualizado_em' são preenchidas automaticamente pelo banco de dados.
*/

// Monta a string SQL com as colunas corretas da tabela 'usuarios'
$sql = "INSERT INTO usuarios (nome_completo, email, senha, perfil) VALUES ('" . $nome_completo . "', '" . $email . "', '" . $senha_hash . "', '" . $perfil . "');";

echo "<pre><code>" . htmlspecialchars($sql) . "</code></pre>";

?>